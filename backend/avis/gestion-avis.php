<?php
/**
 * /backend/avis/gestion-avis.php — dépôt et modération des avis.
 *
 * GET  (employé/admin)  : liste des avis avec leur statut (?statut=en_attente pour la modération)
 * POST (client)         : { "action": "deposer", "commande_id": 4, "note": 5, "description": "..." }
 *                         → uniquement sur SA commande, terminée, sans avis existant
 * POST (employé/admin)  : { "action": "moderer", "avis_id": 4, "decision": "valide" | "refuse" }
 *                         → seuls les avis validés apparaissent sur l'accueil
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$pdo = getPDO();

/* ---------- GET : liste pour l'espace employé ---------- */

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
    exigerRole('employe', 'administrateur');

    $condition = '';
    $parametres = [];
    if (isset($_GET['statut']) && in_array($_GET['statut'], ['en_attente', 'valide', 'refuse'], true)) {
        $condition = 'WHERE a.statut = :statut';
        $parametres['statut'] = $_GET['statut'];
    }

    $requete = $pdo->prepare(
        "SELECT a.avis_id, a.note, a.description, a.statut,
                c.numero_commande, m.titre AS menu,
                CONCAT(u.prenom, ' ', LEFT(u.nom, 1), '.') AS auteur
         FROM avis a
         JOIN commande c ON c.commande_id = a.commande_id
         JOIN menu m ON m.menu_id = c.menu_id
         JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
         $condition
         ORDER BY a.avis_id DESC"
    );
    $requete->execute($parametres);
    repondre(200, ['avis' => $requete->fetchAll()]);
}

/* ---------- POST : dépôt (client) ou modération (équipe) ---------- */

exigerMethode('POST');
$utilisateur = exigerAuthentification();
$donnees = lireJson();
$action = champTexte($donnees, 'action');

if ($action === 'deposer') {
    $commandeId  = $donnees['commande_id'] ?? null;
    $note        = $donnees['note'] ?? null;
    $description = champTexte($donnees, 'description');

    if (!is_numeric($commandeId)) {
        repondre(400, ['erreur' => 'Commande manquante.']);
    }
    if (!is_numeric($note) || (int) $note < 1 || (int) $note > 5) {
        repondre(422, ['erreur' => 'La note doit être comprise entre 1 et 5.']);
    }
    if ($description === '') {
        repondre(422, ['erreur' => 'Le commentaire est obligatoire.']);
    }

    // La commande doit appartenir au client, être terminée et sans avis
    $requete = $pdo->prepare(
        'SELECT c.statut,
                (SELECT COUNT(*) FROM avis a WHERE a.commande_id = c.commande_id) AS deja_note
         FROM commande c
         WHERE c.commande_id = :id AND c.utilisateur_id = :utilisateur_id'
    );
    $requete->execute(['id' => (int) $commandeId, 'utilisateur_id' => $utilisateur['id']]);
    $commande = $requete->fetch();

    if ($commande === false) {
        repondre(404, ['erreur' => 'Commande introuvable.']);
    }
    if ($commande['statut'] !== 'terminee') {
        repondre(409, ['erreur' => 'Vous pourrez donner votre avis quand la commande sera terminée.']);
    }
    if ((int) $commande['deja_note'] > 0) {
        repondre(409, ['erreur' => 'Un avis a déjà été déposé pour cette commande.']);
    }

    $requete = $pdo->prepare(
        'INSERT INTO avis (commande_id, note, description) VALUES (:id, :note, :description)'
    );
    $requete->execute([
        'id'          => (int) $commandeId,
        'note'        => (int) $note,
        'description' => $description,
    ]);

    repondre(201, ['message' => 'Merci pour votre avis ! Il sera visible après validation par notre équipe.']);
}

if ($action === 'moderer') {
    if (!in_array($utilisateur['role'], ['employe', 'administrateur'], true)) {
        repondre(403, ['erreur' => 'Seule l\'équipe peut modérer les avis.']);
    }

    $avisId   = $donnees['avis_id'] ?? null;
    $decision = champTexte($donnees, 'decision');

    if (!is_numeric($avisId)) {
        repondre(400, ['erreur' => 'Avis manquant.']);
    }
    if (!in_array($decision, ['valide', 'refuse'], true)) {
        repondre(422, ['erreur' => 'Décision invalide : « valide » ou « refuse ».']);
    }

    $requete = $pdo->prepare('UPDATE avis SET statut = :decision WHERE avis_id = :id');
    $requete->execute(['decision' => $decision, 'id' => (int) $avisId]);

    if ($requete->rowCount() === 0) {
        repondre(404, ['erreur' => 'Avis introuvable.']);
    }

    repondre(200, ['message' => $decision === 'valide'
        ? 'Avis validé : il apparaît maintenant sur la page d\'accueil.'
        : 'Avis refusé : il ne sera pas publié.']);
}

repondre(400, ['erreur' => 'Action inconnue (deposer ou moderer).']);
