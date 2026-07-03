<?php
/**
 * POST /backend/menus/create-menu.php
 * Création d'un menu — réservé aux rôles employé et administrateur.
 * Corps JSON : titre, description, nb_personnes_min, prix_min, conditions,
 * quantite_restante, theme_id, regime_id, plats[] (ids), images[] {chemin, alt}.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

exigerMethode('POST');
exigerRole('employe', 'administrateur');
$donnees = lireJson();

/* ---------- Validation ---------- */

$erreurs = [];
$titre       = champTexte($donnees, 'titre');
$description = champTexte($donnees, 'description');
$conditions  = champTexte($donnees, 'conditions');
$nbMin       = $donnees['nb_personnes_min'] ?? null;
$prixMin     = $donnees['prix_min'] ?? null;
$stock       = $donnees['quantite_restante'] ?? 0;
$themeId     = $donnees['theme_id'] ?? null;
$regimeId    = $donnees['regime_id'] ?? null;
$plats       = $donnees['plats'] ?? [];
$images      = $donnees['images'] ?? [];

if ($titre === '')       { $erreurs['titre'] = 'Le titre est obligatoire.'; }
if ($description === '') { $erreurs['description'] = 'La description est obligatoire.'; }
if ($conditions === '')  { $erreurs['conditions'] = 'Les conditions du menu sont obligatoires.'; }
if (!is_numeric($nbMin) || (int) $nbMin < 1)   { $erreurs['nb_personnes_min'] = 'Nombre de personnes minimum invalide.'; }
if (!is_numeric($prixMin) || (float) $prixMin < 0) { $erreurs['prix_min'] = 'Prix invalide.'; }
if (!is_numeric($stock) || (int) $stock < 0)   { $erreurs['quantite_restante'] = 'Stock invalide.'; }
if (!is_numeric($themeId))  { $erreurs['theme_id'] = 'Thème obligatoire.'; }
if (!is_numeric($regimeId)) { $erreurs['regime_id'] = 'Régime obligatoire.'; }
if (!is_array($plats) || $plats === []) { $erreurs['plats'] = 'Un menu doit proposer au moins un plat.'; }

if ($erreurs !== []) {
    repondre(422, ['erreur' => 'Formulaire invalide.', 'champs' => $erreurs]);
}

/* ---------- Insertion (transaction : menu + plats + images, tout ou rien) ---------- */

$pdo = getPDO();
$pdo->beginTransaction();

try {
    $requete = $pdo->prepare(
        'INSERT INTO menu (titre, description, nb_personnes_min, prix_min, conditions,
                           quantite_restante, theme_id, regime_id)
         VALUES (:titre, :description, :nb_min, :prix_min, :conditions, :stock, :theme_id, :regime_id)'
    );
    $requete->execute([
        'titre'       => $titre,
        'description' => $description,
        'nb_min'      => (int) $nbMin,
        'prix_min'    => (float) $prixMin,
        'conditions'  => $conditions,
        'stock'       => (int) $stock,
        'theme_id'    => (int) $themeId,
        'regime_id'   => (int) $regimeId,
    ]);
    $menuId = (int) $pdo->lastInsertId();

    $lien = $pdo->prepare('INSERT INTO menu_plat (menu_id, plat_id) VALUES (:menu_id, :plat_id)');
    foreach (array_unique(array_map('intval', $plats)) as $platId) {
        $lien->execute(['menu_id' => $menuId, 'plat_id' => $platId]);
    }

    $image = $pdo->prepare(
        'INSERT INTO image_menu (menu_id, chemin, alt, position) VALUES (:menu_id, :chemin, :alt, :position)'
    );
    foreach (array_values($images) as $position => $img) {
        $image->execute([
            'menu_id'  => $menuId,
            'chemin'   => (string) ($img['chemin'] ?? ''),
            'alt'      => (string) ($img['alt'] ?? $titre),
            'position' => $position + 1,
        ]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('create-menu : ' . $e->getMessage());
    repondre(422, ['erreur' => 'Création impossible : vérifiez les plats, le thème et le régime.']);
}

repondre(201, ['message' => 'Menu créé.', 'menu_id' => $menuId]);
