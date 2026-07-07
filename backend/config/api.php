<?php
/**
 * Aides communes aux endpoints de l'API JSON.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

/**
 * Envoie une réponse JSON et termine la requête.
 */
function repondre(int $code, array $donnees): never
{
    http_response_code($code);
    echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Refuse la requête si la méthode HTTP n'est pas celle attendue.
 */
function exigerMethode(string $methode): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $methode) {
        repondre(405, ['erreur' => 'Méthode non autorisée.']);
    }
}

/**
 * Lit et décode le corps JSON de la requête.
 *
 * @return array<string, mixed>
 */
function lireJson(): array
{
    $corps = file_get_contents('php://input');
    $donnees = json_decode($corps ?: '', true);
    if (!is_array($donnees)) {
        repondre(400, ['erreur' => 'Corps de requête JSON invalide.']);
    }
    return $donnees;
}

/**
 * Récupère un champ texte du tableau reçu, nettoyé des espaces superflus.
 */
function champTexte(array $donnees, string $cle): string
{
    $valeur = $donnees[$cle] ?? '';
    return is_string($valeur) ? trim($valeur) : '';
}

/**
 * Vérifie la politique de mot de passe du cahier des charges :
 * 10 caractères minimum, au moins une majuscule, une minuscule,
 * un chiffre et un caractère spécial.
 */
function motDePasseValide(string $motDePasse): bool
{
    return strlen($motDePasse) >= 10
        && preg_match('/[A-Z]/', $motDePasse) === 1
        && preg_match('/[a-z]/', $motDePasse) === 1
        && preg_match('/[0-9]/', $motDePasse) === 1
        && preg_match('/[^A-Za-z0-9]/', $motDePasse) === 1;
}
