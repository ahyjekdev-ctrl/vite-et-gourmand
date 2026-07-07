<?php
/**
 * POST /backend/commandes/update-commande.php
 * Toutes les évolutions d'une commande, selon le rôle :
 *
 * CLIENT (propriétaire, tant que la commande n'est pas « acceptée ») :
 *   { "commande_id": 7, "action": "annuler" }
 *   { "commande_id": 7, "action": "modifier", ...champs }   → tout sauf le menu
 *
 * EMPLOYÉ / ADMINISTRATEUR :
 *   { "commande_id": 7, "action": "changer_statut", "statut": "accepte", "pret_materiel": true? }
 *   { "commande_id": 7, "action": "annuler", "motif": "...", "mode_contact": "gsm|mail" }
 *   { "commande_id": 7, "action": "modifier", "motif": "...", "mode_contact": "...", ...champs }
 *   → motif et mode de contact OBLIGATOIRES : l'employé doit avoir
 *     contacté le client avant de modifier ou d'annuler sa commande.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../mail/send-mail.php';
require_once __DIR__ . '/calcul-prix.php';

exigerMethode('POST');
$utilisateur = exigerAuthentification();
$donnees = lireJson();

$commandeId = $donnees['commande_id'] ?? null;
$action     = champTexte($donnees, 'action');

if (!is_numeric($commandeId) || (int) $commandeId <= 0) {
    repondre(400, ['erreur' => 'Identifiant de commande manquant.']);
}
$commandeId = (int) $commandeId;

$pdo = getPDO();

$requete = $pdo->prepare(
    'SELECT c.*, m.prix_min, m.nb_personnes_min, m.titre AS menu_titre,
            u.email AS client_email, u.prenom AS client_prenom
     FROM commande c
     JOIN menu m ON m.menu_id = c.menu_id
     JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
     WHERE c.commande_id = :id'
);
$requete->execute(['id' => $commandeId]);
$commande = $requete->fetch();

if ($commande === false) {
    repondre(404, ['erreur' => 'Commande introuvable.']);
}

$estClient = $utilisateur['role'] === 'utilisateur';
$estEquipe = in_array($utilisateur['role'], ['employe', 'administrateur'], true);

// Un client ne touche qu'à SES commandes
if ($estClient && (int) $commande['utilisateur_id'] !== $utilisateur['id']) {
    repondre(403, ['erreur' => 'Cette commande ne vous appartient pas.']);
}

/* ---------- Aides ---------- */

/** Ajoute un jalon daté dans le suivi historisé. */
function ajouterSuivi(PDO $pdo, int $commandeId, string $statut): void
{
    $pdo->prepare('INSERT INTO suivi_commande (commande_id, statut) VALUES (:id, :statut)')
        ->execute(['id' => $commandeId, 'statut' => $statut]);
}

/** Restitue une commande au stock du menu (annulation). */
function restituerStock(PDO $pdo, int $menuId): void
{
    $pdo->prepare('UPDATE menu SET quantite_restante = quantite_restante + 1 WHERE menu_id = :id')
        ->execute(['id' => $menuId]);
}

/** L'équipe doit justifier tout changement : motif + mode de contact. */
function exigerMotifContact(array $donnees): array
{
    $motif = champTexte($donnees, 'motif');
    $mode  = champTexte($donnees, 'mode_contact');
    if ($motif === '' || !in_array($mode, ['gsm', 'mail'], true)) {
        repondre(422, ['erreur' => 'Contactez d\'abord le client : le motif et le mode de contact (gsm ou mail) sont obligatoires.']);
    }
    return [$motif, $mode];
}

/* ============================================================
   ACTION : annuler
   ============================================================ */

if ($action === 'annuler') {
    if (in_array($commande['statut'], ['terminee', 'annulee'], true)) {
        repondre(409, ['erreur' => 'Cette commande est déjà ' . ($commande['statut'] === 'annulee' ? 'annulée' : 'terminée') . '.']);
    }

    if ($estClient) {
        // Annulation libre uniquement tant que l'équipe n'a pas accepté
        if ($commande['statut'] !== 'cree') {
            repondre(409, ['erreur' => 'Commande déjà acceptée : contactez l\'équipe pour toute annulation.']);
        }
        $motif = 'Annulée par le client';
        $mode = null;
    } else {
        [$motif, $mode] = exigerMotifContact($donnees);
    }

    $pdo->beginTransaction();
    $pdo->prepare(
        'UPDATE commande SET statut = \'annulee\', motif_annulation = :motif, mode_contact = :mode
         WHERE commande_id = :id'
    )->execute(['motif' => $motif, 'mode' => $mode, 'id' => $commandeId]);
    ajouterSuivi($pdo, $commandeId, 'annulee');
    restituerStock($pdo, (int) $commande['menu_id']);
    $pdo->commit();

    repondre(200, ['message' => 'Commande annulée.']);
}

/* ============================================================
   ACTION : modifier (tout sauf le choix du menu)
   ============================================================ */

if ($action === 'modifier') {
    if ($estClient && $commande['statut'] !== 'cree') {
        repondre(409, ['erreur' => 'Commande déjà acceptée : elle ne peut plus être modifiée en ligne.']);
    }
    if ($estEquipe) {
        [$motif, $mode] = exigerMotifContact($donnees);
        $pdo->prepare('UPDATE commande SET motif_annulation = :motif, mode_contact = :mode WHERE commande_id = :id')
            ->execute(['motif' => $motif, 'mode' => $mode, 'id' => $commandeId]);
    }
    if (array_key_exists('menu_id', $donnees)) {
        repondre(422, ['erreur' => 'Le choix du menu n\'est pas modifiable : annulez puis commandez à nouveau.']);
    }

    // Valeurs mises à jour (les champs absents gardent leur valeur actuelle)
    $datePresta = champTexte($donnees, 'date_prestation') ?: $commande['date_prestation'];
    $heure      = champTexte($donnees, 'heure_livraison') ?: substr((string) $commande['heure_livraison'], 0, 5);
    $adresse    = champTexte($donnees, 'adresse_livraison') ?: $commande['adresse_livraison'];
    $ville      = champTexte($donnees, 'ville_livraison') ?: $commande['ville_livraison'];
    $nb         = isset($donnees['nb_personnes']) && is_numeric($donnees['nb_personnes'])
                    ? (int) $donnees['nb_personnes'] : (int) $commande['nb_personnes'];
    $distance   = isset($donnees['distance_km']) && is_numeric($donnees['distance_km'])
                    ? (float) $donnees['distance_km'] : (float) $commande['distance_km'];

    $dateValide = DateTime::createFromFormat('Y-m-d', $datePresta);
    if ($dateValide === false || $dateValide < new DateTime('today')) {
        repondre(422, ['erreur' => 'Date de prestation invalide ou passée.']);
    }
    if ($nb < (int) $commande['nb_personnes_min']) {
        repondre(422, ['erreur' => "Ce menu se commande pour {$commande['nb_personnes_min']} personnes minimum."]);
    }
    if (!estBordeaux($ville) && $distance <= 0) {
        repondre(422, ['erreur' => 'Hors Bordeaux, indiquez la distance en kilomètres.']);
    }

    // Le prix est recalculé avec les mêmes règles qu'à la création
    $menu = ['prix_min' => $commande['prix_min'], 'nb_personnes_min' => $commande['nb_personnes_min']];
    $prix = calculerPrix($menu, $nb, $ville, $distance);

    $pdo->prepare(
        'UPDATE commande SET date_prestation = :date_prestation, heure_livraison = :heure,
                adresse_livraison = :adresse, ville_livraison = :ville, distance_km = :distance,
                nb_personnes = :nb, prix_menu = :prix_menu, prix_livraison = :prix_livraison,
                prix_total = :prix_total, reduction_10 = :reduction
         WHERE commande_id = :id'
    )->execute([
        'date_prestation' => $datePresta,
        'heure'           => $heure,
        'adresse'         => $adresse,
        'ville'           => $ville,
        'distance'        => $prix['distance_km'],
        'nb'              => $nb,
        'prix_menu'       => $prix['prix_menu'],
        'prix_livraison'  => $prix['prix_livraison'],
        'prix_total'      => $prix['prix_total'],
        'reduction'       => (int) $prix['reduction_10'],
        'id'              => $commandeId,
    ]);

    repondre(200, ['message' => 'Commande mise à jour.', 'prix' => $prix]);
}

/* ============================================================
   ACTION : changer_statut (équipe uniquement) — workflow du cahier des charges
   ============================================================ */

if ($action === 'changer_statut') {
    if (!$estEquipe) {
        repondre(403, ['erreur' => 'Seule l\'équipe peut faire évoluer le statut d\'une commande.']);
    }

    $nouveau = champTexte($donnees, 'statut');

    // Transitions autorisées depuis chaque statut
    $transitions = [
        'cree'             => ['accepte'],
        'accepte'          => ['en_preparation'],
        'en_preparation'   => ['en_livraison'],
        'en_livraison'     => ['livre'],
        'livre'            => ['attente_materiel', 'terminee'],
        'attente_materiel' => ['terminee'],
    ];

    $possibles = $transitions[$commande['statut']] ?? [];
    if (!in_array($nouveau, $possibles, true)) {
        repondre(422, [
            'erreur' => "Passage impossible de « {$commande['statut']} » à « $nouveau ».",
            'statuts_possibles' => $possibles,
        ]);
    }

    $pdo->beginTransaction();

    $champsSupplementaires = '';
    $parametres = ['statut' => $nouveau, 'id' => $commandeId];

    if ($nouveau === 'attente_materiel') {
        // Du matériel a été prêté : il devra être restitué
        $champsSupplementaires = ', pret_materiel = 1, materiel_restitue = 0';
    }
    if ($nouveau === 'terminee' && $commande['statut'] === 'attente_materiel') {
        $champsSupplementaires = ', materiel_restitue = 1';
    }

    $pdo->prepare("UPDATE commande SET statut = :statut$champsSupplementaires WHERE commande_id = :id")
        ->execute($parametres);
    ajouterSuivi($pdo, $commandeId, $nouveau);
    $pdo->commit();

    // Mails automatiques liés au statut
    if ($nouveau === 'attente_materiel') {
        mailMaterielAttente($commande['client_email'], $commande['client_prenom'], $commande['numero_commande']);
    }
    if ($nouveau === 'terminee') {
        mailInvitationAvis($commande['client_email'], $commande['client_prenom'], $commande['numero_commande']);
    }

    repondre(200, ['message' => "Commande passée au statut « $nouveau »."]);
}

repondre(400, ['erreur' => 'Action inconnue (annuler, modifier ou changer_statut).']);
