<?php
/**
 * GET /backend/plats/get-plats.php — réservé employé/admin.
 * Catalogue des plats avec leurs allergènes + référentiel des allergènes
 * (pour composer les menus et gérer les plats depuis l'espace employé).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

exigerMethode('GET');
exigerRole('employe', 'administrateur');

$pdo = getPDO();

$plats = [];
$lignes = $pdo->query(
    'SELECT p.plat_id, p.titre, p.type, a.allergene_id, a.libelle AS allergene
     FROM plat p
     LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
     LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
     ORDER BY FIELD(p.type, \'entree\', \'plat\', \'dessert\'), p.titre'
)->fetchAll();

foreach ($lignes as $ligne) {
    $id = (int) $ligne['plat_id'];
    if (!isset($plats[$id])) {
        $plats[$id] = [
            'plat_id'    => $id,
            'titre'      => $ligne['titre'],
            'type'       => $ligne['type'],
            'allergenes' => [],
        ];
    }
    if ($ligne['allergene_id'] !== null) {
        $plats[$id]['allergenes'][] = [
            'allergene_id' => (int) $ligne['allergene_id'],
            'libelle'      => $ligne['allergene'],
        ];
    }
}

repondre(200, [
    'plats'      => array_values($plats),
    'allergenes' => $pdo->query('SELECT allergene_id, libelle FROM allergene ORDER BY libelle')->fetchAll(),
]);
