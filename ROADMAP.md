# 🗺️ Feuille de route — Vite & Gourmand

> **Fichier de pilotage du projet.** On le met à jour à chaque avancée (Claude comme Alexandre).
> Légende : ✅ fait · 🔄 en cours · ⬜ à faire

---

## 📍 Où on en est (mis à jour le 03/07/2026)

**Phase actuelle : Phase 1 — Conception**

Le squelette du projet existe (arborescence, branches Git, remote GitHub) mais **tous les fichiers de code sont vides**. La documentation de pilotage vient d'être créée. Prochaine étape : maquettes + charte graphique + MCD, puis la base de données.

**⚠️ Rappel workflow : aucun merge vers `develop` ou `main` sans validation d'Alexandre.**

---

## Phase 0 — Initialisation ✅

- [x] Création du dépôt GitHub + remote
- [x] Branches `main` et `develop`
- [x] Arborescence du projet (frontend / backend / database / docs)
- [x] `.gitignore` + `.env.example`
- [x] README (démarche d'installation locale)
- [x] Documentation de pilotage (cahier des charges, livrables, conventions, roadmap)

## Phase 1 — Conception 🔄

> Branche suggérée : travail hors code, directement documenté dans `docs/`

- [ ] **Charte graphique** : palette de couleurs + typographie (export PDF à terme)
- [ ] **Wireframes** : 3 maquettes bureautiques + 3 maquettes mobiles
- [ ] **Mockups** : versions finalisées des mêmes écrans
- [ ] Schéma d'enchaînement des maquettes
- [ ] **MCD** (modèle conceptuel de données) — s'appuyer sur l'annexe 1 de l'ECF, la corriger/compléter
- [ ] Diagramme de cas d'utilisation
- [ ] Diagramme de séquence (ex. : parcours commande)
- [ ] Mise en place de l'outil de gestion de projet (Trello / Notion) + lien dans le README
- [ ] Compléter `.env.example` (variables MongoDB manquantes : `MONGO_URI`, `MONGO_DB`)

## Phase 2 — Base de données ⬜

> Branche : `feature/database`

- [ ] `database/schema.sql` — création des tables (utilisateur, role, menu, plat, theme, regime, allergene, commande, statut/suivi, avis, horaire, images…)
- [ ] `database/fixtures.sql` — données de test : menus, plats, comptes de démo (utilisateur, employé, **admin José créé ici, pas via l'app**)
- [ ] `database/mongodb-config.js` — collections statistiques (commandes par menu)
- [ ] Vérifier la cohérence avec le MCD

## Phase 3 — Front statique ⬜

> Branche : `feature/front-statique` (ou une branche par groupe de pages)

- [ ] Layout commun : header (nav) + footer (horaires lun→dim, mentions légales, CGV)
- [ ] `index.html` — accueil (présentation, équipe, avis validés)
- [ ] `menus.html` — vue globale + zone filtres
- [ ] `menu-detail.html` — détail complet + conditions mises en évidence + bouton commander
- [ ] `connexion.html` / `inscription.html`
- [ ] `commande.html`
- [ ] `contact.html`
- [ ] `mentions-legales.html` / `cgv.html` (inclure la clause des 600 € de matériel)
- [ ] Dashboards : espace utilisateur / employé / admin
- [ ] `style.css` — responsive mobile-first, conforme charte graphique
- [ ] Accessibilité RGAA (sémantique, alt, labels, contrastes)

## Phase 4 — Back : socle & authentification ⬜

> Branche : `feature/auth`

- [ ] `backend/config/database.php` — connexion PDO + lecture `.env`
- [ ] `backend/auth/register.php` — inscription (validation mdp fort, rôle « utilisateur », mail de bienvenue)
- [ ] `backend/auth/login.php` — connexion (session sécurisée)
- [ ] `backend/auth/logout.php`
- [ ] `backend/auth/reset-password.php` — token à expiration + mail
- [ ] `backend/mail/send-mail.php` — service d'envoi centralisé
- [ ] Middleware / contrôle des rôles côté serveur
- [ ] `frontend/assets/js/auth.js` — formulaires connexion/inscription

## Phase 5 — Menus dynamiques ⬜

> Branche : `feature/menus`

- [ ] `backend/menus/get-menus.php` — liste + paramètres de filtre
- [ ] `backend/menus/get-menu.php` — détail complet (plats, allergènes, images, conditions, stock)
- [ ] `backend/menus/create-menu.php` / `update-menu.php` (+ suppression) — réservé employé/admin
- [ ] CRUD plats et horaires (espace employé)
- [ ] `frontend/assets/js/filtres.js` — filtres **dynamiques sans rechargement** (prix max, fourchette, thème, régime, nb personnes)
- [ ] Affichage accueil : avis validés

## Phase 6 — Commandes ⬜

> Branche : `feature/commandes`

- [ ] `backend/commandes/create-commande.php` :
  - [ ] Auto-remplissage infos client
  - [ ] Contrôle nombre de personnes ≥ minimum du menu
  - [ ] Réduction 10 % si ≥ min + 5 personnes
  - [ ] Frais de livraison hors Bordeaux : 5 € + 0,59 €/km
  - [ ] Décrément du stock du menu
  - [ ] Mail de confirmation
- [ ] `frontend/assets/js/commande.js` — calcul du prix en direct + récap détaillé avant validation
- [ ] `backend/commandes/get-commandes.php` — par client (espace utilisateur) + filtres statut/client (employé)
- [ ] `backend/commandes/update-commande.php` :
  - [ ] Annulation/modification par le client tant que non « accepté » (tout sauf le menu)
  - [ ] Changement de statut par l'employé (accepté → … → terminée)
  - [ ] Motif + mode de contact obligatoires pour modif/annulation par employé
  - [ ] Historique de suivi (statut + date + heure)
  - [ ] Mails automatiques : matériel (600 € / 10 jours ouvrés), terminée (invitation avis)

## Phase 7 — Avis & espaces ⬜

> Branche : `feature/avis-espaces`

- [ ] `backend/avis/gestion-avis.php` — dépôt (note 1–5 + commentaire, commande terminée uniquement), validation/refus par employé
- [ ] Espace utilisateur complet (commandes, suivi, infos perso, avis)
- [ ] Espace employé complet (menus, plats, horaires, commandes, avis)
- [ ] Espace admin : création compte employé (mail sans mdp), désactivation compte
- [ ] **Statistiques MongoDB** : nb de commandes par menu + **graphique** comparatif
- [ ] Chiffre d'affaires par menu, filtres par menu et par période

## Phase 8 — Contact ⬜

> Branche : `feature/contact`

- [ ] Formulaire contact (titre, description, mail)
- [ ] Envoi de la demande par mail à l'entreprise

## Phase 9 — Qualité, sécurité, tests ⬜

> Branche : `fix/…` selon besoin

- [ ] Passe sécurité complète (voir docs/conventions.md §3) : XSS, CSRF, injections, sessions
- [ ] Passe accessibilité RGAA sur toutes les pages
- [ ] Jeux d'essai documentés (front + back) pour le dossier projet
- [ ] Tests manuels de tous les parcours (visiteur, utilisateur, employé, admin)
- [ ] Veille sécurité documentée (front + back)

## Phase 10 — Déploiement & livrables ⬜

- [ ] Choix de l'hébergeur + déploiement (app + MySQL + MongoDB)
- [ ] Documentation du déploiement (démarche + étapes)
- [ ] Manuel d'utilisation PDF (avec identifiants de test)
- [ ] Charte graphique PDF (palette, police, export des 6 maquettes)
- [ ] Documentation gestion de projet
- [ ] Documentation technique finale (voir docs/livrables.md)
- [ ] Dépôt GitHub passé en **public**
- [ ] **Dossier projet** (20–30 pages) + support de soutenance
- [ ] Copie à rendre remplie et déposée

---

## 📓 Journal des décisions

| Date | Décision |
|---|---|
| 03/07/2026 | Création de la doc de pilotage (cahier des charges, livrables, conventions, roadmap). Règle actée : **aucun merge vers develop/main sans validation d'Alexandre**. |
| avant | Squelette du projet + branches main/develop + remote GitHub. |
