<?php
/**
 * POST /backend/contact/envoyer.php — public (accessible aux visiteurs).
 * Formulaire de contact : titre, description, mail du demandeur.
 * La demande est transmise par mail à l'entreprise, qui répondra
 * directement à l'adresse indiquée.
 *
 * Anti-spam : champ « piège » (honeypot) invisible pour les humains.
 * Si un robot le remplit, on répond comme si tout allait bien…
 * mais aucun mail n'est envoyé.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php'; // pour chargerEnv()
require_once __DIR__ . '/../mail/send-mail.php';

exigerMethode('POST');
$donnees = lireJson();

/* ---------- Anti-spam : honeypot ---------- */

if (champTexte($donnees, 'site_web') !== '') {
    // Réponse identique au succès : le robot ne sait pas qu'il est détecté
    repondre(200, ['message' => 'Votre demande a bien été envoyée. Nous vous répondrons par mail.']);
}

/* ---------- Validation ---------- */

$erreurs = [];
$titre       = champTexte($donnees, 'titre');
$description = champTexte($donnees, 'description');
$email       = champTexte($donnees, 'email');

if ($titre === '')       { $erreurs['titre'] = 'Le titre est obligatoire.'; }
if ($description === '') { $erreurs['description'] = 'La description est obligatoire.'; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreurs['email'] = 'Adresse mail invalide.';
}

if ($erreurs !== []) {
    repondre(422, ['erreur' => 'Formulaire invalide.', 'champs' => $erreurs]);
}

/* ---------- Envoi à l'entreprise ---------- */

$env = chargerEnv();
$destinataire = $env['MAIL_FROM'] ?? 'contact@vite-et-gourmand.fr'; // boîte de l'entreprise

$corps = "Nouvelle demande reçue via le formulaire de contact du site :\n\n"
    . "De      : $email\n"
    . "Titre   : $titre\n\n"
    . "$description\n\n"
    . "— Répondre directement à $email";

if (!envoyerMail($destinataire, "[Contact site] $titre", $corps)) {
    repondre(500, ['erreur' => 'L\'envoi a échoué. Réessayez plus tard.']);
}

repondre(200, ['message' => 'Votre demande a bien été envoyée. Nous vous répondrons par mail.']);
