<?php
/**
 * GET /backend/commandes/get-commandes.php
 * - Utilisateur : uniquement SES commandes, avec le suivi complet.
 * - Employé / administrateur : toutes les commandes, avec filtres
 *   ?statut=accepte et/ou ?client=texte (nom, prénom ou mail).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

exigerMethode('GET');
$utilisateur = exigerAuthentification();
$pdo = getPDO();

$conditions = [];
$parametres = [];

if ($utilisateur['role'] === 'utilisateur') {
    // Un client ne voit que ses propres commandes
    $conditions[] = 'c.utilisateur_id = :utilisateur_id';
    $parametres['utilisateur_id'] = $utilisateur['id'];
} else {
    // Filtres de l'espace employé : par statut ou par client
    $statutsValides = ['cree', 'accepte', 'en_preparation', 'en_livraison',
                       'livre', 'attente_materiel', 'terminee', 'annulee'];
    if (isset($_GET['statut']) && in_array($_GET['statut'], $statutsValides, true)) {
        $conditions[] = 'c.statut = :statut';
        $parametres['statut'] = $_GET['statut'];
    }
    if (!empty($_GET['client']) && is_string($_GET['client'])) {
        // Trois paramètres distincts : avec les requêtes préparées réelles
        // (EMULATE_PREPARES désactivé), un même nom ne peut pas être réutilisé
        $conditions[] = '(u.nom LIKE :client_nom OR u.prenom LIKE :client_prenom OR u.email LIKE :client_email)';
        $recherche = '%' . trim($_GET['client']) . '%';
        $parametres['client_nom'] = $recherche;
        $parametres['client_prenom'] = $recherche;
        $parametres['client_email'] = $recherche;
    }
}

$sql = 'SELECT c.commande_id, c.numero_commande, c.date_commande, c.date_prestation,
               c.heure_livraison, c.adresse_livraison, c.ville_livraison, c.distance_km,
               c.nb_personnes, c.prix_menu, c.prix_livraison, c.prix_total, c.reduction_10,
               c.statut, c.pret_materiel, c.materiel_restitue, c.motif_annulation, c.mode_contact,
               m.menu_id, m.titre AS menu, m.nb_personnes_min,
               u.nom AS client_nom, u.prenom AS client_prenom, u.email AS client_email,
               (SELECT COUNT(*) FROM avis a WHERE a.commande_id = c.commande_id) AS avis_depose
        FROM commande c
        JOIN menu m ON m.menu_id = c.menu_id
        JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id'
     . ($conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '')
     . ' ORDER BY c.date_commande DESC';

$requete = $pdo->prepare($sql);
$requete->execute($parametres);
$commandes = $requete->fetchAll();

/* ---------- Suivi historisé de chaque commande (état + date/heure) ---------- */

if ($commandes !== []) {
    $ids = array_column($commandes, 'commande_id');
    $emplacements = implode(',', array_fill(0, count($ids), '?'));
    $requete = $pdo->prepare(
        "SELECT commande_id, statut, date_heure
         FROM suivi_commande
         WHERE commande_id IN ($emplacements)
         ORDER BY suivi_id"
    );
    $requete->execute($ids);

    $suivis = [];
    foreach ($requete->fetchAll() as $ligne) {
        $suivis[(int) $ligne['commande_id']][] = [
            'statut'     => $ligne['statut'],
            'date_heure' => $ligne['date_heure'],
        ];
    }
    foreach ($commandes as &$commande) {
        $commande['suivi'] = $suivis[(int) $commande['commande_id']] ?? [];
    }
    unset($commande);
}

repondre(200, ['total' => count($commandes), 'commandes' => $commandes]);
