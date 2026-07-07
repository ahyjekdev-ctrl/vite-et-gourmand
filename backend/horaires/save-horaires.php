<?php
/**
 * POST /backend/horaires/save-horaires.php — réservé employé/admin.
 * Mise à jour des horaires d'ouverture.
 * Corps JSON : { "horaires": [ { "jour": "lundi", "heure_ouverture": "09:00",
 *                                "heure_fermeture": "19:00" }, … ] }
 * Heures vides (null ou "") = fermé ce jour-là.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

exigerMethode('POST');
exigerRole('employe', 'administrateur');
$donnees = lireJson();

$horaires = $donnees['horaires'] ?? null;
if (!is_array($horaires) || $horaires === []) {
    repondre(400, ['erreur' => 'Aucun horaire transmis.']);
}

$joursValides = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

/** Valide une heure HH:MM (ou vide = fermé). */
function heureOuNull(mixed $valeur): ?string
{
    if ($valeur === null || $valeur === '') {
        return null;
    }
    if (is_string($valeur) && preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $valeur) === 1) {
        return $valeur;
    }
    repondre(422, ['erreur' => 'Heure invalide (format attendu : HH:MM).']);
}

$pdo = getPDO();
$pdo->beginTransaction();

$requete = $pdo->prepare(
    'UPDATE horaire SET heure_ouverture = :ouverture, heure_fermeture = :fermeture WHERE jour = :jour'
);

foreach ($horaires as $ligne) {
    $jour = is_array($ligne) ? ($ligne['jour'] ?? '') : '';
    if (!in_array($jour, $joursValides, true)) {
        $pdo->rollBack();
        repondre(422, ['erreur' => "Jour invalide : « $jour »."]);
    }
    $ouverture = heureOuNull($ligne['heure_ouverture'] ?? null);
    $fermeture = heureOuNull($ligne['heure_fermeture'] ?? null);
    if (($ouverture === null) !== ($fermeture === null)) {
        $pdo->rollBack();
        repondre(422, ['erreur' => "Pour $jour : renseignez l'ouverture ET la fermeture, ou laissez les deux vides (fermé)."]);
    }
    $requete->execute(['ouverture' => $ouverture, 'fermeture' => $fermeture, 'jour' => $jour]);
}

$pdo->commit();
repondre(200, ['message' => 'Horaires mis à jour.']);
