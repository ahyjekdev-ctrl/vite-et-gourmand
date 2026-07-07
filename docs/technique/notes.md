# 🛠️ Notes techniques — Configuration de l'environnement de travail

> Livrable « documentation technique » : configuration de l'environnement.
> Les autres volets sont dans leurs fichiers dédiés : [MCD](../conception/mcd.md), [diagrammes](../conception/diagrammes.md), [conventions & sécurité](../conventions.md), [charte graphique](../charte-graphique.md), [gestion de projet](../gestion-de-projet.md).

## 1. Poste de développement

| Élément | Choix | Détail |
|---|---|---|
| OS | Windows 11 Pro | |
| Éditeur | Visual Studio Code | |
| Versionnage | Git + GitHub | Workflow décrit dans [conventions.md](../conventions.md) §1 |
| PHP | 8.5.6 (zip officiel windows.php.net) | Extensions activées dans `php.ini` : `pdo_mysql`, `openssl`, `curl`, `mbstring`, `mongodb` (DLL PECL 2.3.3, build 8.5-ts-vs17-x64) |
| BDD relationnelle | MariaDB 11.8 LTS (portable) | Compatible MySQL — autorisée par l'énoncé ; installée sans droits admin |
| BDD NoSQL | MongoDB 8.3 (portable) + mongosh 2.9 | |
| Serveur de dev | Serveur intégré PHP | `php -S localhost:8000` depuis la racine (sert le front **et** le back) |

## 2. Démarrage d'une session de travail

```powershell
# 1. Démarrer les bases (MariaDB :3306 + MongoDB :27017)
powershell -File C:\Users\ahyje\tools\demarrer-bdd.ps1

# 2. Lancer le serveur de développement (racine du projet)
php -S localhost:8000

# 3. Ouvrir http://localhost:8000/frontend/index.html
```

Arrêt des bases : `powershell -File C:\Users\ahyje\tools\arreter-bdd.ps1`.
Les binaires (`mysql`, `mongosh`, `mongod`) sont dans le PATH utilisateur.

## 3. Initialisation / réinitialisation des données

```bash
mysql -u root < database/schema.sql      # création des 15 tables
mysql -u root < database/fixtures.sql    # données de démonstration
mongosh --quiet database/mongodb-config.js   # collection stats_commandes
```

Comptes de test : voir l'en-tête de [database/fixtures.sql](../../database/fixtures.sql).

## 4. Configuration applicative

- Secrets dans `.env` à la racine (copié depuis `.env.example`, jamais commité).
- En dev, les mails ne partent pas : ils sont journalisés dans `backend/mail/mails.log` (gitignoré) — pratique pour récupérer le lien de réinitialisation de mot de passe.

## 5. Choix notables (résumé — détails dans le journal de la [ROADMAP](../../ROADMAP.md))

- **PHP vanilla + PDO** plutôt qu'un framework : exigence pédagogique du titre (maîtrise des fondamentaux), requêtes préparées réelles (`EMULATE_PREPARES` désactivé).
- **Sessions PHP durcies** plutôt que JWT : application même origine, révocation immédiate, pas de stockage de token côté client.
- **MariaDB portable** plutôt que MySQL MSI : installation sans droits administrateur, drop-in compatible.
- **API JSON, un endpoint = un fichier** : lisible pour le jury, testable en curl.
- **Suppression douce des menus** (`actif = 0`) : l'historique des commandes reste intact (FK `RESTRICT`).
