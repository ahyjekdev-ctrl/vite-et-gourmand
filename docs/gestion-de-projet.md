# 📊 Gestion de projet — Vite & Gourmand

## 1. Choix de l'outil : Trello

**Outil retenu : [Trello](https://trello.com)** (livrable ECF : lien vers le logiciel de gestion de projet).

| Critère | Trello | Notion | Jira |
|---|---|---|---|
| Prise en main | ✅ Immédiate | Moyenne | Complexe |
| Kanban natif | ✅ | ✅ | ✅ |
| Gratuit (usage solo) | ✅ | ✅ | ✅ mais lourd |
| Lien public partageable au jury | ✅ En un clic | ✅ | ⚠️ Plus délicat |
| Adapté à un projet solo de cette taille | ✅ | ⚠️ Sur-outillé | ❌ Sur-outillé |

**Justification** : projet mené en solo sur quelques semaines, avec un besoin simple — visualiser l'avancement par fonctionnalité et le montrer au jury. Trello offre un kanban immédiat et un lien public ; Jira est dimensionné pour des équipes avec sprints et rapports, Notion demande de construire soi-même la structure.

## 2. Méthode : kanban adossé au workflow Git

Chaque **carte = une fonctionnalité = une branche `feature/xxx`** (cohérence avec [conventions.md](conventions.md)). La ROADMAP reste la source de vérité détaillée ; Trello donne la vue d'avancement synthétique.

### Colonnes du board

| Colonne | Signification |
|---|---|
| 📋 **Backlog** | Toutes les fonctionnalités identifiées, non priorisées |
| 🎯 **À faire** | Priorisées pour l'itération en cours |
| 🔨 **En cours** | En développement (branche ouverte) — limite : 1-2 cartes max |
| 👀 **À valider** | Développé et testé, **en attente de validation avant merge** |
| ✅ **Terminé** | Mergé dans `develop` |

### Labels

- `conception` (violet) · `front` (orange) · `back` (rouge) · `bdd` (vert) · `docs` (bleu) · `déploiement` (noir)

## 3. Cartes à créer (reprise des phases de la ROADMAP)

**Backlog initial** — copier ces titres dans Trello :

1. ~~Documentation de pilotage~~ → *Terminé*
2. ~~Conception : charte graphique, maquettes, MCD, diagrammes~~ → *À valider*
3. BDD : schéma MySQL + fixtures + config MongoDB
4. Front statique : layout commun (header/footer) + accueil
5. Front statique : pages menus, détail, commande
6. Front statique : connexion, inscription, contact, mentions/CGV
7. Back : socle PDO + authentification complète
8. Back : API menus + filtres dynamiques
9. Back : commandes (prix, réduction, livraison, statuts, mails)
10. Avis + espaces utilisateur/employé/admin
11. Statistiques MongoDB + graphique CA
12. Page contact + envoi mail
13. Passe sécurité + accessibilité RGAA + jeux d'essai
14. Déploiement + documentation
15. Livrables PDF : manuel, charte graphique, dossier projet

## 4. Rituel de travail

1. Début de session : consulter la **ROADMAP** + le board → choisir la carte prioritaire.
2. Créer la branche `feature/xxx`, déplacer la carte en **En cours**.
3. Développer, tester, commiter (messages au format `type: description`).
4. Push de la branche → carte en **À valider** → **demande de validation avant merge**.
5. Après merge dans `develop` : carte en **Terminé**, mise à jour de la ROADMAP (cases cochées + section « Où on en est »).

## 5. Actions à faire (une seule fois)

- [ ] Créer le board Trello « Vite & Gourmand — ECF » avec les 5 colonnes ci-dessus
- [ ] Créer les 15 cartes du backlog initial + labels
- [ ] Passer le board en visibilité **publique** (lien pour le jury)
- [ ] Reporter le lien du board dans le README (section « Liens du projet »)
