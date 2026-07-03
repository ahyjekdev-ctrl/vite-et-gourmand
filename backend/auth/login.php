<?php
/**
 * POST /backend/auth/login.php
 * Connexion par mail (username) + mot de passe.
 * Corps JSON attendu : email, password.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

exigerMethode('POST');
$donnees = lireJson();

$email      = champTexte($donnees, 'email');
$motDePasse = (string) ($donnees['password'] ?? '');

if ($email === '' || $motDePasse === '') {
    repondre(422, ['erreur' => 'Mail et mot de passe sont obligatoires.']);
}

$pdo = getPDO();
$requete = $pdo->prepare(
    'SELECT u.utilisateur_id, u.password, u.prenom, u.actif, r.libelle AS role
     FROM utilisateur u
     JOIN role r ON r.role_id = u.role_id
     WHERE u.email = :email'
);
$requete->execute(['email' => $email]);
$utilisateur = $requete->fetch();

// Message identique quelle que soit la cause : ne révèle pas si le compte existe
if (
    $utilisateur === false
    || !password_verify($motDePasse, $utilisateur['password'])
    || !$utilisateur['actif']
) {
    repondre(401, ['erreur' => 'Identifiants invalides.']);
}

demarrerSession();
session_regenerate_id(true); // nouvel identifiant de session : anti-fixation

$_SESSION['utilisateur_id'] = (int) $utilisateur['utilisateur_id'];
$_SESSION['role']           = $utilisateur['role'];
$_SESSION['prenom']         = $utilisateur['prenom'];

repondre(200, [
    'message' => 'Connexion réussie.',
    'utilisateur' => [
        'prenom' => $utilisateur['prenom'],
        'role'   => $utilisateur['role'],
    ],
]);
