<?php
/**
 * POST /backend/menus/update-menu.php
 * Modification d'un menu — réservé aux rôles employé et administrateur.
 * Corps JSON : menu_id + les champs à modifier (titre, description,
 * nb_personnes_min, prix_min, conditions, quantite_restante, theme_id,
 * regime_id, actif, plats[] pour remplacer la composition).
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
$menuId = (int) $menuId;

$pdo = getPDO();

$requete = $pdo->prepare('SELECT COUNT(*) FROM menu WHERE menu_id = :id');
$requete->execute(['id' => $menuId]);
if ((int) $requete->fetchColumn() === 0) {
    repondre(404, ['erreur' => 'Menu introuvable.']);
}

/* ---------- Champs modifiables : seuls ceux transmis sont mis à jour ---------- */

$colonnes = [
    'titre'             => 'texte',
    'description'       => 'texte',
    'conditions'        => 'texte',
    'nb_personnes_min'  => 'entier',
    'prix_min'          => 'decimal',
    'quantite_restante' => 'entier',
    'theme_id'          => 'entier',
    'regime_id'         => 'entier',
    'actif'             => 'entier',
];

$affectations = [];
$parametres = ['menu_id' => $menuId];

foreach ($colonnes as $colonne => $type) {
    if (!array_key_exists($colonne, $donnees)) {
        continue;
    }
    $valeur = $donnees[$colonne];
    if ($type === 'texte') {
        $valeur = trim((string) $valeur);
        if ($valeur === '') {
            repondre(422, ['erreur' => "Le champ « $colonne » ne peut pas être vide."]);
        }
    } elseif (!is_numeric($valeur) || (float) $valeur < 0) {
        repondre(422, ['erreur' => "Le champ « $colonne » doit être un nombre positif."]);
    } else {
        $valeur = $type === 'entier' ? (int) $valeur : (float) $valeur;
    }
    $affectations[] = "$colonne = :$colonne";
    $parametres[$colonne] = $valeur;
}

$plats = $donnees['plats'] ?? null;
if ($affectations === [] && $plats === null) {
    repondre(400, ['erreur' => 'Aucun champ à modifier.']);
}

/* ---------- Mise à jour (transaction) ---------- */

$pdo->beginTransaction();
try {
    if ($affectations !== []) {
        $sql = 'UPDATE menu SET ' . implode(', ', $affectations) . ' WHERE menu_id = :menu_id';
        $pdo->prepare($sql)->execute($parametres);
    }

    // Remplacement complet de la composition si une liste de plats est fournie
    if (is_array($plats)) {
        if ($plats === []) {
            repondre(422, ['erreur' => 'Un menu doit proposer au moins un plat.']);
        }
        $pdo->prepare('DELETE FROM menu_plat WHERE menu_id = :id')->execute(['id' => $menuId]);
        $lien = $pdo->prepare('INSERT INTO menu_plat (menu_id, plat_id) VALUES (:menu_id, :plat_id)');
        foreach (array_unique(array_map('intval', $plats)) as $platId) {
            $lien->execute(['menu_id' => $menuId, 'plat_id' => $platId]);
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('update-menu : ' . $e->getMessage());
    repondre(422, ['erreur' => 'Modification impossible : vérifiez les valeurs transmises.']);
}

repondre(200, ['message' => 'Menu mis à jour.']);
