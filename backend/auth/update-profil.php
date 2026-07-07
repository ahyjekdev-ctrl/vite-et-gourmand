<?php
/**
 * POST /backend/auth/update-profil.php
 * Modification des informations personnelles de la personne connectée.
 * L'adresse mail (identifiant de connexion) et le mot de passe ne se
 * modifient pas ici (mot de passe : parcours « mot de passe oublié »).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

exigerMethode('POST');
$utilisateur = exigerAuthentification();
$donnees = lireJson();

$erreurs = [];
$nom        = champTexte($donnees, 'nom');
$prenom     = champTexte($donnees, 'prenom');
$telephone  = champTexte($donnees, 'telephone');
$adresse    = champTexte($donnees, 'adresse');
$ville      = champTexte($donnees, 'ville');
$codePostal = champTexte($donnees, 'code_postal');

if ($nom === '')     { $erreurs['nom'] = 'Le nom est obligatoire.'; }
if ($prenom === '')  { $erreurs['prenom'] = 'Le prénom est obligatoire.'; }
if ($adresse === '') { $erreurs['adresse'] = 'L\'adresse est obligatoire.'; }
if ($ville === '')   { $erreurs['ville'] = 'La ville est obligatoire.'; }
if (preg_match('/^\+?[0-9 .-]{10,15}$/', $telephone) !== 1) {
    $erreurs['telephone'] = 'Numéro de GSM invalide.';
}
if (preg_match('/^[0-9]{5}$/', $codePostal) !== 1) {
    $erreurs['code_postal'] = 'Code postal invalide (5 chiffres).';
}

if ($erreurs !== []) {
    repondre(422, ['erreur' => 'Formulaire invalide.', 'champs' => $erreurs]);
}

$requete = getPDO()->prepare(
    'UPDATE utilisateur
     SET nom = :nom, prenom = :prenom, telephone = :telephone,
         adresse = :adresse, ville = :ville, code_postal = :code_postal
     WHERE utilisateur_id = :id'
);
$requete->execute([
    'nom'         => $nom,
    'prenom'      => $prenom,
    'telephone'   => $telephone,
    'adresse'     => $adresse,
    'ville'       => $ville,
    'code_postal' => $codePostal,
    'id'          => $utilisateur['id'],
]);

$_SESSION['prenom'] = $prenom;

repondre(200, ['message' => 'Informations personnelles mises à jour.']);
