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

/**
 * Confirmation de commande, avec le détail complet du prix.
 *
 * @param array $prix Résultat de calculerPrix() : prix_menu, prix_livraison, prix_total, reduction_10
 */
function mailConfirmationCommande(
    string $destinataire,
    string $prenom,
    string $numero,
    string $menu,
    array $prix,
    int $nbPersonnes,
    string $datePrestation,
    string $heure
): bool {
    $lignes = "  - Menu ($nbPersonnes personnes) : " . number_format($prix['prix_menu'], 2, ',', ' ') . " €\n";
    if ($prix['reduction_10']) {
        $lignes .= "    (réduction de 10 % appliquée)\n";
    }
    $lignes .= '  - Livraison : ' . number_format($prix['prix_livraison'], 2, ',', ' ') . " €\n";
    $lignes .= '  - TOTAL : ' . number_format($prix['prix_total'], 2, ',', ' ') . ' €';

    $corps = "Bonjour $prenom,\n\n"
        . "Votre commande $numero est bien enregistrée !\n\n"
        . "Récapitulatif :\n"
        . "  - $menu\n"
        . "  - Prestation le $datePrestation, livraison souhaitée à $heure\n"
        . "$lignes\n\n"
        . "Vous pouvez suivre, modifier ou annuler votre commande depuis votre espace\n"
        . "tant que l'équipe ne l'a pas acceptée.\n\n"
        . "Merci de votre confiance,\nJulie & José — Vite & Gourmand, Bordeaux";
    return envoyerMail($destinataire, "Confirmation de votre commande $numero", $corps);
}

/**
 * Rappel de restitution du matériel prêté (clause des CGV).
 */
function mailMaterielAttente(string $destinataire, string $prenom, string $numero): bool
{
    $corps = "Bonjour $prenom,\n\n"
        . "Votre commande $numero est livrée : il ne reste plus qu'à nous restituer\n"
        . "le matériel prêté (plats, matériel de maintien au chaud…).\n\n"
        . "⚠ Conformément à nos conditions générales de vente, si le matériel n'est pas\n"
        . "restitué sous 10 jours ouvrés, des frais de 600 € vous seront facturés.\n\n"
        . "Pour organiser le retour, prenez simplement contact avec la société.\n\n"
        . "Merci,\nJulie & José — Vite & Gourmand, Bordeaux";
    return envoyerMail($destinataire, "Commande $numero : matériel à restituer sous 10 jours ouvrés", $corps);
}

/**
 * Invitation à donner un avis quand la commande est terminée.
 */
function mailInvitationAvis(string $destinataire, string $prenom, string $numero): bool
{
    $corps = "Bonjour $prenom,\n\n"
        . "Votre commande $numero est terminée — nous espérons que vos convives se sont régalés !\n\n"
        . "Votre avis compte : connectez-vous à votre compte et donnez une note (de 1 à 5)\n"
        . "et un commentaire depuis la commande, dans votre espace personnel.\n\n"
        . "Merci et à bientôt,\nJulie & José — Vite & Gourmand, Bordeaux";
    return envoyerMail($destinataire, "Commande $numero terminée : donnez votre avis !", $corps);
}
