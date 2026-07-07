# 🗺️ Feuille de route — Vite & Gourmand

> **Fichier de pilotage du projet.** Mis à jour à chaque avancée.
> Légende : ✅ fait · 🔄 en cours · ⬜ à faire

## 📅 Dates clés

- **28/07/2026 — rendu de l'ECF** : application déployée, repo public, tous les livrables.
- **Novembre/décembre 2026 — jury** : dossier projet (20-30 p.) + soutenance orale.
- Planning de juillet : Phases 2-3 (semaine du 3), Phases 4-5 (semaine du 10), Phases 6-8 (semaine du 17), Phases 9-10 (24 → 28).
- D'août à novembre : relecture du code, dossier projet, support et répétition de l'oral.

---

## 📍 Où on en est (mis à jour le 07/07/2026)

**Phase actuelle : Phase 6 — Commandes, en cours sur `feature/commandes`**

Phases 1 à 5 mergées dans `develop`. Le site fonctionne en local de bout en bout : catalogue avec filtres dynamiques sans rechargement, détail de menu par id, inscription/connexion/reset, CRUD menus protégé par rôle. Documentation à jour ([livrables.md](docs/livrables.md) coché, environnement de travail documenté dans [docs/technique/notes.md](docs/technique/notes.md)). Côté Alexandre : créer le board Trello ([docs/gestion-de-projet.md](docs/gestion-de-projet.md) §5).

**⚠️ Rappel workflow : aucun merge vers `develop` ou `main` sans validation d'Alexandre.**

---

## Phase 0 — Initialisation ✅

- [x] Création du dépôt GitHub + remote
- [x] Branches `main` et `develop`
- [x] Arborescence du projet (frontend / backend / database / docs)
- [x] `.gitignore` + `.env.example`
- [x] README (démarche d'installation locale)
- [x] Documentation de pilotage (cahier des charges, livrables, conventions, roadmap)

## Phase 1 — Conception ✅

> Branche : `feature/conception` — mergée dans `develop` le 03/07/2026

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

## Phase 2 — Base de données ✅

> Branche : `feature/database` — mergée dans `develop` le 03/07/2026

- [x] `database/schema.sql` — 15 tables (role, utilisateur, reset_token, theme, regime, allergene, plat, plat_allergene, menu, image_menu, menu_plat, commande, suivi_commande, avis, horaire)
- [x] `database/fixtures.sql` — données de démo : 6 menus des maquettes, 21 plats (dont partagés entre menus), comptes de test (**admin José créé ici, pas via l'app**), 6 commandes avec historique de suivi, 4 avis
- [x] `database/mongodb-config.js` — collection `stats_commandes` (validation de schéma, index, données miroir des fixtures)
- [x] Cohérence vérifiée avec le MCD (mêmes entités, mêmes règles)
- [x] Import testé en local (MariaDB 11.8 + MongoDB 8.3 portables) : 15 tables, prix cohérents, UTF-8 et bcrypt vérifiés ; bug du validateur Mongo (`double` vs `Int32`) trouvé et corrigé

## Phase 3 — Front statique ✅

> Branche : `feature/front-statique` — mergée dans `develop` le 03/07/2026

- [x] Layout commun : header (nav + burger accessible via `nav.js`) + footer (horaires lun→dim, mentions légales, CGV)
- [x] `index.html` — accueil (présentation, équipe, avis validés des fixtures)
- [x] `menus.html` — vue globale des 6 menus + zone filtres (les 5 filtres du sujet)
- [x] `menu-detail.html` — détail complet + conditions mises en évidence + bouton commander
- [x] `connexion.html` (+ mot de passe oublié) / `inscription.html` (politique de mdp + consentement RGPD)
- [x] `commande.html` — coordonnées, prestation, menu, nb personnes, récapitulatif de prix
- [x] `contact.html` — titre, description, mail
- [x] `mentions-legales.html` / `cgv.html` (clause des 600 € de matériel, réduction 10 %, livraison 5 € + 0,59 €/km)
- [x] Dashboards : espace utilisateur / employé / admin (squelettes avec données de démo)
- [x] `style.css` — responsive mobile-first, conforme charte graphique
- [x] Accessibilité RGAA : sémantique, skip-link, labels visibles, focus visible, aria-current, sr-only, aria-live
- [x] Test de fumée : les 12 pages + CSS + JS répondent en HTTP 200 via `php -S`
- [ ] Relecture visuelle par Alexandre (bureau + mobile) *(action Alexandre)*

## Phase 4 — Back : socle & authentification ✅

> Branche : `feature/auth` — mergée dans `develop` le 03/07/2026

- [x] `backend/config/database.php` — connexion PDO (requêtes préparées réelles, erreurs en exceptions) + lecture `.env`
- [x] `backend/config/api.php` — réponses JSON, lecture du corps, politique de mot de passe
- [x] `backend/config/session.php` — session durcie (httponly, samesite, strict mode) + `exigerRole()` côté serveur
- [x] `backend/auth/register.php` — validation serveur complète, bcrypt, rôle « utilisateur », mail de bienvenue, anti-doublon (409)
- [x] `backend/auth/login.php` — message d'erreur unique (anti-énumération), régénération d'ID de session, comptes désactivés refusés
- [x] `backend/auth/logout.php` — destruction complète (session + cookie)
- [x] `backend/auth/reset-password.php` — token 64 hex à usage unique, expiration 1 h, réponse identique que le compte existe ou non
- [x] `backend/mail/send-mail.php` — service centralisé + modèles (journalisé en dev, API branchée en Phase 10)
- [x] `frontend/assets/js/auth.js` — connexion (redirection selon rôle), inscription, mot de passe oublié (demande + nouveau mdp via `?token=`)
- [x] Test de bout en bout : 12 scénarios (mdp faible 422, doublon 409, mauvais mdp 401, rôles, reset complet, token à usage unique, ancien mdp refusé)

## Phase 5 — Menus dynamiques ✅

> Branche : `feature/menus` — mergée dans `develop` le 03/07/2026

- [x] `backend/menus/get-menus.php` — liste publique + 5 filtres cumulables (prix max, fourchette, thème, régime, nb personnes)
- [x] `backend/menus/get-menu.php` — détail complet (galerie, plats groupés par type avec allergènes, conditions, stock, seuil de réduction)
- [x] `backend/menus/get-filtres.php` — thèmes et régimes depuis la base (rien de codé en dur)
- [x] `backend/menus/create-menu.php` / `update-menu.php` / `delete-menu.php` — réservé employé/admin (`exigerRole`), transactions, suppression douce (`actif = 0`, l'historique des commandes est préservé)
- [x] `backend/avis/get-avis.php` — avis validés uniquement, nom réduit à l'initiale (RGPD)
- [x] `frontend/assets/js/filtres.js` — filtres **dynamiques sans rechargement** (fetch + debounce), rendu DOM via `textContent` (anti-XSS)
- [x] `frontend/assets/js/menu-detail.js` — détail chargé selon `?id=`, bouton commander pré-rempli (`commande.html?menu=X`), gestion menu épuisé
- [x] `frontend/assets/js/accueil.js` — avis validés de l'accueil chargés depuis l'API
- [x] Tests : 15 scénarios (filtres seuls et combinés, 404, 401 sans session, 403 pour un client, create/update/delete par employé)
- [ ] ~~CRUD plats et horaires~~ → déplacé en Phase 7 avec l'interface employé qui l'utilise

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
- [ ] CRUD plats et horaires (endpoints + interface employé) *(déplacé depuis la Phase 5)*
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
| 03/07/2026 | Phase 5 sur `feature/menus` : suppression douce des menus (`actif=0`, FK RESTRICT préserve l'historique), rendu DOM en `textContent` uniquement (anti-XSS), filtre « personnes » = menus dont le minimum est accessible pour le nombre de convives saisi. CRUD plats/horaires déplacé en Phase 7 (avec son interface). |
| 03/07/2026 | Phase 4 mergée dans `develop` après validation. |
| 03/07/2026 | Phase 4 sur `feature/auth` : API JSON (un endpoint = un fichier), session PHP durcie plutôt que JWT (app même origine, plus simple et révocable), mails journalisés en dev dans `mails.log` (gitignoré), réponse anti-énumération sur login et reset. PHP local : `pdo_mysql`, `openssl`, `curl`, `mbstring` activés. |
| 03/07/2026 | Phase 3 mergée dans `develop` après validation. |
| 03/07/2026 | Phase 3 sur `feature/front-statique` : 12 pages statiques + charte appliquée en CSS (variables), burger en JS minimal accessible, visuels de substitution en CSS en attendant les photos. Env local : MariaDB 11.8 + MongoDB 8.3 portables (sans droits admin) dans `C:\Users\ahyje\tools`. |
| 03/07/2026 | Phase 2 mergée dans `develop` après test local validé par Alexandre. |
| 03/07/2026 | Phase 2 sur `feature/database` : statuts en ENUM (lisibilité + intégrité), avis lié à la commande (UNIQUE, un avis par commande terminée), hashs bcrypt réels dans les fixtures, données de démo alignées sur les maquettes. Base Mongo séparée `vite_et_gourmand_stats`. |
| 03/07/2026 | Phase 1 mergée dans `develop` après validation. |
| 03/07/2026 | Phase 1 sur `feature/conception` : maquettes en SVG (exportables PDF), MCD enrichi (suivi historisé, galerie d'images, reset tokens), diagrammes en Mermaid (rendus sur GitHub), outil de gestion de projet : **Trello**. |
| 03/07/2026 | Création de la doc de pilotage (cahier des charges, livrables, conventions, roadmap). Règle actée : **aucun merge vers develop/main sans validation d'Alexandre**. |
| avant | Squelette du projet + branches main/develop + remote GitHub. |
