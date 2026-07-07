<?php
/**
 * GET /backend/admin/get-stats.php — réservé à l'ADMINISTRATEUR.
 * Statistiques issues de la base NON relationnelle (MongoDB) :
 *   - nombre de commandes par menu (alimente le graphique comparatif)
 *   - chiffre d'affaires par menu
 * Filtres cumulables : ?menu=3&debut=2026-06-01&fin=2026-06-30
 * Les commandes annulées sont exclues des statistiques.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/mongo.php';

exigerMethode('GET');
exigerRole('administrateur');

/* ---------- Construction du filtre $match ---------- */

$filtre = ['statut' => ['$ne' => 'annulee']];

if (isset($_GET['menu']) && ctype_digit((string) $_GET['menu'])) {
    $filtre['menu_id'] = (int) $_GET['menu'];
}

$plage = [];
if (!empty($_GET['debut']) && ($debut = DateTime::createFromFormat('Y-m-d', $_GET['debut'])) !== false) {
    $plage['$gte'] = new MongoDB\BSON\UTCDateTime($debut->setTime(0, 0));
}
if (!empty($_GET['fin']) && ($fin = DateTime::createFromFormat('Y-m-d', $_GET['fin'])) !== false) {
    $plage['$lte'] = new MongoDB\BSON\UTCDateTime($fin->setTime(23, 59, 59));
}
if ($plage !== []) {
    $filtre['date_commande'] = $plage;
}

/* ---------- Agrégation : nb de commandes + CA par menu ---------- */

try {
    $lignes = statsAgreger([
        ['$match' => $filtre],
        ['$group' => [
            '_id'              => '$menu_id',
            'titre'            => ['$first' => '$titre_menu'],
            'nb_commandes'     => ['$sum' => 1],
            'chiffre_affaires' => ['$sum' => '$prix_total'],
        ]],
        ['$sort' => ['chiffre_affaires' => -1]],
    ]);
} catch (Throwable $e) {
    error_log('get-stats : ' . $e->getMessage());
    repondre(503, ['erreur' => 'Statistiques momentanément indisponibles (base NoSQL injoignable).']);
}

$totalCommandes = 0;
$totalCa = 0.0;
foreach ($lignes as &$ligne) {
    $ligne['menu_id'] = (int) $ligne['_id'];
    unset($ligne['_id']);
    $ligne['chiffre_affaires'] = round((float) $ligne['chiffre_affaires'], 2);
    $totalCommandes += (int) $ligne['nb_commandes'];
    $totalCa += $ligne['chiffre_affaires'];
}
unset($ligne);

repondre(200, [
    'stats'            => $lignes,
    'total_commandes'  => $totalCommandes,
    'total_ca'         => round($totalCa, 2),
]);
