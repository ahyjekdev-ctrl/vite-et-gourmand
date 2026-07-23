<?php
/**
 * Session sécurisée et contrôle des rôles côté serveur.
 * À inclure dans tout endpoint qui nécessite une authentification.
 */

declare(strict_types=1);

/**
 * Démarre la session avec des options durcies :
 * - cookie httponly : inaccessible en JavaScript (protection XSS)
 * - samesite=Lax   : le cookie n'est pas envoyé sur les requêtes intersites (protection CSRF)
 * - secure         : cookie réservé au HTTPS quand la connexion l'est
 * - use_strict_mode: refuse les identifiants de session non initialisés (protection fixation)
 *
 * En production, l'application est derrière un proxy inverse qui termine le
 * TLS : la requête arrive donc en HTTP et `$_SERVER['HTTPS']` est vide. C'est
 * l'en-tête `X-Forwarded-Proto` qui indique le protocole vu par le navigateur —
 * sans lui, le cookie perdrait son attribut `secure` alors que le site est
 * bien en HTTPS.
 */
function demarrerSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $httpsActif = !empty($_SERVER['HTTPS'])
        || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure'   => $httpsActif,
        'use_strict_mode' => true,
    ]);
}

/**
 * Retourne l'utilisateur connecté (ou null).
 *
 * @return array{id: int, role: string, prenom: string}|null
 */
function utilisateurCourant(): ?array
{
    demarrerSession();
    if (!isset($_SESSION['utilisateur_id'], $_SESSION['role'])) {
        return null;
    }
    return [
        'id'     => (int) $_SESSION['utilisateur_id'],
        'role'   => (string) $_SESSION['role'],
        'prenom' => (string) ($_SESSION['prenom'] ?? ''),
    ];
}

/**
 * Bloque la requête si personne n'est connecté.
 */
function exigerAuthentification(): array
{
    $utilisateur = utilisateurCourant();
    if ($utilisateur === null) {
        repondre(401, ['erreur' => 'Authentification requise.']);
    }
    return $utilisateur;
}

/**
 * Bloque la requête si le rôle de l'utilisateur n'est pas dans la liste.
 * L'administrateur peut faire tout ce qu'un employé peut faire :
 * inclure 'administrateur' partout où 'employe' est accepté.
 */
function exigerRole(string ...$roles): array
{
    $utilisateur = exigerAuthentification();
    if (!in_array($utilisateur['role'], $roles, true)) {
        repondre(403, ['erreur' => 'Accès refusé pour ce rôle.']);
    }
    return $utilisateur;
}
