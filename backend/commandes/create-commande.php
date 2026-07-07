<?php
/**
 * POST /backend/commandes/create-commande.php
 * Passage d'une commande par un utilisateur connecté.
 * Corps JSON : menu_id, nb_personnes, date_prestation (AAAA-MM-JJ),
 * heure_livraison (HH:MM), adresse_livraison, ville_livraison,
 * distance_km (obligatoire hors Bordeaux).
 *
 * Le prix est intégralement recalculé côté serveur (voir calcul-prix.php).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/mongo.php';
require_once __DIR__ . '/../mail/send-mail.php';
require_once __DIR__ . '/calcul-prix.php';

exigerMethode('POST');
$utilisateur = exigerAuthentification();
$donnees = lireJson();

/* ---------- Validation des champs ---------- */

$erreurs = [];

$menuId      = $donnees['menu_id'] ?? null;
$nbPersonnes = $donnees['nb_personnes'] ?? null;
$datePresta  = champTexte($donnees, 'date_prestation');
$heure       = champTexte($donnees, 'heure_livraison');
$adresse     = champTexte($donnees, 'adresse_livraison');
$ville       = champTexte($donnees, 'ville_livraison');
$distanceKm  = $donnees['distance_km'] ?? 0;

if (!is_numeric($menuId))                { $erreurs['menu_id'] = 'Menu manquant.'; }
if (!is_numeric($nbPersonnes) || (int) $nbPersonnes < 1) { $erreurs['nb_personnes'] = 'Nombre de personnes invalide.'; }
if ($adresse === '')                     { $erreurs['adresse_livraison'] = 'L\'adresse de livraison est obligatoire.'; }
if ($ville === '')                       { $erreurs['ville_livraison'] = 'La ville de livraison est obligatoire.'; }

$dateValide = DateTime::createFromFormat('Y-m-d', $datePresta);
if ($dateValide === false) {
    $erreurs['date_prestation'] = 'Date de prestation invalide.';
} elseif ($dateValide < new DateTime('today')) {
    $erreurs['date_prestation'] = 'La date de prestation doit être à venir.';
}
if (preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $heure) !== 1) {
    $erreurs['heure_livraison'] = 'Heure de livraison invalide (HH:MM).';
}
if ($ville !== '' && !estBordeaux($ville) && (!is_numeric($distanceKm) || (float) $distanceKm <= 0)) {
    $erreurs['distance_km'] = 'Hors Bordeaux, indiquez la distance en kilomètres (livraison 5 € + 0,59 €/km).';
}

if ($erreurs !== []) {
    repondre(422, ['erreur' => 'Formulaire invalide.', 'champs' => $erreurs]);
}

$menuId = (int) $menuId;
$nbPersonnes = (int) $nbPersonnes;

/* ---------- Transaction : stock verrouillé, commande + suivi, tout ou rien ---------- */

$pdo = getPDO();
$pdo->beginTransaction();

try {
    // Verrou sur la ligne du menu : deux commandes simultanées ne peuvent pas
    // consommer le même stock (SELECT ... FOR UPDATE)
    $requete = $pdo->prepare(
        'SELECT menu_id, titre, prix_min, nb_personnes_min, quantite_restante
         FROM menu WHERE menu_id = :id AND actif = 1 FOR UPDATE'
    );
    $requete->execute(['id' => $menuId]);
    $menu = $requete->fetch();

    if ($menu === false) {
        $pdo->rollBack();
        repondre(404, ['erreur' => 'Menu introuvable ou plus proposé.']);
    }
    if ((int) $menu['quantite_restante'] < 1) {
        $pdo->rollBack();
        repondre(409, ['erreur' => 'Ce menu est épuisé : plus aucune commande disponible.']);
    }
    if ($nbPersonnes < (int) $menu['nb_personnes_min']) {
        $pdo->rollBack();
        repondre(422, ['erreur' => "Ce menu se commande pour {$menu['nb_personnes_min']} personnes minimum."]);
    }

    $prix = calculerPrix($menu, $nbPersonnes, $ville, (float) $distanceKm);

    // Numéro de commande lisible : CMD-ANNEE-XXXX
    $suivant = (int) $pdo->query('SELECT COALESCE(MAX(commande_id), 0) + 1 FROM commande')->fetchColumn();
    $numero = sprintf('CMD-%s-%04d', date('Y'), $suivant);

    $requete = $pdo->prepare(
        'INSERT INTO commande (numero_commande, utilisateur_id, menu_id, date_prestation,
                               heure_livraison, adresse_livraison, ville_livraison, distance_km,
                               nb_personnes, prix_menu, prix_livraison, prix_total, reduction_10)
         VALUES (:numero, :utilisateur_id, :menu_id, :date_prestation, :heure, :adresse, :ville,
                 :distance, :nb_personnes, :prix_menu, :prix_livraison, :prix_total, :reduction)'
    );
    $requete->execute([
        'numero'          => $numero,
        'utilisateur_id'  => $utilisateur['id'],
        'menu_id'         => $menuId,
        'date_prestation' => $datePresta,
        'heure'           => $heure,
        'adresse'         => $adresse,
        'ville'           => $ville,
        'distance'        => $prix['distance_km'],
        'nb_personnes'    => $nbPersonnes,
        'prix_menu'       => $prix['prix_menu'],
        'prix_livraison'  => $prix['prix_livraison'],
        'prix_total'      => $prix['prix_total'],
        'reduction'       => (int) $prix['reduction_10'],
    ]);
    $commandeId = (int) $pdo->lastInsertId();

    // Premier jalon du suivi historisé
    $pdo->prepare('INSERT INTO suivi_commande (commande_id, statut) VALUES (:id, \'cree\')')
        ->execute(['id' => $commandeId]);

    // Décrément du stock du menu
    $pdo->prepare('UPDATE menu SET quantite_restante = quantite_restante - 1 WHERE menu_id = :id')
        ->execute(['id' => $menuId]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('create-commande : ' . $e->getMessage());
    repondre(500, ['erreur' => 'La commande n\'a pas pu être enregistrée. Réessayez.']);
}

/* ---------- Statistiques NoSQL (miroir MongoDB, best effort) ---------- */

statsInsererCommande($numero, $menuId, $menu['titre'], $nbPersonnes, $prix['prix_total'], 'cree');

/* ---------- Mail de confirmation automatique ---------- */

$requete = $pdo->prepare('SELECT email, prenom FROM utilisateur WHERE utilisateur_id = :id');
$requete->execute(['id' => $utilisateur['id']]);
$client = $requete->fetch();
if ($client !== false) {
    mailConfirmationCommande($client['email'], $client['prenom'], $numero, $menu['titre'], $prix, $nbPersonnes, $datePresta, $heure);
}

repondre(201, [
    'message'  => 'Commande enregistrée ! Un mail de confirmation vous a été envoyé.',
    'commande' => [
        'numero_commande' => $numero,
        'menu'            => $menu['titre'],
        'nb_personnes'    => $nbPersonnes,
        'prix_menu'       => $prix['prix_menu'],
        'prix_livraison'  => $prix['prix_livraison'],
        'prix_total'      => $prix['prix_total'],
        'reduction_10'    => $prix['reduction_10'],
    ],
]);
