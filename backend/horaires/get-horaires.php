<?php
/**
 * GET /backend/horaires/get-horaires.php — public.
 * Horaires d'ouverture affichés dans le pied de page (lundi → dimanche).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';

exigerMethode('GET');

$horaires = getPDO()->query(
    "SELECT jour, heure_ouverture, heure_fermeture
     FROM horaire
     ORDER BY FIELD(jour, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche')"
)->fetchAll();

repondre(200, ['horaires' => $horaires]);
