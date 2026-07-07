# 🍽️ Vite & Gourmand

Application web pour **Vite & Gourmand**, entreprise de traiteur événementiel à Bordeaux (Julie & José).
Projet réalisé dans le cadre de l'ECF **Titre Professionnel Développeur Web et Web Mobile** (Studi).

L'application permet aux visiteurs de consulter les menus du traiteur, de filtrer selon leurs besoins (prix, thème, régime…), de créer un compte et de commander une prestation. Les employés gèrent les menus et les commandes, l'administrateur pilote les comptes et les statistiques.

## 📚 Documentation

| Fichier | Contenu |
|---|---|
| [ROADMAP.md](ROADMAP.md) | Feuille de route : où on va, ce qui est fait, ce qu'il reste à faire |
| [docs/cahier-des-charges.md](docs/cahier-des-charges.md) | Besoins fonctionnels et règles métier complètes |
| [docs/livrables.md](docs/livrables.md) | Checklist des livrables attendus pour l'ECF |
| [docs/conventions.md](docs/conventions.md) | Workflow Git, conventions de code et règles de sécurité |

## 🛠️ Stack technique

- **Front** : HTML5, CSS3, JavaScript (vanilla)
- **Back** : PHP 8+ avec PDO
- **Base de données relationnelle** : MySQL
- **Base de données NoSQL** : MongoDB (statistiques des commandes)
- **Déploiement** : *(à définir — fly.io / Vercel / Azure…)*

## 🚀 Installation en local

### Prérequis

- [PHP 8+](https://www.php.net/downloads) (avec les extensions `pdo_mysql` et `mongodb`)
- [MySQL 8+](https://dev.mysql.com/downloads/) ou MariaDB
- [MongoDB Community](https://www.mongodb.com/try/download/community)
- [Git](https://git-scm.com/)

### Étapes

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/ahyjekdev-ctrl/vite-et-gourmand.git
   cd vite-et-gourmand
   ```

2. **Configurer les variables d'environnement**
   ```bash
   cp .env.example .env
   ```
   Puis remplir `.env` avec vos valeurs locales :

   | Variable | Description | Exemple |
   |---|---|---|
   | `DB_HOST` | Hôte MySQL | `localhost` |
   | `DB_NAME` | Nom de la base | `vite_et_gourmand` |
   | `DB_USER` | Utilisateur MySQL | `root` |
   | `DB_PASS` | Mot de passe MySQL | |
   | `MAIL_API_KEY` | Clé API du service d'envoi de mails | |
   | `MAIL_FROM` | Adresse expéditrice des mails | `contact@vite-et-gourmand.fr` |

3. **Créer et alimenter la base de données**
   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p < database/fixtures.sql
   ```

4. **Initialiser MongoDB** (collections de statistiques)
   ```bash
   mongosh --quiet database/mongodb-config.js
   ```

5. **Lancer le serveur de développement** (depuis la racine du projet, pour servir le front **et** le back)
   ```bash
   php -S localhost:8000 router.php
   ```
   ⚠️ Le `router.php` est **important pour la sécurité** : sans lui, le serveur intégré de PHP exposerait `.env`, les journaux et les fichiers SQL. Il n'autorise que le front et les endpoints de l'API.

6. Ouvrir [http://localhost:8000](http://localhost:8000) (redirige vers l'accueil)

### Comptes de test

Les identifiants de démonstration (utilisateur, employé, administrateur) sont fournis dans le **manuel d'utilisation** (`docs/manuel-utilisation.pdf`) et créés par `database/fixtures.sql`.

## 📁 Structure du projet

```
vite-et-gourmand/
├── frontend/                → Pages HTML, CSS, JS
│   ├── assets/css/          → Feuilles de style
│   ├── assets/js/           → Scripts (filtres dynamiques, commande, auth)
│   ├── espace-utilisateur/  → Espace client
│   ├── espace-employe/      → Espace employé
│   └── espace-admin/        → Espace administrateur
├── backend/                 → API PHP
│   ├── config/              → Connexion BDD (PDO)
│   ├── auth/                → Inscription, connexion, reset mot de passe
│   ├── menus/               → CRUD menus
│   ├── commandes/           → Gestion des commandes
│   ├── avis/                → Modération des avis
│   └── mail/                → Envoi de mails automatiques
├── database/                → schema.sql, fixtures.sql, config MongoDB
├── docs/                    → Cahier des charges, livrables, conventions, maquettes
├── ROADMAP.md               → Feuille de route du projet
└── README.md
```

## 🌿 Workflow Git

- `main` → branche de production stable
- `develop` → branche d'intégration
- `feature/xxx` → une branche par fonctionnalité, issue de `develop`

Chaque fonctionnalité est développée sur sa branche, testée, puis mergée vers `develop`. Une fois `develop` validée, merge vers `main`. Détails dans [docs/conventions.md](docs/conventions.md).

## 🔗 Liens du projet (livrables ECF)

- **Application déployée** : *(à venir)*
- **Gestion de projet (Trello/Notion)** : *(à venir)*
