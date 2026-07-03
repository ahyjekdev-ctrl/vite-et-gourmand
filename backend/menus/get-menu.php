<?php
/**
 * GET /backend/menus/get-menu.php?id=X
 * Détail complet d'un menu : toutes les informations de la base doivent
 * être visibles (exigence du cahier des charges) — galerie, plats,
 * allergènes, conditions, régime, thème, stock, seuil de réduction.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';

exigerMethode('GET');

$id = isset($_GET['id']) && ctype_digit((string) $_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    repondre(400, ['erreur' => 'Identifiant de menu manquant ou invalide.']);
}

$pdo = getPDO();

$requete = $pdo->prepare(
    'SELECT m.menu_id, m.titre, m.description, m.nb_personnes_min, m.prix_min,
            m.conditions, m.quantite_restante,
            t.theme_id, t.libelle AS theme, r.regime_id, r.libelle AS regime
     FROM menu m
     JOIN theme t ON t.theme_id = m.theme_id
     JOIN regime r ON r.regime_id = m.regime_id
     WHERE m.menu_id = :id AND m.actif = 1'
);
$requete->execute(['id' => $id]);
$menu = $requete->fetch();

if ($menu === false) {
    repondre(404, ['erreur' => 'Menu introuvable.']);
}

// Galerie d'images, dans l'ordre défini
$requete = $pdo->prepare(
    'SELECT chemin, alt FROM image_menu WHERE menu_id = :id ORDER BY position'
);
$requete->execute(['id' => $id]);
$menu['images'] = $requete->fetchAll();

// Plats du menu avec leurs allergènes, groupés par type (entrée / plat / dessert)
$requete = $pdo->prepare(
    'SELECT p.plat_id, p.titre, p.type, a.libelle AS allergene
     FROM menu_plat mp
     JOIN plat p ON p.plat_id = mp.plat_id
     LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
     LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
     WHERE mp.menu_id = :id
     ORDER BY FIELD(p.type, \'entree\', \'plat\', \'dessert\'), p.titre'
);
$requete->execute(['id' => $id]);

$plats = [];
foreach ($requete->fetchAll() as $ligne) {
    $platId = (int) $ligne['plat_id'];
    if (!isset($plats[$platId])) {
        $plats[$platId] = [
            'titre'      => $ligne['titre'],
            'type'       => $ligne['type'],
            'allergenes' => [],
        ];
    }
    if ($ligne['allergene'] !== null) {
        $plats[$platId]['allergenes'][] = $ligne['allergene'];
    }
}
$menu['plats'] = array_values($plats);

// Seuil de la réduction de 10 % : minimum + 5 personnes
$menu['seuil_reduction'] = (int) $menu['nb_personnes_min'] + 5;

repondre(200, ['menu' => $menu]);
