# Vite & Gourmand

Application web pour le traiteur Vite & Gourmand — Bordeaux.

## Stack technique

- **Front** : HTML5, CSS3, JavaScript
- **Back** : PHP avec PDO
- **BDD relationnelle** : MySQL
- **BDD NoSQL** : MongoDB
- **Déploiement** : (à compléter)

## Installation en local

### Prérequis
- PHP 8+
- MySQL
- MongoDB

### Étapes

1. Cloner le repo
\`\`\`bash
git clone https://github.com/TON_USERNAME/vite-et-gourmand.git
cd vite-et-gourmand
\`\`\`

2. Copier le fichier d'environnement
\`\`\`bash
cp .env.example .env
\`\`\`

3. Remplir le fichier `.env` avec tes valeurs locales

4. Importer la base de données
\`\`\`bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/fixtures.sql
\`\`\`

5. Lancer le serveur PHP
\`\`\`bash
php -S localhost:8000 -t frontend/
\`\`\`

## Structure du projet

\`\`\`
vite-et-gourmand/
├── frontend/        → Pages HTML, CSS, JS
├── backend/         → PHP (auth, menus, commandes, mail)
├── database/        → Fichiers SQL + config MongoDB
├── docs/            → Maquettes, charte graphique, documentation
├── .env.example     → Template variables d'environnement
└── README.md
\`\`\`

## Branches Git

- \`main\` → production stable
- \`develop\` → développement en cours
- \`feature/xxx\` → fonctionnalité en cours
