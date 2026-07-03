-- ============================================================
-- Vite & Gourmand — Script de création de la base de données
-- SGBD : MySQL 8+ (InnoDB, utf8mb4)
-- Modèle : voir docs/conception/mcd.md
-- Usage : mysql -u root -p < database/schema.sql
-- ============================================================

DROP DATABASE IF EXISTS vite_et_gourmand;
CREATE DATABASE vite_et_gourmand
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE vite_et_gourmand;

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- Rôles et utilisateurs
-- ------------------------------------------------------------

CREATE TABLE role (
    role_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle     VARCHAR(50) NOT NULL UNIQUE
) ENGINE = InnoDB;

CREATE TABLE utilisateur (
    utilisateur_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(255) NOT NULL UNIQUE,           -- sert d'identifiant de connexion
    password        VARCHAR(255) NOT NULL,                  -- hash bcrypt (password_hash)
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    telephone       VARCHAR(20)  NOT NULL,                  -- GSM
    adresse         VARCHAR(255) NOT NULL,
    ville           VARCHAR(100) NOT NULL,
    code_postal     VARCHAR(10)  NOT NULL,
    pays            VARCHAR(100) NOT NULL DEFAULT 'France',
    actif           TINYINT(1)   NOT NULL DEFAULT 1,        -- désactivation d'un compte employé
    role_id         INT UNSIGNED NOT NULL,
    cree_le         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_utilisateur_role FOREIGN KEY (role_id)
        REFERENCES role (role_id) ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE reset_token (
    token_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT UNSIGNED NOT NULL,
    token           CHAR(64) NOT NULL UNIQUE,               -- token aléatoire à usage unique
    expire_le       DATETIME NOT NULL,
    utilise         TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_token_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateur (utilisateur_id) ON DELETE CASCADE
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- Catalogue : thèmes, régimes, plats, allergènes, menus
-- ------------------------------------------------------------

CREATE TABLE theme (
    theme_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle     VARCHAR(50) NOT NULL UNIQUE
) ENGINE = InnoDB;

CREATE TABLE regime (
    regime_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle     VARCHAR(50) NOT NULL UNIQUE
) ENGINE = InnoDB;

CREATE TABLE allergene (
    allergene_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle         VARCHAR(100) NOT NULL UNIQUE
) ENGINE = InnoDB;

CREATE TABLE plat (
    plat_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre       VARCHAR(150) NOT NULL,
    type        ENUM('entree', 'plat', 'dessert') NOT NULL,
    photo       VARCHAR(255) NULL
) ENGINE = InnoDB;

-- N-N : un plat peut contenir plusieurs allergènes
CREATE TABLE plat_allergene (
    plat_id         INT UNSIGNED NOT NULL,
    allergene_id    INT UNSIGNED NOT NULL,
    PRIMARY KEY (plat_id, allergene_id),
    CONSTRAINT fk_pa_plat FOREIGN KEY (plat_id)
        REFERENCES plat (plat_id) ON DELETE CASCADE,
    CONSTRAINT fk_pa_allergene FOREIGN KEY (allergene_id)
        REFERENCES allergene (allergene_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE menu (
    menu_id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre               VARCHAR(150) NOT NULL,
    description         TEXT NOT NULL,
    nb_personnes_min    INT UNSIGNED NOT NULL,
    prix_min            DECIMAL(8,2) NOT NULL,              -- prix pour nb_personnes_min
    conditions          TEXT NOT NULL,                      -- délai de commande, stockage…
    quantite_restante   INT UNSIGNED NOT NULL DEFAULT 0,    -- stock de commandes possibles
    actif               TINYINT(1) NOT NULL DEFAULT 1,
    theme_id            INT UNSIGNED NOT NULL,
    regime_id           INT UNSIGNED NOT NULL,
    CONSTRAINT fk_menu_theme FOREIGN KEY (theme_id)
        REFERENCES theme (theme_id) ON DELETE RESTRICT,
    CONSTRAINT fk_menu_regime FOREIGN KEY (regime_id)
        REFERENCES regime (regime_id) ON DELETE RESTRICT,
    CONSTRAINT chk_menu_personnes CHECK (nb_personnes_min > 0),
    CONSTRAINT chk_menu_prix CHECK (prix_min >= 0)
) ENGINE = InnoDB;

CREATE TABLE image_menu (
    image_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    menu_id     INT UNSIGNED NOT NULL,
    chemin      VARCHAR(255) NOT NULL,
    alt         VARCHAR(255) NOT NULL,                      -- accessibilité RGAA
    position    INT UNSIGNED NOT NULL DEFAULT 1,            -- ordre dans la galerie
    CONSTRAINT fk_image_menu FOREIGN KEY (menu_id)
        REFERENCES menu (menu_id) ON DELETE CASCADE
) ENGINE = InnoDB;

-- N-N : un plat peut être proposé dans plusieurs menus
CREATE TABLE menu_plat (
    menu_id     INT UNSIGNED NOT NULL,
    plat_id     INT UNSIGNED NOT NULL,
    PRIMARY KEY (menu_id, plat_id),
    CONSTRAINT fk_mp_menu FOREIGN KEY (menu_id)
        REFERENCES menu (menu_id) ON DELETE CASCADE,
    CONSTRAINT fk_mp_plat FOREIGN KEY (plat_id)
        REFERENCES plat (plat_id) ON DELETE CASCADE
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- Commandes, suivi historisé, avis
-- ------------------------------------------------------------

CREATE TABLE commande (
    commande_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero_commande     VARCHAR(20) NOT NULL UNIQUE,        -- ex. CMD-2026-0001
    utilisateur_id      INT UNSIGNED NOT NULL,
    menu_id             INT UNSIGNED NOT NULL,
    date_commande       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_prestation     DATE NOT NULL,
    heure_livraison     TIME NOT NULL,
    adresse_livraison   VARCHAR(255) NOT NULL,
    ville_livraison     VARCHAR(100) NOT NULL,
    distance_km         DECIMAL(6,2) NOT NULL DEFAULT 0,    -- 0 si livraison dans Bordeaux
    nb_personnes        INT UNSIGNED NOT NULL,
    prix_menu           DECIMAL(8,2) NOT NULL,              -- après réduction éventuelle
    prix_livraison      DECIMAL(8,2) NOT NULL DEFAULT 0,    -- 5 € + 0,59 €/km hors Bordeaux
    prix_total          DECIMAL(8,2) NOT NULL,
    reduction_10        TINYINT(1) NOT NULL DEFAULT 0,      -- vrai si nb_personnes >= min + 5
    statut              ENUM('cree', 'accepte', 'en_preparation', 'en_livraison',
                             'livre', 'attente_materiel', 'terminee', 'annulee')
                        NOT NULL DEFAULT 'cree',            -- statut courant (historique dans suivi_commande)
    pret_materiel       TINYINT(1) NOT NULL DEFAULT 0,
    materiel_restitue   TINYINT(1) NULL,                    -- NULL si aucun matériel prêté
    motif_annulation    TEXT NULL,                          -- obligatoire si modif/annulation par employé
    mode_contact        ENUM('gsm', 'mail') NULL,           -- mode de contact du client par l'employé
    CONSTRAINT fk_commande_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateur (utilisateur_id) ON DELETE RESTRICT,
    CONSTRAINT fk_commande_menu FOREIGN KEY (menu_id)
        REFERENCES menu (menu_id) ON DELETE RESTRICT,
    CONSTRAINT chk_commande_personnes CHECK (nb_personnes > 0)
) ENGINE = InnoDB;

CREATE INDEX idx_commande_statut ON commande (statut);          -- filtre employé : par statut
CREATE INDEX idx_commande_client ON commande (utilisateur_id);  -- filtre employé : par client

-- Historique : chaque changement d'état est daté (exigence du cahier des charges)
CREATE TABLE suivi_commande (
    suivi_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    commande_id     INT UNSIGNED NOT NULL,
    statut          ENUM('cree', 'accepte', 'en_preparation', 'en_livraison',
                         'livre', 'attente_materiel', 'terminee', 'annulee') NOT NULL,
    date_heure      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_suivi_commande FOREIGN KEY (commande_id)
        REFERENCES commande (commande_id) ON DELETE CASCADE
) ENGINE = InnoDB;

-- Un avis par commande terminée ; visible sur l'accueil après validation employé
CREATE TABLE avis (
    avis_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    commande_id     INT UNSIGNED NOT NULL UNIQUE,
    note            TINYINT UNSIGNED NOT NULL,
    description     TEXT NOT NULL,
    statut          ENUM('en_attente', 'valide', 'refuse') NOT NULL DEFAULT 'en_attente',
    CONSTRAINT fk_avis_commande FOREIGN KEY (commande_id)
        REFERENCES commande (commande_id) ON DELETE CASCADE,
    CONSTRAINT chk_avis_note CHECK (note BETWEEN 1 AND 5)
) ENGINE = InnoDB;

-- ------------------------------------------------------------
-- Horaires d'ouverture (pied de page, gérés par employé/admin)
-- ------------------------------------------------------------

CREATE TABLE horaire (
    horaire_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jour            ENUM('lundi', 'mardi', 'mercredi', 'jeudi',
                         'vendredi', 'samedi', 'dimanche') NOT NULL UNIQUE,
    heure_ouverture TIME NULL,                              -- NULL = fermé ce jour
    heure_fermeture TIME NULL
) ENGINE = InnoDB;
