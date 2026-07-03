# 📐 Diagrammes UML — Vite & Gourmand

> Diagrammes en Mermaid (rendus nativement par GitHub). Pour le dossier projet, ils seront exportés en image (clic droit → export, ou [mermaid.live](https://mermaid.live)).

## 1. Diagramme de cas d'utilisation

> Mermaid n'a pas de type « use case » natif : représentation en graphe acteurs → cas d'utilisation, avec héritage entre acteurs (l'Utilisateur peut tout ce que fait le Visiteur, l'Administrateur tout ce que fait l'Employé).

```mermaid
flowchart LR
    V(["👤 Visiteur"])
    U(["👤 Utilisateur<br/>(client authentifié)"])
    E(["👤 Employé"])
    A(["👤 Administrateur"])

    U -. hérite de .-> V
    A -. hérite de .-> E

    subgraph S["Application Vite & Gourmand"]
        UC1(["Consulter et filtrer les menus"])
        UC2(["Voir le détail d'un menu"])
        UC3(["Contacter l'entreprise"])
        UC4(["Créer un compte"])
        UC5(["Se connecter / réinitialiser son mot de passe"])
        UC6(["Commander un menu"])
        UC7(["Suivre / modifier / annuler ses commandes"])
        UC8(["Donner un avis (commande terminée)"])
        UC9(["Modifier ses informations personnelles"])
        UC10(["Gérer menus, plats et horaires"])
        UC11(["Gérer les commandes (statuts, motifs)"])
        UC12(["Valider / refuser les avis"])
        UC13(["Créer / désactiver un compte employé"])
        UC14(["Consulter statistiques et CA (MongoDB)"])
    end

    V --> UC1
    V --> UC2
    V --> UC3
    V --> UC4
    V --> UC5

    U --> UC6
    U --> UC7
    U --> UC8
    U --> UC9

    E --> UC10
    E --> UC11
    E --> UC12

    A --> UC13
    A --> UC14

    UC6 -. «include» : authentification .-> UC5
    UC8 -. «include» : commande terminée .-> UC7
```

## 2. Diagramme de séquence — Commander un menu

> Parcours le plus représentatif du projet (côté front **et** back) : calcul de prix dynamique, règles métier, mail automatique.

```mermaid
sequenceDiagram
    actor C as Client (authentifié)
    participant F as Front (commande.html + commande.js)
    participant API as Back-end PHP
    participant BDD as MySQL (PDO)
    participant M as Service Mail
    participant NoSQL as MongoDB

    C->>F: Clic « Commander » (depuis le détail du menu)
    F->>API: GET get-menu.php?id=X
    API->>BDD: SELECT menu, conditions, prix, nb_pers_min, stock
    BDD-->>API: données menu
    API-->>F: JSON menu (pré-remplissage)
    F->>F: Auto-remplissage nom, prénom, mail, GSM (session)

    C->>F: Saisit adresse, date, heure, nb de personnes
    F->>F: Calcul dynamique du prix (JS)<br/>+ réduction 10 % si nb ≥ min + 5<br/>+ livraison 5 € + 0,59 €/km hors Bordeaux
    F-->>C: Affiche le détail du prix (menu + livraison)

    C->>F: Valide la commande
    F->>API: POST create-commande.php (JSON)
    API->>API: Vérifie session + rôle utilisateur
    API->>BDD: Vérifie stock > 0 et nb ≥ nb_pers_min
    API->>API: RECALCULE le prix côté serveur<br/>(jamais de confiance au front)
    API->>BDD: INSERT commande + INSERT suivi (statut initial)<br/>UPDATE stock du menu (transaction)
    API->>NoSQL: Insère l'entrée statistique
    API->>M: Mail de confirmation au client
    M-->>C: 📧 Confirmation de commande
    API-->>F: 201 Created + numéro de commande
    F-->>C: Redirection espace utilisateur (récapitulatif)
```

## 3. Diagramme de séquence — Cycle de vie côté employé (complément)

```mermaid
sequenceDiagram
    actor E as Employé
    participant API as Back-end PHP
    participant BDD as MySQL
    participant M as Service Mail
    actor C as Client

    E->>API: Passe la commande à « accepté »
    API->>BDD: UPDATE statut + INSERT suivi (date/heure)
    Note over C: Le client ne peut plus modifier/annuler
    E->>API: … « en préparation » → « en cours de livraison » → « livré »
    API->>BDD: INSERT suivi à chaque étape
    alt Matériel prêté
        E->>API: Statut « en attente du retour de matériel »
        API->>M: Mail : restitution sous 10 jours ouvrés sinon 600 €
        M-->>C: 📧 Rappel matériel
    end
    E->>API: Statut « terminée »
    API->>M: Mail : invitation à donner un avis
    M-->>C: 📧 Donnez votre avis
```

## 4. Schéma d'enchaînement des écrans

```mermaid
flowchart TD
    A[Accueil] --> B[Vue globale des menus]
    A --> K[Contact]
    A --> L[Connexion]
    B -->|Voir le détail| C[Détail d'un menu]
    C -->|Commander — authentifié| D[Page de commande]
    C -->|Commander — visiteur| L
    L --> M[Inscription]
    L -->|mot de passe oublié| N[Réinitialisation par mail]
    L -->|selon rôle| E[Espace utilisateur]
    L -->|selon rôle| F[Espace employé]
    L -->|selon rôle| G[Espace administrateur]
    D -->|validation| E
    E --> H[Suivi de commande]
    H -->|commande terminée| I[Dépôt d'un avis]
    A --> O[Mentions légales / CGV]
```
