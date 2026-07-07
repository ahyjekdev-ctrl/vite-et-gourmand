<?php
/**
 * GET /backend/auth/me.php
 * Profil de l'utilisateur connecté — sert au pré-remplissage de la page
 * de commande et à l'espace utilisateur. Le mot de passe n'est jamais renvoyé.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

exigerMethode('GET');
$utilisateur = exigerAuthentification();

$requete = getPDO()->prepare(
    'SELECT u.nom, u.prenom, u.email, u.telephone, u.adresse, u.ville, u.code_postal,
            r.libelle AS role
     FROM utilisateur u
     JOIN role r ON r.role_id = u.role_id
     WHERE u.utilisateur_id = :id'
);
$requete->execute(['id' => $utilisateur['id']]);
$profil = $requete->fetch();

if ($profil === false) {
    repondre(404, ['erreur' => 'Profil introuvable.']);
}

repondre(200, ['profil' => $profil]);
