<?php
/**
 * POST /backend/plats/save-plat.php — réservé employé/admin.
 * Création ou modification d'un plat.
 * Corps JSON : plat_id (absent = création), titre, type (entree|plat|dessert),
 * allergenes[] (identifiants — remplace la liste existante).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

exigerMethode('POST');
exigerRole('employe', 'administrateur');
$donnees = lireJson();

$titre      = champTexte($donnees, 'titre');
$type       = champTexte($donnees, 'type');
$allergenes = $donnees['allergenes'] ?? [];
$platId     = $donnees['plat_id'] ?? null;

if ($titre === '') {
    repondre(422, ['erreur' => 'Le titre du plat est obligatoire.']);
}
if (!in_array($type, ['entree', 'plat', 'dessert'], true)) {
    repondre(422, ['erreur' => 'Type invalide : entree, plat ou dessert.']);
}
if (!is_array($allergenes)) {
    repondre(422, ['erreur' => 'Liste d\'allergènes invalide.']);
}

$pdo = getPDO();
$pdo->beginTransaction();

try {
    if (is_numeric($platId)) {
        $platId = (int) $platId;
        $requete = $pdo->prepare('UPDATE plat SET titre = :titre, type = :type WHERE plat_id = :id');
        $requete->execute(['titre' => $titre, 'type' => $type, 'id' => $platId]);
        if ($requete->rowCount() === 0 && !platExiste($pdo, $platId)) {
            $pdo->rollBack();
            repondre(404, ['erreur' => 'Plat introuvable.']);
        }
        $pdo->prepare('DELETE FROM plat_allergene WHERE plat_id = :id')->execute(['id' => $platId]);
    } else {
        $pdo->prepare('INSERT INTO plat (titre, type) VALUES (:titre, :type)')
            ->execute(['titre' => $titre, 'type' => $type]);
        $platId = (int) $pdo->lastInsertId();
    }

    $lien = $pdo->prepare('INSERT INTO plat_allergene (plat_id, allergene_id) VALUES (:plat_id, :allergene_id)');
    foreach (array_unique(array_map('intval', $allergenes)) as $allergeneId) {
        $lien->execute(['plat_id' => $platId, 'allergene_id' => $allergeneId]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('save-plat : ' . $e->getMessage());
    repondre(422, ['erreur' => 'Enregistrement impossible : vérifiez les allergènes transmis.']);
}

repondre(200, ['message' => 'Plat enregistré.', 'plat_id' => $platId]);

/** Le plat existe-t-il ? (un UPDATE sans changement renvoie rowCount 0) */
function platExiste(PDO $pdo, int $id): bool
{
    $requete = $pdo->prepare('SELECT COUNT(*) FROM plat WHERE plat_id = :id');
    $requete->execute(['id' => $id]);
    return (int) $requete->fetchColumn() > 0;
}
