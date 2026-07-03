<?php
/**
 * POST /backend/auth/logout.php
 * Déconnexion : destruction complète de la session.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/session.php';

exigerMethode('POST');
demarrerSession();

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

repondre(200, ['message' => 'Déconnexion réussie.']);
