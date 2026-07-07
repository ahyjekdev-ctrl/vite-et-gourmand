<?php
/**
 * GET /backend/menus/get-filtres.php
 * Référentiels alimentant les listes déroulantes des filtres
 * (thèmes et régimes), gérés en base et non codés en dur dans le front.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';

exigerMethode('GET');

$pdo = getPDO();

repondre(200, [
    'themes'  => $pdo->query('SELECT theme_id, libelle FROM theme ORDER BY libelle')->fetchAll(),
    'regimes' => $pdo->query('SELECT regime_id, libelle FROM regime ORDER BY libelle')->fetchAll(),
]);
