<?php
/**
 * GET /backend/menus/get-menus.php
 * Vue globale des menus actifs, accessible sans authentification.
 *
 * Filtres (paramètres d'URL, tous optionnels et cumulables) :
 *   prix_max=350            → prix ≤ 350 €
 *   prix_de=100&prix_a=300  → fourchette de prix
 *   theme=1                 → identifiant du thème
 *   regime=2                → identifiant du régime
 *   personnes=8             → menus accessibles pour ce nombre de convives
 *                             (nombre minimum du menu ≤ valeur saisie)
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';

exigerMethode('GET');

$conditions = ['m.actif = 1'];
$parametres = [];

/** Lit un paramètre d'URL numérique positif (null si absent ou invalide). */
function parametreNumerique(string $cle): ?float
{
    if (!isset($_GET[$cle]) || $_GET[$cle] === '' || !is_numeric($_GET[$cle])) {
        return null;
    }
    $valeur = (float) $_GET[$cle];
    return $valeur >= 0 ? $valeur : null;
}

if (($prixMax = parametreNumerique('prix_max')) !== null) {
    $conditions[] = 'm.prix_min <= :prix_max';
    $parametres['prix_max'] = $prixMax;
}
if (($prixDe = parametreNumerique('prix_de')) !== null) {
    $conditions[] = 'm.prix_min >= :prix_de';
    $parametres['prix_de'] = $prixDe;
}
if (($prixA = parametreNumerique('prix_a')) !== null) {
    $conditions[] = 'm.prix_min <= :prix_a';
    $parametres['prix_a'] = $prixA;
}
if (($theme = parametreNumerique('theme')) !== null) {
    $conditions[] = 'm.theme_id = :theme';
    $parametres['theme'] = (int) $theme;
}
if (($regime = parametreNumerique('regime')) !== null) {
    $conditions[] = 'm.regime_id = :regime';
    $parametres['regime'] = (int) $regime;
}
if (($personnes = parametreNumerique('personnes')) !== null) {
    $conditions[] = 'm.nb_personnes_min <= :personnes';
    $parametres['personnes'] = (int) $personnes;
}

$sql = 'SELECT m.menu_id, m.titre, m.description, m.nb_personnes_min, m.prix_min,
               m.quantite_restante, t.libelle AS theme, r.libelle AS regime,
               (SELECT i.alt FROM image_menu i
                WHERE i.menu_id = m.menu_id ORDER BY i.position LIMIT 1) AS image_alt
        FROM menu m
        JOIN theme t ON t.theme_id = m.theme_id
        JOIN regime r ON r.regime_id = m.regime_id
        WHERE ' . implode(' AND ', $conditions) . '
        ORDER BY m.titre';

$requete = getPDO()->prepare($sql);
$requete->execute($parametres);
$menus = $requete->fetchAll();

repondre(200, ['total' => count($menus), 'menus' => $menus]);
