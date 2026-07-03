-- ============================================================
-- Vite & Gourmand — Jeu de données de démonstration
-- Usage : mysql -u root -p < database/fixtures.sql
--         (après avoir exécuté schema.sql)
--
-- Comptes de test (mots de passe conformes à la politique :
-- 10+ caractères, majuscule, minuscule, chiffre, spécial) :
--   Administrateur : jose@vite-et-gourmand.fr  / Admin!Vg2026
--   Employé        : julie@vite-et-gourmand.fr / Employe!Vg2026
--   Client démo    : client@demo.fr            / Client!Vg2026
--   (claire@, marc@, sophie@ demo.fr : même mot de passe que client démo)
-- ============================================================

USE vite_et_gourmand;
SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- Rôles
-- ------------------------------------------------------------
INSERT INTO role (role_id, libelle) VALUES
(1, 'utilisateur'),
(2, 'employe'),
(3, 'administrateur');

-- ------------------------------------------------------------
-- Utilisateurs
-- Le compte administrateur de José est créé ICI, en base :
-- il n'est pas possible de créer un administrateur depuis l'application.
-- ------------------------------------------------------------
INSERT INTO utilisateur (utilisateur_id, email, password, nom, prenom, telephone, adresse, ville, code_postal, role_id) VALUES
(1, 'jose@vite-et-gourmand.fr',  '$2y$12$2MX0GFTujQgpapBo845vTe/w84cxx.2bmLr.qsW1dATMlREqfNvHe', 'Martinez', 'José',   '0612345601', '12 rue des Faures',        'Bordeaux', '33000', 3),
(2, 'julie@vite-et-gourmand.fr', '$2y$12$t3fFeo39Dgla/8vB1P3nt.rb54Sio19yHmiz2zbTQrhJY8yY/AQmq', 'Bernard',  'Julie',  '0612345602', '12 rue des Faures',        'Bordeaux', '33000', 2),
(3, 'client@demo.fr',            '$2y$12$S3qGHMnoFjZV4txZx1gel.41S2Ud33SGVRz8DCTzOTqdiOd0VzuwS', 'Dupont',   'Jean',   '0612345603', '5 cours de l''Intendance', 'Bordeaux', '33000', 1),
(4, 'claire@demo.fr',            '$2y$12$S3qGHMnoFjZV4txZx1gel.41S2Ud33SGVRz8DCTzOTqdiOd0VzuwS', 'Durand',   'Claire', '0612345604', '8 rue Sainte-Catherine',   'Bordeaux', '33000', 1),
(5, 'marc@demo.fr',              '$2y$12$S3qGHMnoFjZV4txZx1gel.41S2Ud33SGVRz8DCTzOTqdiOd0VzuwS', 'Lefevre',  'Marc',   '0612345605', '22 avenue de la Marne',    'Mérignac', '33700', 1),
(6, 'sophie@demo.fr',            '$2y$12$S3qGHMnoFjZV4txZx1gel.41S2Ud33SGVRz8DCTzOTqdiOd0VzuwS', 'Roux',     'Sophie', '0612345606', '3 avenue Pasteur',         'Pessac',   '33600', 1);

-- ------------------------------------------------------------
-- Horaires (pied de page : lundi → dimanche)
-- ------------------------------------------------------------
INSERT INTO horaire (jour, heure_ouverture, heure_fermeture) VALUES
('lundi',    '09:00', '19:00'),
('mardi',    '09:00', '19:00'),
('mercredi', '09:00', '19:00'),
('jeudi',    '09:00', '19:00'),
('vendredi', '09:00', '19:00'),
('samedi',   '09:00', '20:00'),
('dimanche', '10:00', '16:00');

-- ------------------------------------------------------------
-- Référentiels : thèmes, régimes, allergènes
-- ------------------------------------------------------------
INSERT INTO theme (theme_id, libelle) VALUES
(1, 'Noël'), (2, 'Pâques'), (3, 'Classique'), (4, 'Évènement');

INSERT INTO regime (regime_id, libelle) VALUES
(1, 'Classique'), (2, 'Végétarien'), (3, 'Vegan');

INSERT INTO allergene (allergene_id, libelle) VALUES
(1, 'Gluten'), (2, 'Lactose'), (3, 'Œufs'), (4, 'Fruits à coque'),
(5, 'Arachides'), (6, 'Poissons'), (7, 'Crustacés'), (8, 'Soja'),
(9, 'Céleri'), (10, 'Moutarde');

-- ------------------------------------------------------------
-- Plats (entrées / plats / desserts)
-- ------------------------------------------------------------
INSERT INTO plat (plat_id, titre, type, photo) VALUES
(1,  'Foie gras mi-cuit, chutney de figues',   'entree',  'assets/img/plats/foie-gras.jpg'),
(2,  'Velouté de châtaignes',                  'entree',  'assets/img/plats/veloute-chataignes.jpg'),
(3,  'Œufs mimosa printaniers',                'entree',  'assets/img/plats/oeufs-mimosa.jpg'),
(4,  'Salade de chèvre chaud',                 'entree',  'assets/img/plats/chevre-chaud.jpg'),
(5,  'Houmos de saison et crudités',           'entree',  'assets/img/plats/houmos.jpg'),
(6,  'Gaspacho de tomates',                    'entree',  'assets/img/plats/gaspacho.jpg'),
(7,  'Assortiment de canapés',                 'entree',  'assets/img/plats/canapes.jpg'),
(8,  'Chapon fermier aux morilles',            'plat',    'assets/img/plats/chapon-morilles.jpg'),
(9,  'Purée truffée de la maison',             'plat',    'assets/img/plats/puree-truffee.jpg'),
(10, 'Agneau de sept heures',                  'plat',    'assets/img/plats/agneau.jpg'),
(11, 'Légumes printaniers rôtis',              'plat',    'assets/img/plats/legumes-rotis.jpg'),
(12, 'Entrecôte à la bordelaise',              'plat',    'assets/img/plats/entrecote.jpg'),
(13, 'Risotto crémeux aux légumes',            'plat',    'assets/img/plats/risotto.jpg'),
(14, 'Curry de légumes au lait de coco',       'plat',    'assets/img/plats/curry-coco.jpg'),
(15, 'Pièce de bœuf en croûte',                'plat',    'assets/img/plats/boeuf-croute.jpg'),
(16, 'Bûche signature chocolat-praliné',       'dessert', 'assets/img/plats/buche.jpg'),
(17, 'Mignardises de fête',                    'dessert', 'assets/img/plats/mignardises.jpg'),
(18, 'Nid chocolaté de Pâques',                'dessert', 'assets/img/plats/nid-chocolat.jpg'),
(19, 'Cannelés bordelais',                     'dessert', 'assets/img/plats/canneles.jpg'),
(20, 'Tarte fine aux fruits de saison',        'dessert', 'assets/img/plats/tarte-fruits.jpg'),
(21, 'Pavlova végétale aux fruits rouges',     'dessert', 'assets/img/plats/pavlova.jpg');

INSERT INTO plat_allergene (plat_id, allergene_id) VALUES
(1, 1), (1, 2),                 -- foie gras : gluten, lactose
(2, 2), (2, 4),                 -- velouté : lactose, fruits à coque
(3, 3), (3, 10),                -- œufs mimosa : œufs, moutarde
(4, 1), (4, 2),                 -- chèvre chaud : gluten, lactose
(6, 9),                         -- gaspacho : céleri
(7, 1), (7, 2), (7, 3),         -- canapés : gluten, lactose, œufs
(8, 2),                         -- chapon : lactose
(9, 2),                         -- purée truffée : lactose
(12, 2),                        -- entrecôte (beurre bordelais) : lactose
(13, 2),                        -- risotto : lactose
(15, 1), (15, 3),               -- bœuf en croûte : gluten, œufs
(16, 1), (16, 2), (16, 4),      -- bûche : gluten, lactose, fruits à coque
(17, 1), (17, 2), (17, 3),      -- mignardises : gluten, lactose, œufs
(18, 1), (18, 2), (18, 3), (18, 4), -- nid chocolaté
(19, 1), (19, 2), (19, 3),      -- cannelés : gluten, lactose, œufs
(20, 1), (20, 2);               -- tarte fine : gluten, lactose

-- ------------------------------------------------------------
-- Menus (cohérents avec les maquettes docs/maquettes/)
-- ------------------------------------------------------------
INSERT INTO menu (menu_id, titre, description, nb_personnes_min, prix_min, conditions, quantite_restante, theme_id, regime_id) VALUES
(1, 'Menu Noël Prestige',
   'Un menu d''exception pour les fêtes : foie gras mi-cuit et son chutney de figues, chapon fermier aux morilles et sa purée truffée, bûche signature chocolat-praliné de la maison.',
   8, 320.00,
   'Commande à passer au minimum 7 jours avant la date de la prestation. Conservation au frais entre 0 et 4 °C jusqu''au service.',
   5, 1, 1),
(2, 'Menu Pâques Gourmand',
   'Agneau de sept heures fondant, légumes printaniers rôtis et nid chocolaté : le repas de Pâques sans passer par la cuisine.',
   6, 180.00,
   'Commande à passer au minimum 5 jours avant la date de la prestation. Conservation au frais entre 0 et 4 °C.',
   8, 2, 1),
(3, 'Menu Classique Bordelais',
   'Le terroir à l''honneur : entrecôte à la bordelaise, cannelés et douceurs locales. Notre best-seller depuis 25 ans.',
   6, 150.00,
   'Commande à passer au minimum 3 jours avant la date de la prestation.',
   12, 3, 1),
(4, 'Menu Végétarien du Marché',
   'Légumes rôtis du marché, risotto crémeux et tarte fine aux fruits de saison : un menu végétarien qui régale tout le monde.',
   6, 140.00,
   'Commande à passer au minimum 3 jours avant la date de la prestation. Composition variable selon le marché.',
   10, 3, 2),
(5, 'Menu Vegan Découverte',
   'Houmos de saison, curry de légumes au lait de coco et pavlova végétale aux fruits rouges : 100 % végétal, 100 % gourmand.',
   6, 145.00,
   'Commande à passer au minimum 4 jours avant la date de la prestation.',
   10, 3, 3),
(6, 'Menu Évènement Grand Format',
   'Buffet complet pour réceptions, mariages et séminaires : canapés, pièces à trancher et desserts bordelais. Matériel de service fourni sur demande.',
   20, 550.00,
   'Commande à passer au minimum 14 jours avant la date de la prestation. Le matériel prêté doit être restitué sous 10 jours ouvrés après la prestation (600 € de frais en cas de non-restitution, cf. CGV).',
   3, 4, 1);

-- Galerie d'images (alt renseigné : exigence RGAA)
INSERT INTO image_menu (menu_id, chemin, alt, position) VALUES
(1, 'assets/img/menus/noel-1.jpg',      'Chapon fermier aux morilles dressé sur plat de service', 1),
(1, 'assets/img/menus/noel-2.jpg',      'Foie gras mi-cuit et chutney de figues en entrée',       2),
(1, 'assets/img/menus/noel-3.jpg',      'Bûche signature chocolat-praliné',                        3),
(2, 'assets/img/menus/paques-1.jpg',    'Agneau de sept heures et légumes printaniers',            1),
(2, 'assets/img/menus/paques-2.jpg',    'Nid chocolaté de Pâques',                                 2),
(3, 'assets/img/menus/bordelais-1.jpg', 'Entrecôte à la bordelaise grillée',                       1),
(3, 'assets/img/menus/bordelais-2.jpg', 'Cannelés bordelais dorés',                                2),
(4, 'assets/img/menus/vege-1.jpg',      'Assiette de légumes rôtis du marché et risotto',          1),
(5, 'assets/img/menus/vegan-1.jpg',     'Curry de légumes au lait de coco et houmos',              1),
(6, 'assets/img/menus/event-1.jpg',     'Buffet dressé pour une réception',                        1),
(6, 'assets/img/menus/event-2.jpg',     'Assortiment de canapés sur plateaux',                     2);

-- Composition des menus.
-- NB : certains plats sont partagés entre menus (exigence du cahier des charges),
-- ex. plat 11 (légumes rôtis) dans les menus 2 et 4 ; plat 19 (cannelés) dans les menus 3 et 6.
INSERT INTO menu_plat (menu_id, plat_id) VALUES
(1, 1), (1, 2), (1, 8), (1, 9), (1, 16), (1, 17),   -- Noël Prestige
(2, 3), (2, 10), (2, 11), (2, 18),                   -- Pâques Gourmand
(3, 4), (3, 12), (3, 19), (3, 20),                   -- Classique Bordelais
(4, 6), (4, 11), (4, 13), (4, 20),                   -- Végétarien du Marché
(5, 5), (5, 14), (5, 21),                            -- Vegan Découverte
(6, 1), (6, 7), (6, 12), (6, 15), (6, 17), (6, 19);  -- Évènement Grand Format

-- ------------------------------------------------------------
-- Commandes de démonstration
-- Rappel des règles de prix :
--   prix/personne = prix_min / nb_personnes_min
--   réduction 10 % si nb_personnes >= nb_personnes_min + 5
--   livraison : 0 € dans Bordeaux, sinon 5 € + 0,59 €/km
-- ------------------------------------------------------------

-- CMD-2026-0001 — Claire, Menu Noël (320/8 = 40 €/pers), 13 pers → 520 − 10 % = 468 ; Bordeaux → 0 €
-- Commande TERMINÉE avec avis 5★ validé (affiché sur l'accueil)
INSERT INTO commande (commande_id, numero_commande, utilisateur_id, menu_id, date_commande, date_prestation, heure_livraison, adresse_livraison, ville_livraison, distance_km, nb_personnes, prix_menu, prix_livraison, prix_total, reduction_10, statut, pret_materiel, materiel_restitue) VALUES
(1, 'CMD-2026-0001', 4, 1, '2026-06-01 10:15:00', '2026-06-13', '11:30', '8 rue Sainte-Catherine', 'Bordeaux', 0, 13, 468.00, 0.00, 468.00, 1, 'terminee', 0, NULL);

-- CMD-2026-0002 — Marc, Menu Classique (150/6 = 25 €/pers), 6 pers → 150 ; Mérignac 8 km → 5 + 4,72 = 9,72
INSERT INTO commande VALUES
(2, 'CMD-2026-0002', 5, 3, '2026-06-05 14:30:00', '2026-06-14', '12:00', '22 avenue de la Marne', 'Mérignac', 8.00, 6, 150.00, 9.72, 159.72, 0, 'terminee', 0, NULL, NULL, NULL);

-- CMD-2026-0003 — Sophie, Menu Végétarien (140/6), 6 pers → 140 ; Pessac 10 km → 5 + 5,90 = 10,90
INSERT INTO commande VALUES
(3, 'CMD-2026-0003', 6, 4, '2026-06-10 09:00:00', '2026-06-20', '19:00', '3 avenue Pasteur', 'Pessac', 10.00, 6, 140.00, 10.90, 150.90, 0, 'terminee', 0, NULL, NULL, NULL);

-- CMD-2026-0004 — Client démo, Menu Vegan (145/6), 12 pers → 290 − 10 % = 261 ; Bordeaux → 0 €
-- Commande TERMINÉE avec avis EN ATTENTE (pour tester la validation côté employé)
INSERT INTO commande VALUES
(4, 'CMD-2026-0004', 3, 5, '2026-06-15 16:45:00', '2026-06-27', '12:30', '5 cours de l''Intendance', 'Bordeaux', 0, 12, 261.00, 0.00, 261.00, 1, 'terminee', 0, NULL, NULL, NULL);

-- CMD-2026-0005 — Marc, Menu Évènement (550/20 = 27,50 €/pers), 25 pers → 687,50 − 10 % = 618,75 ;
-- Mérignac 8 km → 9,72 ; matériel prêté, EN ATTENTE DE RETOUR (mail 600 €/10 j ouvrés envoyé)
INSERT INTO commande VALUES
(5, 'CMD-2026-0005', 5, 6, '2026-06-12 11:20:00', '2026-06-28', '18:00', '22 avenue de la Marne', 'Mérignac', 8.00, 25, 618.75, 9.72, 628.47, 1, 'attente_materiel', 1, 0, NULL, NULL);

-- CMD-2026-0006 — Client démo, Menu Pâques (180/6), 6 pers → 180 ; Bordeaux → 0 €
-- Commande CRÉÉE : encore annulable / modifiable par le client (pas encore acceptée)
INSERT INTO commande VALUES
(6, 'CMD-2026-0006', 3, 2, '2026-07-02 18:00:00', '2026-07-18', '12:00', '5 cours de l''Intendance', 'Bordeaux', 0, 6, 180.00, 0.00, 180.00, 0, 'cree', 0, NULL, NULL, NULL);

-- Historique de suivi : chaque état est daté (exigence du cahier des charges)
INSERT INTO suivi_commande (commande_id, statut, date_heure) VALUES
(1, 'cree',             '2026-06-01 10:15:00'),
(1, 'accepte',          '2026-06-02 09:00:00'),
(1, 'en_preparation',   '2026-06-12 08:00:00'),
(1, 'en_livraison',     '2026-06-13 10:30:00'),
(1, 'livre',            '2026-06-13 11:25:00'),
(1, 'terminee',         '2026-06-13 11:25:00'),
(2, 'cree',             '2026-06-05 14:30:00'),
(2, 'accepte',          '2026-06-06 10:00:00'),
(2, 'en_preparation',   '2026-06-13 08:00:00'),
(2, 'en_livraison',     '2026-06-14 11:00:00'),
(2, 'livre',            '2026-06-14 11:55:00'),
(2, 'terminee',         '2026-06-14 11:55:00'),
(3, 'cree',             '2026-06-10 09:00:00'),
(3, 'accepte',          '2026-06-11 09:30:00'),
(3, 'en_preparation',   '2026-06-19 08:00:00'),
(3, 'en_livraison',     '2026-06-20 17:45:00'),
(3, 'livre',            '2026-06-20 18:50:00'),
(3, 'terminee',         '2026-06-20 18:50:00'),
(4, 'cree',             '2026-06-15 16:45:00'),
(4, 'accepte',          '2026-06-16 09:15:00'),
(4, 'en_preparation',   '2026-06-26 08:00:00'),
(4, 'en_livraison',     '2026-06-27 11:15:00'),
(4, 'livre',            '2026-06-27 12:20:00'),
(4, 'terminee',         '2026-06-27 12:20:00'),
(5, 'cree',             '2026-06-12 11:20:00'),
(5, 'accepte',          '2026-06-13 10:00:00'),
(5, 'en_preparation',   '2026-06-27 08:00:00'),
(5, 'en_livraison',     '2026-06-28 16:30:00'),
(5, 'livre',            '2026-06-28 17:40:00'),
(5, 'attente_materiel', '2026-06-29 09:00:00'),
(6, 'cree',             '2026-07-02 18:00:00');

-- Avis : les 3 validés apparaissent sur l'accueil (cf. maquettes),
-- le 4ᵉ est en attente de modération par un employé
INSERT INTO avis (commande_id, note, description, statut) VALUES
(1, 5, 'Menu de Noël parfait, nos invités en parlent encore. Merci Julie et José !', 'valide'),
(2, 5, 'Livraison ponctuelle, plats délicieux, rapport qualité-prix imbattable.', 'valide'),
(3, 4, 'Le menu végétarien a conquis toute la tablée. Nous recommandons !', 'valide'),
(4, 5, 'Le menu vegan est une vraie découverte, la pavlova est incroyable.', 'en_attente');
