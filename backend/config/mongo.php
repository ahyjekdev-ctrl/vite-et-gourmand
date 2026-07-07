<?php
/**
 * Accès à la base NoSQL (MongoDB) — composant d'accès aux données.
 *
 * La collection `stats_commandes` est le miroir statistique des commandes :
 * elle alimente l'espace administrateur (nombre de commandes et chiffre
 * d'affaires par menu, filtrables par période) sans jamais toucher à MySQL.
 *
 * Choix d'architecture : les écritures statistiques sont « best effort » —
 * si MongoDB est indisponible, la commande MySQL aboutit quand même
 * (l'erreur est journalisée). La vente prime sur la statistique.
 */

declare(strict_types=1);

require_once __DIR__ . '/database.php'; // pour chargerEnv()

/** Connexion MongoDB (unique pour la requête). */
function getMongo(): MongoDB\Driver\Manager
{
    static $manager = null;
    if ($manager === null) {
        $env = chargerEnv();
        $manager = new MongoDB\Driver\Manager($env['MONGO_URI'] ?? 'mongodb://localhost:27017');
    }
    return $manager;
}

/** Nom complet de la collection (base.collection). */
function statsNamespace(): string
{
    $env = chargerEnv();
    return ($env['MONGO_DB'] ?? 'vite_et_gourmand_stats') . '.stats_commandes';
}

/**
 * Insère l'entrée statistique d'une nouvelle commande.
 */
function statsInsererCommande(
    string $numero,
    int $menuId,
    string $titreMenu,
    int $nbPersonnes,
    float $prixTotal,
    string $statut
): void {
    try {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->insert([
            'numero_commande' => $numero,
            'menu_id'         => $menuId,
            'titre_menu'      => $titreMenu,
            'date_commande'   => new MongoDB\BSON\UTCDateTime(),
            'nb_personnes'    => $nbPersonnes,
            'prix_total'      => $prixTotal,
            'statut'          => $statut,
        ]);
        getMongo()->executeBulkWrite(statsNamespace(), $bulk);
    } catch (Throwable $e) {
        error_log('stats mongo (insertion) : ' . $e->getMessage());
    }
}

/**
 * Met à jour l'entrée statistique d'une commande (changement de statut,
 * ou nouveau prix/nombre de personnes après modification).
 */
function statsMajCommande(string $numero, array $champs): void
{
    try {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->update(['numero_commande' => $numero], ['$set' => $champs]);
        getMongo()->executeBulkWrite(statsNamespace(), $bulk);
    } catch (Throwable $e) {
        error_log('stats mongo (mise à jour) : ' . $e->getMessage());
    }
}

/**
 * Exécute un pipeline d'agrégation sur la collection des statistiques.
 *
 * @return array Documents résultats (tableaux associatifs)
 */
function statsAgreger(array $pipeline): array
{
    $env = chargerEnv();
    $base = $env['MONGO_DB'] ?? 'vite_et_gourmand_stats';

    $commande = new MongoDB\Driver\Command([
        'aggregate' => 'stats_commandes',
        'pipeline'  => $pipeline,
        'cursor'    => new stdClass(),
    ]);
    $curseur = getMongo()->executeCommand($base, $commande);
    $curseur->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
    return $curseur->toArray();
}
