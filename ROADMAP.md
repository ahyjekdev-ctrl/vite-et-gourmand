# 🗺️ Feuille de route — Vite & Gourmand

> **Fichier de pilotage du projet.** Mis à jour à chaque avancée.
> Légende : ✅ fait · 🔄 en cours · ⬜ à faire

## 📅 Dates clés

- **28/07/2026 — rendu de l'ECF** : application déployée, repo public, tous les livrables.
- **Novembre/décembre 2026 — jury** : dossier projet (20-30 p.) + soutenance orale.
- Planning de juillet : Phases 2-3 (semaine du 3), Phases 4-5 (semaine du 10), Phases 6-8 (semaine du 17), Phases 9-10 (24 → 28).
- D'août à novembre : relecture du code, dossier projet, support et répétition de l'oral.

---

## 📍 Où on en est (mis à jour le 03/07/2026)

**Phase actuelle : Phase 1 — Conception, quasi terminée (branche `feature/conception`, en attente de validation avant merge)**

Fait sur `feature/conception` : charte graphique, 12 maquettes SVG (wireframes + mockups, bureau + mobile), MCD, diagrammes (cas d'utilisation, séquence, enchaînement des écrans), doc de gestion de projet (Trello). Reste côté Alexandre : créer le board Trello (checklist dans [docs/gestion-de-projet.md](docs/gestion-de-projet.md) §5) et valider le merge. Ensuite : Phase 2 — base de données.

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

> Branche : `feature/conception` — **en attente de validation avant merge**

- [x] **Charte graphique** : palette de couleurs + typographie → [docs/charte-graphique.md](docs/charte-graphique.md) (export PDF en Phase 10)
- [x] **Wireframes** : 3 maquettes bureautiques + 3 maquettes mobiles → [docs/maquettes/](docs/maquettes/)
- [x] **Mockups** : versions finalisées des mêmes écrans (accueil, menus, détail)
- [x] Schéma d'enchaînement des maquettes → [docs/conception/diagrammes.md](docs/conception/diagrammes.md) §4
- [x] **MCD** (modèle conceptuel de données) — annexe 1 de l'ECF corrigée/complétée → [docs/conception/mcd.md](docs/conception/mcd.md)
- [x] Diagramme de cas d'utilisation → [docs/conception/diagrammes.md](docs/conception/diagrammes.md)
- [x] Diagramme de séquence (parcours commande + cycle de vie employé)
- [x] Choix de l'outil de gestion de projet : **Trello** → [docs/gestion-de-projet.md](docs/gestion-de-projet.md)
- [ ] Créer le board Trello + passer en public + lien dans le README *(action Alexandre — checklist §5 de la doc)*
- [x] Compléter `.env.example` (variables MongoDB : `MONGO_URI`, `MONGO_DB`)

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
| 03/07/2026 | Phase 1 sur `feature/conception` : maquettes en SVG (exportables PDF), MCD enrichi (suivi historisé, galerie d'images, reset tokens), diagrammes en Mermaid (rendus sur GitHub), outil de gestion de projet : **Trello**. |
| 03/07/2026 | Création de la doc de pilotage (cahier des charges, livrables, conventions, roadmap). Règle actée : **aucun merge vers develop/main sans validation d'Alexandre**. |
| avant | Squelette du projet + branches main/develop + remote GitHub. |
