<?php
/**
 * POST /backend/auth/reset-password.php
 * Réinitialisation du mot de passe en deux temps :
 *   { "action": "demander", "email": "..." }
 *       → envoie par mail un lien contenant un token à usage unique (valable 1 h)
 *   { "action": "reinitialiser", "token": "...", "password": "...", "password_confirmation": "..." }
 *       → vérifie le token et enregistre le nouveau mot de passe
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../mail/send-mail.php';

exigerMethode('POST');
$donnees = lireJson();
$pdo = getPDO();

$action = champTexte($donnees, 'action');

if ($action === 'demander') {
    $email = champTexte($donnees, 'email');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        repondre(422, ['erreur' => 'Adresse mail invalide.']);
    }

    $requete = $pdo->prepare('SELECT utilisateur_id FROM utilisateur WHERE email = :email AND actif = 1');
    $requete->execute(['email' => $email]);
    $utilisateur = $requete->fetch();

    // On génère le token seulement si le compte existe…
    if ($utilisateur !== false) {
        $token = bin2hex(random_bytes(32)); // 64 caractères hexadécimaux imprévisibles

        $requete = $pdo->prepare(
            'INSERT INTO reset_token (utilisateur_id, token, expire_le)
             VALUES (:id, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
        );
        $requete->execute(['id' => $utilisateur['utilisateur_id'], 'token' => $token]);

        $hote = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        $schema = empty($_SERVER['HTTPS']) ? 'http' : 'https';
        mailReinitialisation($email, "$schema://$hote/frontend/connexion.html?token=$token");
    }

    // … mais la réponse est identique dans tous les cas :
    // impossible de deviner si une adresse possède un compte (anti-énumération)
    repondre(200, ['message' => 'Si un compte existe avec cette adresse, un mail de réinitialisation a été envoyé.']);
}

if ($action === 'reinitialiser') {
    $token        = champTexte($donnees, 'token');
    $motDePasse   = (string) ($donnees['password'] ?? '');
    $confirmation = (string) ($donnees['password_confirmation'] ?? '');

    if (!motDePasseValide($motDePasse)) {
        repondre(422, ['erreur' => 'Mot de passe trop faible : 10 caractères minimum, '
            . 'avec majuscule, minuscule, chiffre et caractère spécial.']);
    }
    if ($motDePasse !== $confirmation) {
        repondre(422, ['erreur' => 'La confirmation ne correspond pas au mot de passe.']);
    }

    $requete = $pdo->prepare(
        'SELECT token_id, utilisateur_id FROM reset_token
         WHERE token = :token AND utilise = 0 AND expire_le > NOW()'
    );
    $requete->execute(['token' => $token]);
    $entree = $requete->fetch();

    if ($entree === false) {
        repondre(400, ['erreur' => 'Lien de réinitialisation invalide ou expiré. Refaites une demande.']);
    }

    // Transaction : nouveau mot de passe + token consommé, tout ou rien
    $pdo->beginTransaction();
    $pdo->prepare('UPDATE utilisateur SET password = :password WHERE utilisateur_id = :id')
        ->execute(['password' => password_hash($motDePasse, PASSWORD_BCRYPT), 'id' => $entree['utilisateur_id']]);
    $pdo->prepare('UPDATE reset_token SET utilise = 1 WHERE token_id = :id')
        ->execute(['id' => $entree['token_id']]);
    $pdo->commit();

    repondre(200, ['message' => 'Mot de passe modifié. Vous pouvez vous connecter.']);
}

repondre(400, ['erreur' => 'Action inconnue.']);
