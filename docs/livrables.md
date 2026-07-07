# 📦 Livrables ECF — Checklist

> Tout ce qui doit être rendu pour l'ECF et le dossier projet. Cocher au fur et à mesure.

## 1. Livrables principaux

- [ ] **Lien du dépôt GitHub PUBLIC** avec le code de l'application
  - Dépôt : https://github.com/ahyjekdev-ctrl/vite-et-gourmand *(à passer en public avant la remise)*
- [ ] **Lien de l'application déployée** (en ligne et fonctionnelle — pénalités sinon)
- [ ] **Lien du logiciel de gestion de projet** (Jira, Notion, Trello…)
- [ ] **Copie à rendre** (Word/Excel) renommée : `ECF_TPDeveloppeurWebEtWebMobile_copiearendre_HYJEK_Alexandre`

## 2. Contenu obligatoire du dépôt Git

- [x] `README.md` avec la démarche de déploiement **en local**
- [ ] Bonnes pratiques Git appliquées :
  - [x] Branche principale `main`
  - [x] Branche de développement `develop`
  - [x] Une branche `feature/xxx` par fonctionnalité, issue de `develop` (conception, database, front-statique, auth, menus…)
  - [x] Merge vers `develop` après test de chaque fonctionnalité (merges `--no-ff`, validés un par un)
  - [x] Merge `develop` → `main` une fois `develop` correctement testée (premier snapshot stable le 07/07 ; merge final avant la remise)
- [x] **Fichiers SQL** :
  - [x] `database/schema.sql` — création de la base (15 tables, testé à l'import)
  - [x] `database/fixtures.sql` — intégration de données (SQL brut, testé à l'import)
- [ ] **Manuel d'utilisation (PDF)** :
  - [ ] Présentation de l'application
  - [ ] Identifiants de test pour chaque parcours (utilisateur, employé, admin)
- [ ] **Charte graphique (PDF)** :
  - [x] Palette de couleurs → [charte-graphique.md](charte-graphique.md)
  - [x] Police(s) utilisée(s) → Playfair Display + Lato
  - [x] Maquettes sources : 3 bureautiques + 3 mobiles, wireframes **et** mockups → [maquettes/](maquettes/)
  - [ ] Export **PDF** de l'ensemble *(Phase 10)*
- [x] **Documentation de gestion de projet** :
  - [x] Explication de la méthode de gestion de projet → [gestion-de-projet.md](gestion-de-projet.md)
- [ ] **Documentation technique** :
  - [x] Réflexions technologiques initiales → [cahier-des-charges.md](cahier-des-charges.md) §8, [mcd.md](conception/mcd.md) (choix justifiés), journal de la [ROADMAP](../ROADMAP.md)
  - [x] Configuration de l'environnement de travail → [technique/notes.md](technique/notes.md)
  - [x] Modèle conceptuel de données (MCD) → [conception/mcd.md](conception/mcd.md)
  - [x] Diagramme de cas d'utilisation → [conception/diagrammes.md](conception/diagrammes.md)
  - [x] Diagramme de séquence (commande + cycle de vie employé) → [conception/diagrammes.md](conception/diagrammes.md)
  - [ ] Documentation du déploiement (démarche + étapes) *(Phase 10)*

## 3. Dossier projet (pour le jury)

Format : **20 à 30 pages max** (hors page de garde, sommaire, annexes) — **annexes : 20 pages max**.

### Volet Front-end (Activité type 1)
- [ ] Liste des compétences du référentiel couvertes
- [ ] Contexte : entreprise, cahier des charges, contraintes, livrables, environnement humain/technique, objectifs qualité
- [ ] Maquettes (adaptation web + web mobile)
- [ ] Schéma d'enchaînement des maquettes
- [ ] Captures d'écran des interfaces (web + mobile)
- [ ] Extraits de code : interfaces statiques
- [ ] Extraits de code : partie dynamique
- [ ] Éléments de **sécurité côté front**
- [ ] **Jeu d'essai** de la fonctionnalité la plus représentative (données en entrée / attendues / obtenues + analyse des écarts)
- [ ] **Veille sécurité front** (vulnérabilités trouvées, failles corrigées)

### Volet Back-end (Activité type 2)
- [ ] Liste des compétences du référentiel couvertes
- [ ] Présentation de la base de données (schéma conceptuel, schéma physique, script de création)
- [ ] Extraits de code : composants métier
- [ ] Extraits de code : composants d'accès aux données
- [ ] Éléments de **sécurité côté back**
- [ ] **Jeu d'essai** de la fonctionnalité back la plus représentative + analyse des écarts
- [ ] **Veille sécurité back**
- [ ] Annexes : code des composants métier et d'accès aux données les plus significatifs

### Production complémentaire
- [ ] **Support de présentation orale** (soutenance devant jury)

## 4. Compétences du référentiel à couvrir

### Activité type 1 — Front-end sécurisé
- [x] Installer et configurer son environnement de travail → [technique/notes.md](technique/notes.md)
- [x] Maquetter des interfaces utilisateur → wireframes + mockups, bureau + mobile
- [x] Réaliser des interfaces utilisateur statiques → 12 pages HTML/CSS, RGAA
- [x] Développer la partie dynamique des interfaces → filtres sans rechargement, détail par id, auth en fetch, avis dynamiques

### Activité type 2 — Back-end sécurisé
- [x] Mettre en place une base de données relationnelle → schema.sql + fixtures.sql testés
- [x] Développer des composants d'accès aux données **SQL et NoSQL** → SQL : PDO préparé ; NoSQL : `backend/config/mongo.php` (driver MongoDB, agrégations, écritures miroir)
- [x] Développer des composants métier côté serveur → règles d'authentification, politique de mdp, CRUD menus *(commandes en Phase 6)*
- [ ] Documenter le déploiement d'une application dynamique *(Phase 10)*
