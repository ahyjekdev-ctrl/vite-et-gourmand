<?php
/**
 * POST /backend/plats/delete-plat.php — réservé employé/admin.
 * Suppression d'un plat. Les liaisons menu_plat et plat_allergene sont
 * supprimées en cascade (contraintes FK) ; les commandes ne référencent
 * pas les plats directement, l'historique n'est donc pas affecté.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

exigerMethode('POST');
exigerRole('employe', 'administrateur');
$donnees = lireJson();

$platId = $donnees['plat_id'] ?? null;
if (!is_numeric($platId) || (int) $platId <= 0) {
    repondre(400, ['erreur' => 'Identifiant de plat manquant.']);
}

$requete = getPDO()->prepare('DELETE FROM plat WHERE plat_id = :id');
$requete->execute(['id' => (int) $platId]);

if ($requete->rowCount() === 0) {
    repondre(404, ['erreur' => 'Plat introuvable.']);
}

repondre(200, ['message' => 'Plat supprimé du catalogue.']);
