<?php
/**
 * POST /backend/auth/register.php
 * Inscription d'un visiteur. Rôle attribué : « utilisateur ».
 * Corps JSON attendu : nom, prenom, telephone, email, adresse, ville,
 * code_postal, password, password_confirmation, consentement.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../mail/send-mail.php';

exigerMethode('POST');
$donnees = lireJson();

/* ---------- Validation côté serveur (jamais de confiance au front) ---------- */

$erreurs = [];

$nom        = champTexte($donnees, 'nom');
$prenom     = champTexte($donnees, 'prenom');
$telephone  = champTexte($donnees, 'telephone');
$email      = champTexte($donnees, 'email');
$adresse    = champTexte($donnees, 'adresse');
$ville      = champTexte($donnees, 'ville');
$codePostal = champTexte($donnees, 'code_postal');
$motDePasse = (string) ($donnees['password'] ?? '');
$confirmation = (string) ($donnees['password_confirmation'] ?? '');

if ($nom === '')     { $erreurs['nom'] = 'Le nom est obligatoire.'; }
if ($prenom === '')  { $erreurs['prenom'] = 'Le prénom est obligatoire.'; }
if ($adresse === '') { $erreurs['adresse'] = 'L\'adresse est obligatoire.'; }
if ($ville === '')   { $erreurs['ville'] = 'La ville est obligatoire.'; }

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreurs['email'] = 'Adresse mail invalide.';
}
if (preg_match('/^\+?[0-9 .-]{10,15}$/', $telephone) !== 1) {
    $erreurs['telephone'] = 'Numéro de GSM invalide.';
}
if (preg_match('/^[0-9]{5}$/', $codePostal) !== 1) {
    $erreurs['code_postal'] = 'Code postal invalide (5 chiffres).';
}
if (!motDePasseValide($motDePasse)) {
    $erreurs['password'] = 'Mot de passe trop faible : 10 caractères minimum, '
        . 'avec majuscule, minuscule, chiffre et caractère spécial.';
}
if ($motDePasse !== $confirmation) {
    $erreurs['password_confirmation'] = 'La confirmation ne correspond pas au mot de passe.';
}
if (empty($donnees['consentement'])) {
    $erreurs['consentement'] = 'Le consentement RGPD est obligatoire.';
}

if ($erreurs !== []) {
    repondre(422, ['erreur' => 'Formulaire invalide.', 'champs' => $erreurs]);
}

/* ---------- Création du compte ---------- */

$pdo = getPDO();

$requete = $pdo->prepare('SELECT COUNT(*) FROM utilisateur WHERE email = :email');
$requete->execute(['email' => $email]);
if ((int) $requete->fetchColumn() > 0) {
    repondre(409, ['erreur' => 'Un compte existe déjà avec cette adresse mail.']);
}

$requete = $pdo->prepare(
    'INSERT INTO utilisateur (email, password, nom, prenom, telephone, adresse, ville, code_postal, role_id)
     VALUES (:email, :password, :nom, :prenom, :telephone, :adresse, :ville, :code_postal,
             (SELECT role_id FROM role WHERE libelle = \'utilisateur\'))'
);
$requete->execute([
    'email'       => $email,
    'password'    => password_hash($motDePasse, PASSWORD_BCRYPT),
    'nom'         => $nom,
    'prenom'      => $prenom,
    'telephone'   => $telephone,
    'adresse'     => $adresse,
    'ville'       => $ville,
    'code_postal' => $codePostal,
]);

// Mail de bienvenue automatique (exigence du cahier des charges)
mailBienvenue($email, $prenom);

repondre(201, ['message' => 'Compte créé. Bienvenue chez Vite & Gourmand !']);
