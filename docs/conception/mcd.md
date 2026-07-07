# 🗃️ Modèle Conceptuel de Données — Vite & Gourmand

> Basé sur l'annexe 1 de l'ECF, **corrigé et complété** : ajout du suivi historisé des commandes (exigence « tous les états avec date et heure »), de la galerie d'images, du type de plat et des tokens de réinitialisation de mot de passe.
> Le diagramme est en [Mermaid](https://mermaid.js.org/) : il s'affiche nativement sur GitHub.

## Diagramme entité-association

```mermaid
erDiagram
    ROLE ||--o{ UTILISATEUR : "possède"
    UTILISATEUR ||--o{ COMMANDE : "passe"
    UTILISATEUR ||--o{ RESET_TOKEN : "demande"
    MENU ||--o{ COMMANDE : "concerne"
    THEME ||--o{ MENU : "propose"
    REGIME ||--o{ MENU : "adopte"
    MENU ||--o{ IMAGE_MENU : "illustre"
    MENU }o--o{ PLAT : "compose"
    PLAT }o--o{ ALLERGENE : "contient"
    COMMANDE ||--o{ SUIVI_COMMANDE : "historise"
    COMMANDE ||--o| AVIS : "donne lieu à"

    ROLE {
        int role_id PK
        varchar libelle "utilisateur / employe / administrateur"
    }

    UTILISATEUR {
        int utilisateur_id PK
        varchar email UK "= username de connexion"
        varchar password "hash bcrypt"
        varchar nom
        varchar prenom
        varchar telephone "GSM"
        varchar adresse
        varchar ville
        varchar code_postal
        varchar pays
        boolean actif "désactivation compte employé"
        int role_id FK
        datetime cree_le
    }

    RESET_TOKEN {
        int token_id PK
        int utilisateur_id FK
        varchar token UK "usage unique"
        datetime expire_le
    }

    MENU {
        int menu_id PK
        varchar titre
        text description
        int nb_personnes_min
        decimal prix_min "prix pour nb_personnes_min"
        text conditions "délai de commande, stockage…"
        int quantite_restante "stock de commandes possibles"
        boolean actif
        int theme_id FK
        int regime_id FK
    }

    IMAGE_MENU {
        int image_id PK
        int menu_id FK
        varchar chemin
        varchar alt "accessibilité RGAA"
        int position "ordre dans la galerie"
    }

    THEME {
        int theme_id PK
        varchar libelle "Noël, Pâques, classique, évènement"
    }

    REGIME {
        int regime_id PK
        varchar libelle "classique, végétarien, vegan…"
    }

    PLAT {
        int plat_id PK
        varchar titre
        varchar type "entree / plat / dessert"
        varchar photo
    }

    ALLERGENE {
        int allergene_id PK
        varchar libelle "gluten, lactose, fruits à coque…"
    }

    COMMANDE {
        int commande_id PK
        varchar numero_commande UK
        int utilisateur_id FK
        int menu_id FK
        datetime date_commande
        date date_prestation
        time heure_livraison
        varchar adresse_livraison
        varchar ville_livraison
        decimal distance_km "0 si Bordeaux"
        int nb_personnes
        decimal prix_menu
        decimal prix_livraison "5 € + 0,59 €/km hors Bordeaux"
        decimal prix_total
        boolean reduction_10 "si nb >= min + 5"
        varchar statut "statut courant (dénormalisé)"
        boolean pret_materiel
        boolean materiel_restitue
        varchar motif_annulation "obligatoire si modif/annulation employé"
        varchar mode_contact "gsm / mail"
    }

    SUIVI_COMMANDE {
        int suivi_id PK
        int commande_id FK
        varchar statut "cree, accepte, en_preparation, en_livraison, livre, attente_materiel, terminee, annulee"
        datetime date_heure "exigence : chaque état daté"
    }

    AVIS {
        int avis_id PK
        int commande_id FK "un avis par commande terminée"
        int note "1 à 5"
        text description
        varchar statut "en_attente / valide / refuse"
    }

    HORAIRE {
        int horaire_id PK
        varchar jour "lundi → dimanche"
        time heure_ouverture
        time heure_fermeture
    }
```

## Choix de conception (à justifier devant le jury)

| Choix | Justification |
|---|---|
| `SUIVI_COMMANDE` séparée de `COMMANDE` | Le cahier des charges exige l'historique de **tous** les états avec date et heure. Le statut courant est aussi dénormalisé dans `COMMANDE` pour simplifier les filtres employé. |
| `MENU`–`PLAT` en N-N (table `menu_plat`) | « Une entrée ou un plat / dessert peuvent être présents dans plusieurs menus ». |
| `PLAT`–`ALLERGENE` en N-N (table `plat_allergene`) | Un plat a plusieurs allergènes, un allergène concerne plusieurs plats. |
| `AVIS` lié à `COMMANDE` (pas directement à l'utilisateur) | L'avis se donne « depuis la commande » terminée → garantit 1 avis max par commande et un avis authentique. |
| `IMAGE_MENU` avec `alt` et `position` | Galerie ordonnée + exigence RGAA. |
| `actif` sur `UTILISATEUR` | Désactivation d'un compte employé sans suppression (traçabilité des commandes traitées, RGPD compatible). |
| `RESET_TOKEN` avec expiration | Réinitialisation de mot de passe sécurisée (token à usage unique). |

## Base NoSQL (MongoDB) — statistiques

Collection `stats_commandes` alimentée à chaque changement de statut de commande :

```json
{
  "menu_id": 1,
  "titre_menu": "Menu Noël Prestige",
  "date_commande": "2026-07-03T14:30:00Z",
  "nb_personnes": 10,
  "prix_total": 288.0,
  "statut": "terminee"
}
```

Elle alimente l'espace administrateur : **nombre de commandes par menu** (graphique comparatif) et **chiffre d'affaires par menu** avec filtres par menu et par période.
