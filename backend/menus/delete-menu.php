<?php
/**
 * POST /backend/menus/delete-menu.php
 * Suppression d'un menu — réservé aux rôles employé et administrateur.
 *
 * Suppression « douce » (actif = 0) : le menu disparaît du site mais les
 * commandes passées le référencent toujours (contrainte FK RESTRICT) —
 * l'historique client et les statistiques restent intacts.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

exigerMethode('POST');
exigerRole('employe', 'administrateur');
$donnees = lireJson();

$menuId = $donnees['menu_id'] ?? null;
if (!is_numeric($menuId) || (int) $menuId <= 0) {
    repondre(400, ['erreur' => 'Identifiant de menu manquant ou invalide.']);
}

$requete = getPDO()->prepare('UPDATE menu SET actif = 0 WHERE menu_id = :id');
$requete->execute(['id' => (int) $menuId]);

if ($requete->rowCount() === 0) {
    repondre(404, ['erreur' => 'Menu introuvable ou déjà supprimé.']);
}

repondre(200, ['message' => 'Menu supprimé du catalogue.']);
