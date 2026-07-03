# 📋 Cahier des charges — Vite & Gourmand

> Source : énoncé ECF Studi — TP Développeur Web et Web Mobile.
> Ce document reformule l'intégralité des besoins fonctionnels et règles métier. C'est la **référence unique** pendant le développement.

## 1. Contexte

**Vite & Gourmand** est un traiteur bordelais tenu par Julie et José depuis 25 ans. Prestations pour tout événement (Noël, Pâques, etc.) via un menu en constante évolution. Aujourd'hui les menus sont envoyés par mail aux habitués ; l'application web doit **augmenter la visibilité** et permettre de **consulter les menus et commander en ligne**.

Le développement est confié à l'entreprise **FastDev** (nous), sous la direction d'un chef de projet.

## 2. Rôles et permissions

| Rôle | Description | Accès |
|---|---|---|
| **Visiteur** | Non authentifié | Accueil, vue globale + détail des menus, contact, création de compte |
| **Utilisateur** | Client inscrit (rôle attribué automatiquement à l'inscription) | Idem visiteur + commander, espace utilisateur, avis |
| **Employé** | Compte créé par l'admin | Espace employé : gestion menus/plats/horaires, commandes, avis |
| **Administrateur** | Compte créé **manuellement** (jamais depuis l'application) | Tout ce que fait l'employé + gestion des comptes employés + statistiques |

⚠️ **Règles importantes** :
- Il ne doit **pas** être possible de créer un compte Administrateur depuis l'application (compte créé pour José directement en base).
- Un compte employé doit pouvoir être **désactivé** (départ de l'entreprise).
- Respect **RGPD** et sécurité sur toutes les catégories de comptes.

## 3. Pages et fonctionnalités

### 3.1 Page d'accueil
- Présentation de l'entreprise
- Mise en avant du professionnalisme de l'équipe
- Affichage des **avis clients validés** (uniquement ceux validés par un employé)

### 3.2 Menu de navigation (header)
Au minimum :
- Retour vers la page d'accueil
- Accès à tous les menus
- Connexion (employé, administrateur, utilisateur)
- Accès à la page de contact

### 3.3 Pied de page (footer)
- **Horaires** visibles, du lundi au dimanche
- Lien vers **Mentions légales**
- Lien vers **Conditions Générales de Vente (CGV)**

### 3.4 Vue globale des menus
Accessible aux visiteurs **et** aux personnes authentifiées. Pour chaque menu, afficher : titre, description, nombre de personnes minimum, prix, bouton « voir le détail ».

**Filtres** (mise à jour **dynamique, sans rechargement de page**) :
- Prix maximum
- Fourchette de prix
- Thème
- Régime
- Nombre de personnes minimum

### 3.5 Vue détaillée d'un menu
- Afficher **toutes** les informations du menu (voir §4 Entité Menu)
- Bouton **« Commander »** :
  - Utilisateur authentifié → redirection vers la page de commande avec le **menu pré-rempli**
  - Visiteur → invitation à se connecter ou créer un compte d'abord
- ⚠️ Les **conditions du menu** doivent être mises en évidence (éviter toute réclamation client : « je n'avais pas vu l'information »)

### 3.6 Création de compte
Informations demandées :
- Nom et prénom
- Numéro de GSM
- Adresse mail et adresse postale
- Mot de passe sécurisé : **10 caractères minimum, au moins 1 caractère spécial, 1 majuscule, 1 minuscule, 1 chiffre**

À la création : rôle **« utilisateur »** attribué + **mail de bienvenue automatique**.

### 3.7 Connexion
- Username = adresse mail + mot de passe
- **Mot de passe oublié** : bouton dédié → formulaire demandant le mail → envoi d'un **lien de réinitialisation par mail**

### 3.8 Commande d'un menu
Informations de la prestation :
- Nom, prénom, mail du client (**auto-remplis** depuis le compte)
- GSM du client (**auto-rempli**)
- Adresse et date de la prestation
- Heure et date souhaitées de livraison, suivi du lieu

Puis choix du menu (pré-rempli si arrivée via le bouton « Commander »), puis choix du **nombre de personnes**.

**Règles de prix** :
- Obligation de commander au minimum pour le **nombre de personnes minimum** du menu
- Le prix se met à jour selon le nombre de personnes
- **Réduction de 10 %** si la commande compte **au moins 5 personnes de plus** que le minimum du menu
- **Frais de livraison** : 5 € + **0,59 €/km** parcouru si la livraison est **hors de Bordeaux** (gratuit dans Bordeaux)
- **Vue détaillée du prix avant validation** (prix menu + prix livraison)

Après commande : **mail de confirmation** automatique au client.

### 3.9 Espace Utilisateur
- Visualiser le **détail de toutes ses commandes**
- Modifier ses **informations personnelles**
- **Annuler** une commande tant qu'elle n'est pas passée en « accepté »
- **Modifier** une commande tant qu'elle n'est pas « accepté » (tout est modifiable **sauf le choix du menu**)
- Une fois « accepté » : accès au **suivi de commande** (historique de tous les états avec **date et heure** de chaque changement)
- Quand la commande est « terminée » : **mail de notification** invitant à donner son **avis** (note **1 à 5** + commentaire) depuis la commande

### 3.10 Espace Employé
- Modifier / supprimer les **menus**, **plats** et **horaires**
- ⚠️ Ne peut **pas** modifier/annuler une commande **avant d'avoir contacté le client** (appel GSM ou mail) → saisie obligatoire d'un **motif** + **mode de contact**
- **Filtre sur les commandes** : par **statut** ou par **client**
- Mise à jour du statut des commandes (voir §5 Cycle de vie)
- **Valider ou refuser les avis** clients (seuls les avis validés apparaissent sur l'accueil)

### 3.11 Espace Administrateur
- Tout ce qu'un employé peut faire
- **Créer un compte employé** : email (= username) + mot de passe → l'employé reçoit un **mail de notification** (⚠️ **sans le mot de passe** — il doit se rapprocher de l'admin)
- **Désactiver** un compte employé
- **Statistiques** : nombre de commandes par menu, comparables via un **graphique** — données issues de la **base NoSQL (MongoDB)**
- **Chiffre d'affaires par menu** avec filtres **par menu** et **par période**

### 3.12 Page Contact
Formulaire : titre, description, adresse mail du demandeur.
→ La demande est **envoyée par mail à l'entreprise**.

## 4. Entité « Menu » (caractéristiques)

Configurable depuis les espaces **Administrateur** et **Employé** :

- Titre
- **Galerie d'images**
- Description (présentation du menu)
- **Thème** : Noël, Pâques, classique, évènement…
- Liste de plats : **entrée, plat, dessert**
- Nombre de personnes **minimum**
- **Prix** pour le nombre de personnes minimum
- Chaque **plat** peut posséder une liste d'**allergènes**
- **Conditions** du menu (ex. : commander X jours/semaines avant la prestation, précautions de stockage)
- **Régime** : végétarien, vegan, classique… (catégorie extensible)
- Une entrée / un plat / un dessert peut être présent dans **plusieurs menus** (relation N-N)
- **Stock disponible** (ex. : il reste 5 commandes possibles de ce menu)

## 5. Cycle de vie d'une commande

| Statut | Description | Effets |
|---|---|---|
| *(créée)* | Commande passée par l'utilisateur | Mail de confirmation. Annulation/modification possible par le client |
| **accepté** | Validée par l'équipe | Fin de l'annulation/modification client ; accès au suivi |
| **en préparation** | En cuisine | |
| **en cours de livraison** | Équipe logistique en route | |
| **livré** | Client livré | |
| **en attente du retour de matériel** | Matériel prêté au client | **Mail automatique** : si non restitué sous **10 jours ouvrés** → **600 € de frais** (mentionné dans les CGV). Le client doit contacter la société pour rendre le matériel |
| **terminée** | Livrée sans prêt de matériel, ou matériel restitué | **Mail** invitant le client à donner son avis |

Le suivi conserve **chaque état avec date et heure** de modification.

## 6. Mails automatiques (récapitulatif)

1. **Bienvenue** — à l'inscription
2. **Réinitialisation du mot de passe** — lien envoyé sur demande
3. **Confirmation de commande** — après validation de la commande
4. **Invitation à donner un avis** — quand la commande passe à « terminée »
5. **Rappel matériel** — au passage à « en attente du retour de matériel » (600 € si non restitué sous 10 jours ouvrés)
6. **Création de compte employé** — notification sans le mot de passe
7. **Contact** — demande transmise par mail à l'entreprise

## 7. Contraintes transverses

- **RGPD** : consentement, minimisation des données, droit d'accès/suppression
- **Sécurité** : mots de passe hachés, requêtes préparées (PDO), protection XSS/CSRF, validation côté client **et** serveur
- **Accessibilité** : conformité **RGAA**
- **Déploiement obligatoire** : pénalités si l'application n'est pas en ligne et fonctionnelle à la livraison
- **Base de données relationnelle ET non relationnelle** obligatoires (seule contrainte technique imposée)

## 8. Stack technique retenue

| Couche | Techno | Justification |
|---|---|---|
| Front | HTML5, CSS3, JS vanilla | Maîtrise des fondamentaux exigée par le référentiel |
| Back | PHP 8 + PDO | Requêtes préparées natives, stack de l'exemple ECF |
| BDD relationnelle | MySQL | Répandu, bien documenté, compatible hébergeurs |
| BDD NoSQL | MongoDB | Statistiques de commandes (exigence admin) |
| Déploiement | à définir (fly.io / Vercel / Azure…) | |
