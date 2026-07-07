<?php
/**
 * Règles de calcul du prix d'une commande — composant métier central.
 *
 * Règles du cahier des charges :
 *   - prix par personne = prix du menu / nombre de personnes minimum
 *   - obligation de commander au moins pour le nombre de personnes minimum
 *   - réduction de 10 % si la commande compte au moins 5 personnes
 *     de plus que le minimum du menu
 *   - livraison offerte dans Bordeaux, sinon 5 € + 0,59 € par kilomètre
 *
 * Le calcul est TOUJOURS refait côté serveur : le montant affiché par le
 * front n'est qu'indicatif, jamais une donnée de confiance.
 */

declare(strict_types=1);

const LIVRAISON_FORFAIT   = 5.00;
const LIVRAISON_PAR_KM    = 0.59;
const SEUIL_REDUCTION     = 5;    // personnes au-dessus du minimum
const TAUX_REDUCTION      = 0.10;

/**
 * La ville est-elle Bordeaux ? (insensible à la casse et aux espaces)
 */
function estBordeaux(string $ville): bool
{
    return mb_strtolower(trim($ville)) === 'bordeaux';
}

/**
 * Calcule le détail du prix d'une commande.
 *
 * @param array $menu        Ligne de la table menu (prix_min, nb_personnes_min)
 * @param int   $nbPersonnes Nombre de personnes commandé (déjà validé ≥ minimum)
 * @param string $ville      Ville de livraison
 * @param float $distanceKm  Distance depuis Bordeaux (ignorée si Bordeaux)
 *
 * @return array{prix_menu: float, prix_livraison: float, prix_total: float,
 *               reduction_10: bool, distance_km: float}
 */
function calculerPrix(array $menu, int $nbPersonnes, string $ville, float $distanceKm): array
{
    $prixParPersonne = (float) $menu['prix_min'] / (int) $menu['nb_personnes_min'];
    $prixMenu = $prixParPersonne * $nbPersonnes;

    $reduction = $nbPersonnes >= (int) $menu['nb_personnes_min'] + SEUIL_REDUCTION;
    if ($reduction) {
        $prixMenu *= (1 - TAUX_REDUCTION);
    }
    $prixMenu = round($prixMenu, 2);

    if (estBordeaux($ville)) {
        $prixLivraison = 0.0;
        $distanceKm = 0.0;
    } else {
        $prixLivraison = round(LIVRAISON_FORFAIT + LIVRAISON_PAR_KM * $distanceKm, 2);
    }

    return [
        'prix_menu'      => $prixMenu,
        'prix_livraison' => $prixLivraison,
        'prix_total'     => round($prixMenu + $prixLivraison, 2),
        'reduction_10'   => $reduction,
        'distance_km'    => $distanceKm,
    ];
}
