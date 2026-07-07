<?php
/**
 * Connexion à la base de données via PDO.
 * Les identifiants viennent du fichier .env à la racine du projet (jamais commité).
 */

declare(strict_types=1);

/**
 * Charge les variables du fichier .env (une seule fois par requête).
 *
 * @return array<string, string>
 */
function chargerEnv(): array
{
    static $env = null;
    if ($env !== null) {
        return $env;
    }

    $chemin = dirname(__DIR__, 2) . '/.env';
    if (!is_readable($chemin)) {
        http_response_code(500);
        exit(json_encode(['erreur' => 'Configuration serveur manquante (.env introuvable).']));
    }

    $env = [];
    foreach (file($chemin, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $ligne) {
        $ligne = trim($ligne);
        if ($ligne === '' || str_starts_with($ligne, '#') || !str_contains($ligne, '=')) {
            continue;
        }
        [$cle, $valeur] = explode('=', $ligne, 2);
        $env[trim($cle)] = trim($valeur);
    }
    return $env;
}

/**
 * Retourne la connexion PDO (unique pour toute la requête).
 * - Requêtes préparées réelles (EMULATE_PREPARES désactivé) : protection contre l'injection SQL.
 * - Erreurs en exceptions : aucune erreur silencieuse.
 */
function getPDO(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $env = chargerEnv();

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $env['DB_HOST'] ?? 'localhost',
        $env['DB_NAME'] ?? 'vite_et_gourmand'
    );

    try {
        $pdo = new PDO($dsn, $env['DB_USER'] ?? 'root', $env['DB_PASS'] ?? '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // Ne jamais renvoyer le message brut (il peut contenir l'hôte ou l'utilisateur)
        error_log('Connexion BDD impossible : ' . $e->getMessage());
        http_response_code(500);
        exit(json_encode(['erreur' => 'Service momentanément indisponible.']));
    }

    return $pdo;
}
