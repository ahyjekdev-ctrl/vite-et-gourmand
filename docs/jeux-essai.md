# 🧪 Jeux d'essai — Vite & Gourmand

> Livrable du dossier projet : « jeu d'essai de la fonctionnalité la plus
> représentative (données en entrée, données attendues, données obtenues)
> et analyse des écarts ». Tous les résultats ci-dessous sont issus de
> tests réels exécutés en local (curl + vérifications SQL/Mongo).

## 1. Fonctionnalité représentative BACK-END : création d'une commande

Le calcul du prix est **toujours refait côté serveur** ([calcul-prix.php](../backend/commandes/calcul-prix.php)) :
prix/personne = prix du menu ÷ minimum ; **−10 %** si nb ≥ minimum + 5 ;
livraison **5 € + 0,59 €/km** hors Bordeaux (offerte à Bordeaux).

### Cas nominaux

| # | Données en entrée | Attendu | Obtenu | Écart |
|---|---|---|---|---|
| B1 | Menu Pâques (180 €/6 pers), 6 pers, Bordeaux | menu 180 € + livraison 0 € = **180 €**, sans réduction | `{"prix_menu":180,"prix_livraison":0,"prix_total":180,"reduction_10":false}` | aucun |
| B2 | Menu Noël (320 €/8 pers), 13 pers, Mérignac 8 km | 13×40 = 520 → −10 % = 468 ; livraison 5 + 0,59×8 = 9,72 → **477,72 €** | `{"prix_menu":468,"prix_livraison":9.72,"prix_total":477.72,"reduction_10":true}` | aucun |
| B3 | Après commande B1 | stock du menu décrémenté (8 → 7), jalon « créé » daté dans `suivi_commande`, mail de confirmation journalisé | stock = 7 ; 1 jalon ; mail présent avec le détail du prix | aucun |

### Cas d'erreur (validation serveur)

| # | Données en entrée | Attendu | Obtenu | Écart |
|---|---|---|---|---|
| B4 | 4 personnes sur un menu à 8 minimum | HTTP 422 | 422 « Ce menu se commande pour 8 personnes minimum. » | aucun |
| B5 | Ville « Pessac » sans distance | HTTP 422 | 422 (champ distance_km) | aucun |
| B6 | Date de prestation passée | HTTP 422 | 422 | aucun |
| B7 | Sans être connecté | HTTP 401 | 401 « Authentification requise. » | aucun |
| B8 | Client A tente d'annuler la commande du client B | HTTP 403 | 403 « Cette commande ne vous appartient pas. » | aucun |
| B9 | Annulation client après acceptation par l'équipe | HTTP 409 | 409 « Commande déjà acceptée… » | aucun |
| B10 | Employé passe « créé » → « livré » directement | HTTP 422 | 422 + liste des statuts possibles (`["accepte"]`) | aucun |
| B11 | Annulation employé sans motif | HTTP 422 | 422 « le motif et le mode de contact sont obligatoires » | aucun |

## 2. Fonctionnalité représentative FRONT-END : filtres dynamiques des menus

Exigence : actualisation **sans rechargement de page** (fetch + reconstruction du DOM).

| # | Données en entrée (filtres) | Attendu | Obtenu | Écart |
|---|---|---|---|---|
| F1 | Aucun filtre | 6 menus | 6 menus | aucun |
| F2 | Prix max = 150 € | Classique (150), Vegan (145), Végétarien (140) | 3 menus, les bons | **écart initial** : la prédiction du test annonçait 2 menus, en oubliant le Vegan à 145 € ≤ 150. L'API avait raison — le jeu d'essai a été corrigé (l'erreur était dans l'attendu, pas dans le code) |
| F3 | Fourchette 160–400 € | Noël (320), Pâques (180) | 2 menus | aucun |
| F4 | Régime vegan | Vegan Découverte uniquement | 1 menu | aucun |
| F5 | Thème=Classique + prix max=142 (cumul) | Végétarien du Marché uniquement | 1 menu | aucun |
| F6 | 10 convives | 5 menus (le Grand Format à 20 min. exclu) | 5 menus | aucun |
| F7 | Calcul en direct page commande : 11 pers sur menu à 6 min (30 €/pers) | 330 − 10 % = **297 €** affiché avant validation | 297 € affiché, confirmé par le serveur à l'envoi | aucun |

## 3. Statistiques NoSQL (MongoDB)

| # | Entrée | Attendu | Obtenu | Écart |
|---|---|---|---|---|
| N1 | Agrégation globale sur les fixtures | 6 commandes, CA 1 848,09 € | 6 / 1 848,09 € | aucun |
| N2 | Filtre période juin 2026 | 5 commandes (celle de juillet exclue) | 5 | aucun |
| N3 | Création puis annulation d'une commande | document Mongo créé, statut suivi à chaque transition, exclu des stats après annulation | conforme à chaque étape | aucun |

## 4. Écarts réels rencontrés et corrigés (analyse)

Ces trois anomalies ont été **détectées par les jeux d'essai** — c'est exactement leur rôle :

1. **Validateur MongoDB trop strict** — `prix_total` déclaré `bsonType: "double"` :
   un montant rond (468,00) est sérialisé en `Int32` par mongosh/PHP → insertion
   rejetée (`Document failed validation`, 0 document inséré).
   **Correction** : `bsonType: "number"` (accepte int, long, double, decimal).
2. **Paramètre nommé PDO réutilisé** — `…nom LIKE :client OR prenom LIKE :client…` :
   avec les requêtes préparées **réelles** (`EMULATE_PREPARES` désactivé), MySQL
   refuse la réutilisation d'un même paramètre nommé → erreur 500 sur le filtre
   client de l'espace employé.
   **Correction** : trois paramètres distincts (`:client_nom`, `:client_prenom`, `:client_email`).
3. **Exposition de fichiers sensibles** (voir [veille-securite.md](veille-securite.md) §3) :
   `.env`, `mails.log` et les fichiers SQL étaient servis par le serveur web.
   **Correction** : routeur en liste blanche (`router.php`) + `.htaccess`.
