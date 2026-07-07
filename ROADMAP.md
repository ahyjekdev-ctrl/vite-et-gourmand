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

**Phase actuelle : Phase 8 — Contact, faite sur `feature/contact` (en attente de validation avant merge)**

Phases 1 à 7 mergées dans `develop` : **toutes les fonctionnalités du cahier des charges sont développées**. Phase 8 écrite et testée : formulaire de contact public transmis par mail à l'entreprise, avec anti-spam honeypot. Il ne reste que la Phase 9 (passe sécurité + RGAA + jeux d'essai) et la Phase 10 (déploiement + livrables PDF + repo public). Côté Alexandre : créer le board Trello.

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

## Phase 6 — Commandes ✅

> Branche : `feature/commandes` — mergée dans `develop` le 07/07/2026, testée par Alexandre dans le navigateur

- [x] `backend/commandes/calcul-prix.php` — composant métier partagé (création **et** modification recalculent avec les mêmes règles)
- [x] `backend/auth/me.php` — profil de la personne connectée (pré-remplissage, espace utilisateur)
- [x] `backend/commandes/create-commande.php` :
  - [x] Auto-remplissage infos client (via me.php, identité prise en session côté serveur)
  - [x] Contrôle nombre de personnes ≥ minimum du menu (+ date future, heure, stock)
  - [x] Réduction 10 % si ≥ min + 5 personnes
  - [x] Frais de livraison hors Bordeaux : 5 € + 0,59 €/km (distance obligatoire hors Bordeaux)
  - [x] Décrément du stock sous verrou (`SELECT … FOR UPDATE` : pas de survente en cas de commandes simultanées)
  - [x] Mail de confirmation avec détail du prix
- [x] `frontend/assets/js/commande.js` — pré-remplissage, menu pré-sélectionné (`?menu=X`), champ distance affiché hors Bordeaux, calcul du prix en direct + récap détaillé avant validation
- [x] `backend/commandes/get-commandes.php` — client : ses commandes + suivi complet ; employé : toutes + filtres statut/client
- [x] `backend/commandes/update-commande.php` :
  - [x] Annulation/modification par le client tant que non « accepté » (tout sauf le menu, prix recalculé, stock restitué à l'annulation)
  - [x] Changement de statut par l'employé avec transitions contrôlées (créé → accepté → … → terminée)
  - [x] Motif + mode de contact obligatoires pour modif/annulation par employé
  - [x] Historique de suivi (statut + date + heure) à chaque changement
  - [x] Mails automatiques : matériel (600 € / 10 jours ouvrés), terminée (invitation avis)
- [x] Tests de bout en bout : prix exacts (180 € / 477,72 €), 422 (minimum, distance, date passée), 409 après acceptation, transitions interdites, stock restitué, 4 mails journalisés
- [ ] Insertion des statistiques MongoDB à la commande *(Phase 7, avec l'extension PHP mongodb)*

## Phase 7 — Avis & espaces ⬜

> Branche : `feature/avis-espaces`

- [x] `backend/avis/gestion-avis.php` — dépôt (note 1–5 + commentaire, commande terminée uniquement, un avis max par commande), validation/refus par employé
- [x] CRUD plats (avec allergènes) et horaires — endpoints + interface employé *(déplacé depuis la Phase 5)*
- [x] Espace utilisateur complet : commandes réelles, suivi daté, annulation/modification tant que non acceptée, dépôt d'avis, infos personnelles modifiables (`update-profil.php`)
- [x] Espace employé complet : commandes filtrées + transitions de statut + annulation motivée, modération des avis, menus (y compris désactivés via `?tous=1`, réactivation, création complète), plats, horaires
- [x] Espace admin : création compte employé (mail **sans** mot de passe — vérifié : 0 occurrence dans le journal), désactivation/réactivation
- [x] Module front commun aux espaces (`espace-commun.js`) : garde par rôle, appels API, formats, badges — rendu DOM en `textContent` (anti-XSS)
- [x] Tests 7a : 16 scénarios (409 avis en double/commande non terminée, 403 modération client et gestion employés, mail sans mdp, connexion refusée après désactivation, menus inactifs invisibles du public…)
- [x] **7b — Statistiques MongoDB** : extension PHP mongodb 2.3.3 (DLL PECL), `backend/config/mongo.php` (insertions best effort, agrégations), stats insérées/mises à jour à chaque événement de commande, nb de commandes par menu en **graphique canvas natif** (aucune librairie externe)
- [x] **7b** — Chiffre d'affaires par menu (`get-stats.php`, admin uniquement), filtres cumulables par menu et par période, annulées exclues, totaux vérifiés (1 848,09 € sur les fixtures)
- [x] Tests 7b : 7 scénarios (403 employé, agrégations exactes, filtres, insertion auto à la commande, maj du statut Mongo à chaque transition, annulée exclue des stats)

## Phase 8 — Contact 🔄

> Branche : `feature/contact` — **en attente de validation avant merge**

- [x] Formulaire contact (titre, description, mail) branché en fetch (`contact.js`)
- [x] Envoi de la demande par mail à l'entreprise (`backend/contact/envoyer.php`, accessible aux visiteurs)
- [x] Anti-spam honeypot : champ invisible, faux succès renvoyé aux robots, aucun mail envoyé
- [x] Tests : envoi valide journalisé, 422 (mail invalide, champs vides), spam silencieusement ignoré

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
| 08/07/2026 | Phase 8 sur `feature/contact` : le formulaire de contact reste public (les visiteurs doivent pouvoir écrire sans compte), anti-spam par honeypot (réponse identique au succès pour ne pas renseigner les robots), la demande part vers la boîte `MAIL_FROM` de l'entreprise avec l'adresse du demandeur en corps de mail. |
| 08/07/2026 | Phase 7b mergée dans `develop` après validation. |
| 08/07/2026 | Phase 7b sur `feature/stats-mongodb` : extension PHP mongodb via DLL PECL (2.3.3, 8.5-ts-vs17-x64), écritures statistiques « best effort » (Mongo en panne n'empêche jamais une vente), clé de rapprochement `numero_commande` + index unique côté Mongo, graphique en canvas natif (pas de dépendance externe, données accessibles dans le tableau voisin). |
| 07/07/2026 | Phase 7a mergée dans `develop` après test navigateur validé. |
| 07/07/2026 | Phase 6 mergée dans `develop` après test navigateur validé, puis **premier merge `develop` → `main`** : `main` porte désormais une version stable et testée (phases 1-6). Ajout d'un `index.php` à la racine (redirection vers l'accueil). |
| 07/07/2026 | Phase 6 sur `feature/commandes` : calcul de prix dans un composant partagé (création/modification cohérentes), verrou `FOR UPDATE` sur le stock, transitions de statut en liste blanche, stock restitué à l'annulation. Bug corrigé : un paramètre nommé PDO ne peut pas être réutilisé avec `EMULATE_PREPARES` désactivé (filtre client de l'espace employé). Stats MongoDB reportées en Phase 7 (extension PHP à installer). |
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
