# 📖 Manuel d'utilisation — Vite & Gourmand

> Livrable ECF (à exporter en PDF). Ce manuel présente l'application et
> fournit des identifiants pour parcourir chacun des rôles.
> Export PDF : ouvrir ce fichier dans un lecteur Markdown (ou VS Code →
> « Print to PDF »), ou copier dans un traitement de texte.

## 1. Présentation de l'application

**Vite & Gourmand** est l'application web du traiteur événementiel bordelais
du même nom. Elle permet :

- aux **visiteurs** : de découvrir les menus, de les filtrer, d'en consulter
  le détail, de créer un compte et de contacter l'entreprise ;
- aux **clients** (utilisateurs inscrits) : de commander un menu, de suivre,
  modifier ou annuler leurs commandes, et de déposer un avis ;
- aux **employés** : de gérer les menus, plats et horaires, de traiter les
  commandes (statuts) et de modérer les avis ;
- à l'**administrateur** : tout ce que fait un employé, plus la gestion des
  comptes employés et les statistiques (commandes et chiffre d'affaires par menu).

**Technologies** : HTML/CSS/JavaScript (front), PHP 8 + PDO (back),
MySQL/MariaDB (données relationnelles), MongoDB (statistiques).

## 2. Accès et identifiants de test

> Ces comptes sont créés par `database/fixtures.sql`. Les mots de passe
> respectent la politique de sécurité (10+ caractères, majuscule, minuscule,
> chiffre, caractère spécial).

| Rôle | Identifiant (mail) | Mot de passe |
|---|---|---|
| **Administrateur** | `jose@vite-et-gourmand.fr` | `Admin!Vg2026` |
| **Employé** | `julie@vite-et-gourmand.fr` | `Employe!Vg2026` |
| **Client** | `client@demo.fr` | `Client!Vg2026` |
| Clients secondaires | `claire@demo.fr`, `marc@demo.fr`, `sophie@demo.fr` | `Client!Vg2026` |

Page de connexion : **Connexion** dans le menu, ou `/frontend/connexion.html`.

## 3. Parcours VISITEUR (sans compte)

1. **Accueil** : présentation de l'entreprise, mise en avant de l'équipe,
   avis clients validés.
2. **Nos menus** : les 6 menus. Utilisez les filtres (prix maximum, fourchette,
   thème, régime, nombre de personnes) — la liste se met à jour **sans
   rechargement**.
3. **Voir le détail** d'un menu : galerie, description, prix, **conditions
   mises en évidence**, composition (entrées/plats/desserts) et allergènes.
4. **Commander** : redirige vers la connexion (un compte est requis).
5. **Contact** : formulaire (titre, description, mail) transmis à l'entreprise.
6. **Créer un compte** depuis la page de connexion.

## 4. Parcours CLIENT

1. Se connecter avec `client@demo.fr` / `Client!Vg2026`.
2. Depuis un menu → **Commander** : le formulaire est **pré-rempli** avec vos
   coordonnées et le menu choisi.
   - Choisissez le nombre de personnes (≥ minimum du menu) ; le prix se met à
     jour en direct. À partir de 5 personnes au-dessus du minimum : **−10 %**.
   - Ville hors Bordeaux : un champ distance apparaît (**5 € + 0,59 €/km**) ;
     livraison offerte dans Bordeaux.
   - Validez : un **mail de confirmation** est envoyé (visible dans
     `backend/mail/mails.log` en local).
3. **Mon espace** :
   - Liste de vos commandes avec leur statut.
   - Tant qu'une commande n'est pas « acceptée » : **Modifier** (tout sauf le
     menu) ou **Annuler**.
   - Une fois « acceptée » : bouton **Suivi** (historique daté des états).
   - Une fois « terminée » : bouton **Donner mon avis** (note 1–5 + commentaire).
   - Formulaire de **modification des informations personnelles**.

## 5. Parcours EMPLOYÉ

1. Se connecter avec `julie@vite-et-gourmand.fr` / `Employe!Vg2026`.
2. **Espace employé** :
   - **Commandes** : filtrer par statut ou par client ; faire évoluer le statut
     (accepté → en préparation → en livraison → livré → [attente matériel] →
     terminée). Annuler nécessite un **motif + mode de contact** (après avoir
     contacté le client).
   - **Avis à modérer** : valider (apparaît sur l'accueil) ou refuser.
   - **Menus** : créer, modifier, désactiver / réactiver.
   - **Plats** : créer, modifier (avec allergènes), supprimer.
   - **Horaires** : modifier les horaires du pied de page.

> Note métier : passer une commande en « en attente du retour de matériel »
> envoie automatiquement au client le rappel des 600 € (10 jours ouvrés).
> La passer en « terminée » envoie l'invitation à donner un avis.

## 6. Parcours ADMINISTRATEUR

1. Se connecter avec `jose@vite-et-gourmand.fr` / `Admin!Vg2026`.
2. **Espace administrateur** :
   - **Comptes employés** : créer un compte (l'employé reçoit un mail **sans**
     le mot de passe), désactiver / réactiver un compte.
   - **Statistiques** (base MongoDB) : graphique du nombre de commandes par menu
     et tableau du chiffre d'affaires, filtrables par menu et par période.
3. L'administrateur accède aussi à **tout l'espace employé**.

## 7. Démarrer l'application en local

Voir le [README](../README.md) pour l'installation complète. En résumé :

```bash
# Démarrer les bases (MySQL + MongoDB), puis :
php -S localhost:8000 router.php
```

Puis ouvrir http://localhost:8000. Le `router.php` est **obligatoire** (il
protège les fichiers sensibles).
