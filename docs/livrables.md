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
  - [ ] Une branche `feature/xxx` par fonctionnalité, issue de `develop`
  - [ ] Merge vers `develop` après test de chaque fonctionnalité
  - [ ] Merge `develop` → `main` une fois `develop` correctement testée
- [ ] **Fichiers SQL** :
  - [ ] `database/schema.sql` — création de la base
  - [ ] `database/fixtures.sql` — intégration de données (⚠️ fichier SQL brut exigé, pas seulement des fixtures/migrations d'un framework)
- [ ] **Manuel d'utilisation (PDF)** :
  - [ ] Présentation de l'application
  - [ ] Identifiants de test pour chaque parcours (utilisateur, employé, admin)
- [ ] **Charte graphique (PDF)** :
  - [ ] Palette de couleurs
  - [ ] Police(s) utilisée(s)
  - [ ] Export des maquettes : **3 maquettes bureautiques + 3 maquettes mobiles** (wireframes & mockups)
- [ ] **Documentation de gestion de projet** :
  - [ ] Explication de la méthode de gestion de projet
- [ ] **Documentation technique** :
  - [ ] Réflexions technologiques initiales (justification des choix — très important pour le jury)
  - [ ] Configuration de l'environnement de travail
  - [ ] Modèle conceptuel de données (MCD) ou diagramme de classes
  - [ ] Diagramme de cas d'utilisation
  - [ ] Diagramme de séquence
  - [ ] Documentation du déploiement (démarche + étapes)

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
- [ ] Installer et configurer son environnement de travail
- [ ] Maquetter des interfaces utilisateur
- [ ] Réaliser des interfaces utilisateur statiques
- [ ] Développer la partie dynamique des interfaces

### Activité type 2 — Back-end sécurisé
- [ ] Mettre en place une base de données relationnelle
- [ ] Développer des composants d'accès aux données **SQL et NoSQL**
- [ ] Développer des composants métier côté serveur
- [ ] Documenter le déploiement d'une application dynamique
