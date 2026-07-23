# 🚀 Documentation de déploiement — Vite & Gourmand

> Livrable ECF : « documentation du déploiement expliquant la démarche et
> les différentes étapes ». Le déploiement effectif sera réalisé **en fin de
> projet** ; ce document décrit la démarche retenue et les étapes à suivre.
>
> **Statut : préparé, pas encore exécuté.** Les liens de l'application en ligne
> seront ajoutés au [README](../README.md) une fois la mise en ligne faite.

## 1. Démarche et choix de l'hébergeur

L'application a trois composants à héberger : le code PHP + front, une base
**MySQL/MariaDB** et une base **MongoDB**. Options envisagées (proposées par
l'énoncé) :

| Hébergeur | Avantages | Points d'attention |
|---|---|---|
| **[Render](https://render.com)** ou **[Railway](https://railway.app)** | PHP + bases managées, HTTPS auto, offre gratuite | Bases parfois payantes au-delà d'un quota |
| **[fly.io](https://fly.io)** | Conteneurs légers, proche de la config locale | Nécessite un Dockerfile |
| **[Heroku](https://heroku.com)** | Simple, add-ons MySQL/Mongo | Offre gratuite supprimée |
| **[Azure](https://azure.microsoft.com)** | Crédits étudiants, complet | Prise en main plus lourde |
| Hébergement mutualisé (OVH, o2switch…) | PHP/MySQL natif, peu cher | MongoDB rarement inclus → Mongo sur **MongoDB Atlas** (gratuit) |

**Choix retenu (22/07/2026) : Railway**, avec les trois composants sur la même
plateforme (application conteneurisée, MySQL, MongoDB).

Le facteur décisif a été l'extension PHP **`mongodb`**. Elle n'est pas fournie
d'origine avec PHP : elle s'installe via PECL et doit être compilée. La plupart
des hébergements mutualisés ne la proposent pas, ce qui aurait cassé la page de
statistiques de l'espace administrateur — or le NoSQL est une exigence du
cahier des charges. Déployer une **image Docker** (voir [`Dockerfile`](../Dockerfile))
permet de maîtriser exactement les extensions installées, au lieu de dépendre de
ce que l'hébergeur a bien voulu activer.

Apache a été préféré au serveur intégré de PHP pour que le
[`.htaccess`](../.htaccess) de la racine s'applique tel quel : c'est lui qui
porte la liste blanche des fichiers servis (Phase 9) et les en-têtes de
sécurité. L'image active donc `AllowOverride All`, sans quoi le fichier serait
purement et simplement ignoré et les fichiers sensibles redeviendraient
accessibles.

Deux adaptations du code ont été nécessaires pour la production :

- `chargerEnv()` lit désormais aussi les **variables d'environnement** du
  serveur, et plus seulement un fichier `.env` — l'hébergeur injecte les
  secrets, aucun n'est écrit sur disque ni dans le dépôt ;
- la session détecte le HTTPS via l'en-tête **`X-Forwarded-Proto`** : derrière
  un proxy inverse qui termine le TLS, `$_SERVER['HTTPS']` est vide et le
  cookie aurait perdu son attribut `secure` alors que le site est bien en HTTPS.

## 2. Pré-requis avant mise en ligne

- [ ] Dépôt GitHub **passé en public** (livrable ECF).
- [ ] Variables d'environnement de production prêtes (voir §4) — **jamais**
      committer le vrai `.env`.
- [ ] HTTPS activé (obligatoire : le cookie de session est `secure`).
- [ ] `display_errors` déjà désactivé sur l'API (fait en Phase 9).

## 3. Étapes de déploiement

### 3.1 Base de données relationnelle
1. Créer la base MySQL/MariaDB chez l'hébergeur.
2. Importer le schéma puis les données :
   ```bash
   mysql -h HOTE -u UTILISATEUR -p BASE < database/schema.sql
   mysql -h HOTE -u UTILISATEUR -p BASE < database/fixtures.sql
   ```

### 3.2 Base NoSQL
1. Créer un cluster gratuit sur **MongoDB Atlas** (ou un service Mongo managé).
2. Autoriser l'IP du serveur applicatif.
3. Initialiser la collection :
   ```bash
   mongosh "URI_ATLAS" --quiet database/mongodb-config.js
   ```

### 3.3 Application
1. Connecter le dépôt GitHub à l'hébergeur (déploiement automatique à chaque
   push sur `main`).
2. Définir les variables d'environnement (§4) dans le tableau de bord de
   l'hébergeur.
3. **Racine web** : configurer le serveur pour appliquer la protection des
   fichiers (le `.htaccess` fourni sur Apache ; sur un autre serveur,
   reproduire la liste blanche du `router.php`).
4. Point d'entrée : `/frontend/index.html` (ou `/` → redirection via `index.php`).

### 3.4 Vérifications post-déploiement
- [ ] La page d'accueil s'affiche avec les avis (API + MySQL OK).
- [ ] Les filtres de menus fonctionnent (API OK).
- [ ] Une connexion réussit (sessions OK).
- [ ] L'espace admin affiche le graphique (MongoDB OK).
- [ ] `GET /.env` renvoie **404** (protection des fichiers OK).
- [ ] Le site est bien en **HTTPS**.

## 4. Variables d'environnement de production

| Variable | Description |
|---|---|
| `DB_HOST` | Hôte MySQL de production |
| `DB_NAME` | Nom de la base |
| `DB_USER` / `DB_PASS` | Identifiants MySQL de production (jamais `root`) |
| `MONGO_URI` | URI de connexion MongoDB Atlas |
| `MONGO_DB` | Nom de la base de statistiques |
| `MAIL_API_KEY` | Clé du fournisseur d'envoi de mails (Brevo, Mailgun…) |
| `MAIL_FROM` | Adresse expéditrice de l'entreprise |

> En production, `MAIL_API_KEY` est renseignée : les mails partent réellement
> (en local, ils sont journalisés dans `backend/mail/mails.log`). Le point
> d'intégration est déjà prévu dans `backend/mail/send-mail.php`.

## 5. Après la mise en ligne

- Renseigner dans le [README](../README.md) : lien de l'application déployée et
  lien du dépôt public.
- Compléter les mentions légales (hébergeur, SIRET) — champs déjà réservés.
- Merger `develop` → `main` (version livrée) et vérifier le déploiement auto.
