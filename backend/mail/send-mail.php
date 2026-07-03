<?php
/**
 * Service d'envoi de mails centralisé.
 *
 * En développement (MAIL_API_KEY vide) : les mails sont écrits dans
 * backend/mail/mails.log pour pouvoir vérifier leur contenu sans compte SMTP.
 * En production : brancher ici l'API du fournisseur (Brevo, Mailgun…)
 * via la clé MAIL_API_KEY du .env.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php'; // pour chargerEnv()

/**
 * Envoie un mail (ou le journalise en dev). Retourne true si accepté.
 */
function envoyerMail(string $destinataire, string $sujet, string $corps): bool
{
    $env = chargerEnv();
    $expediteur = $env['MAIL_FROM'] ?? 'contact@vite-et-gourmand.fr';

    if (empty($env['MAIL_API_KEY'])) {
        // Mode développement : journalisation locale (fichier ignoré par git)
        $entree = sprintf(
            "[%s]\nDe      : %s\nÀ       : %s\nSujet   : %s\n%s\n%s\n",
            date('Y-m-d H:i:s'),
            $expediteur,
            $destinataire,
            $sujet,
            $corps,
            str_repeat('-', 60)
        );
        return file_put_contents(__DIR__ . '/mails.log', $entree, FILE_APPEND | LOCK_EX) !== false;
    }

    // Mode production : appel de l'API du fournisseur de mails.
    // À implémenter lors du déploiement (Phase 10) selon le fournisseur retenu.
    error_log('envoyerMail : aucun fournisseur configuré, mail non envoyé à ' . $destinataire);
    return false;
}

/* ---------- Modèles de mails ---------- */

function mailBienvenue(string $destinataire, string $prenom): bool
{
    $corps = "Bonjour $prenom,\n\n"
        . "Bienvenue chez Vite & Gourmand ! Votre compte a bien été créé.\n"
        . "Vous pouvez dès maintenant parcourir nos menus et passer commande :\n"
        . "nos équipes se feront un plaisir de régaler vos convives.\n\n"
        . "À très bientôt,\nJulie & José — Vite & Gourmand, Bordeaux";
    return envoyerMail($destinataire, 'Bienvenue chez Vite & Gourmand !', $corps);
}

function mailReinitialisation(string $destinataire, string $lien): bool
{
    $corps = "Bonjour,\n\n"
        . "Vous avez demandé la réinitialisation de votre mot de passe.\n"
        . "Cliquez sur ce lien pour en choisir un nouveau (valable 1 heure) :\n$lien\n\n"
        . "Si vous n'êtes pas à l'origine de cette demande, ignorez simplement ce mail.\n\n"
        . "Vite & Gourmand, Bordeaux";
    return envoyerMail($destinataire, 'Réinitialisation de votre mot de passe', $corps);
}
