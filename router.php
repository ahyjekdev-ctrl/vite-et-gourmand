<?php
/**
 * Routeur du serveur de développement — SÉCURITÉ.
 * Usage : php -S localhost:8000 router.php
 *
 * Sans ce routeur, le serveur intégré de PHP sert TOUT le dépôt :
 * .env (identifiants BDD), backend/mail/mails.log (tokens de
 * réinitialisation), database/*.sql (hashs de mots de passe)…
 * Vulnérabilité trouvée lors de la passe sécurité, corrigée ici par
 * une LISTE BLANCHE : seuls le front et les endpoints API sont servis.
 *
 * En production (Apache), le fichier .htaccess applique le même principe.
 */

declare(strict_types=1);

$chemin = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Racine du site → redirection vers la page d'accueil
if ($chemin === '/' || $chemin === '/index.php') {
    require __DIR__ . '/index.php';
    return true;
}

// Endpoints API autorisés : un fichier PHP dans un module connu du backend
if (preg_match('#^/backend/(auth|menus|commandes|avis|plats|horaires|admin|contact)/[a-z0-9-]+\.php$#', $chemin) === 1
    && is_file(__DIR__ . $chemin)) {
    return false; // le serveur exécute le script PHP
}

// Fichiers du front (pages, CSS, JS, images)
if (str_starts_with($chemin, '/frontend/') && is_file(__DIR__ . $chemin)) {
    return false; // le serveur sert le fichier statique
}

// Tout le reste (.env, database/, docs/, logs, .git…) : introuvable
http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo 'Ressource introuvable.';
return true;
