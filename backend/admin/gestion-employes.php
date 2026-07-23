<?php
/**
 * /backend/admin/gestion-employes.php — réservé à l'ADMINISTRATEUR.
 *
 * GET  : liste des comptes employés (email, actif)
 * POST : { "action": "creer", "email": "...", "password": "..." }
 *        → crée un compte employé ; l'employé est notifié par mail
 *          SANS le mot de passe (à transmettre de vive voix)
 *        { "action": "activer", "utilisateur_id": 2, "actif": false }
 *        → désactive / réactive un compte employé (départ de l'entreprise)
 *
 * Rappel : il est impossible de créer un compte administrateur depuis
 * l'application — celui de José a été créé directement en base (fixtures).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../mail/send-mail.php';

$pdo = getPDO();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
    exigerRole('administrateur');
    $employes = $pdo->query(
        "SELECT u.utilisateur_id, u.email, u.actif, u.cree_le
         FROM utilisateur u
         JOIN role r ON r.role_id = u.role_id
         WHERE r.libelle = 'employe'
         ORDER BY u.email"
    )->fetchAll();
    repondre(200, ['employes' => $employes]);
}

exigerMethode('POST');
exigerRole('administrateur');
$donnees = lireJson();
$action = champTexte($donnees, 'action');

if ($action === 'creer') {
    $email      = champTexte($donnees, 'email');
    $motDePasse = (string) ($donnees['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        repondre(422, ['erreur' => 'Adresse mail invalide.']);
    }
    if (!motDePasseValide($motDePasse)) {
        repondre(422, ['erreur' => 'Mot de passe trop faible : 10 caractères minimum, '
            . 'avec majuscule, minuscule, chiffre et caractère spécial.']);
    }

    $requete = $pdo->prepare('SELECT COUNT(*) FROM utilisateur WHERE email = :email');
    $requete->execute(['email' => $email]);
    if ((int) $requete->fetchColumn() > 0) {
        repondre(409, ['erreur' => 'Un compte existe déjà avec cette adresse mail.']);
    }

    // Compte employé minimal : l'employé complétera ses informations ensuite
    $requete = $pdo->prepare(
        "INSERT INTO utilisateur (email, password, nom, prenom, telephone, adresse, ville, code_postal, role_id)
         VALUES (:email, :password, '', '', '', '', '', '',
                 (SELECT role_id FROM role WHERE libelle = 'employe'))"
    );
    $requete->execute([
        'email'    => $email,
        'password' => password_hash($motDePasse, PASSWORD_BCRYPT),
    ]);

    // Notification SANS le mot de passe (exigence du cahier des charges)
    mailCompteEmploye($email);

    repondre(201, ['message' => "Compte employé créé. $email a été notifié par mail — "
        . 'communiquez-lui le mot de passe directement.']);
}

if ($action === 'activer') {
    $utilisateurId = $donnees['utilisateur_id'] ?? null;
    $actif = !empty($donnees['actif']) ? 1 : 0;

    if (!is_numeric($utilisateurId)) {
        repondre(400, ['erreur' => 'Identifiant manquant.']);
    }

    // Uniquement les comptes employés : on ne désactive ni les clients ni l'admin ici
    $requete = $pdo->prepare(
        "UPDATE utilisateur u
         JOIN role r ON r.role_id = u.role_id AND r.libelle = 'employe'
         SET u.actif = :actif
         WHERE u.utilisateur_id = :id"
    );
    $requete->execute(['actif' => $actif, 'id' => (int) $utilisateurId]);

    if ($requete->rowCount() === 0) {
        repondre(404, ['erreur' => 'Compte employé introuvable (ou déjà dans cet état).']);
    }

    repondre(200, ['message' => $actif ? 'Compte réactivé.' : 'Compte désactivé : connexion refusée désormais.']);
}

repondre(400, ['erreur' => 'Action inconnue (creer ou activer).']);
