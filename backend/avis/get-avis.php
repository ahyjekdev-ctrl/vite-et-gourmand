<?php
/**
 * GET /backend/avis/get-avis.php
 * Avis clients VALIDÉS uniquement (exigence : seuls les avis validés
 * par un employé apparaissent sur la page d'accueil).
 * Le nom de famille est réduit à son initiale : minimisation RGPD.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';

exigerMethode('GET');

$requete = getPDO()->query(
    "SELECT a.note, a.description,
            CONCAT(u.prenom, ' ', LEFT(u.nom, 1), '.') AS auteur, u.ville
     FROM avis a
     JOIN commande c ON c.commande_id = a.commande_id
     JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
     WHERE a.statut = 'valide'
     ORDER BY a.avis_id DESC
     LIMIT 6"
);

repondre(200, ['avis' => $requete->fetchAll()]);
