# 🌿 Conventions du projet — Git, code et sécurité

## 1. Workflow Git

### Branches

```
main                    → production stable (= ce qui est déployé)
└── develop             → branche d'intégration
    ├── feature/xxx     → une branche PAR fonctionnalité
    ├── feature/yyy
    └── fix/zzz         → corrections de bugs
```

### Règles

1. **Une fonctionnalité = une branche** `feature/nom-court` issue de `develop`.
2. La fonctionnalité est développée et **testée** sur sa branche.
3. ⚠️ **RÈGLE ABSOLUE : aucun merge vers `develop` ou `main` sans l'accord explicite d'Alexandre.**
   Quand une branche est prête, on le signale et on **demande la validation avant de merger**.
4. Merge `develop` → `main` uniquement quand `develop` est correctement testée (et toujours après accord).
5. Jamais de commit direct sur `main`. Sur `develop`, uniquement la doc/config si trivial — le code passe par des branches.

### Nommage des branches

| Préfixe | Usage | Exemple |
|---|---|---|
| `feature/` | Nouvelle fonctionnalité | `feature/filtres-menus` |
| `fix/` | Correction de bug | `fix/calcul-livraison` |
| `docs/` | Documentation seule | `docs/manuel-utilisation` |

### Messages de commit

Format : `type: description courte en français`

| Type | Usage |
|---|---|
| `feat` | Nouvelle fonctionnalité |
| `fix` | Correction de bug |
| `docs` | Documentation |
| `style` | CSS / mise en forme (pas de logique) |
| `refactor` | Refactoring sans changement de comportement |
| `db` | Schéma ou données de la base |
| `chore` | Config, outillage, divers |

Exemples : `feat: filtres dynamiques sur la vue globale des menus`, `db: table commande + statuts`.

## 2. Conventions de code

- **Langue** : code et commentaires en français (cohérent avec le sujet et le jury).
- **HTML** : sémantique (`header`, `nav`, `main`, `footer`, `section`…) — requis pour le RGAA.
- **CSS** : un fichier principal `style.css`, mobile-first, variables CSS pour la palette (charte graphique).
- **JS** : vanilla, `fetch` pour les appels API, pas de rechargement de page pour les filtres.
- **PHP** : PDO exclusivement, **requêtes préparées partout**, un endpoint = un fichier, réponses JSON pour l'API.
- **Indentation** : 4 espaces (PHP), 2 espaces (HTML/CSS/JS).

## 3. Sécurité (exigences ECF — à documenter dans le dossier)

### Authentification
- Mots de passe hachés avec `password_hash()` (bcrypt) — **jamais en clair**.
- Politique de mot de passe : **10 caractères min, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial** — validée côté client **et** côté serveur.
- Sessions PHP sécurisées (`httponly`, `secure`, régénération d'ID à la connexion).
- Réinitialisation par **token à usage unique avec expiration**, envoyé par mail.
- Pas de création de compte admin depuis l'application.

### Accès aux données
- **Requêtes préparées PDO** systématiques (anti injection SQL).
- Contrôle des rôles **côté serveur** sur chaque endpoint (jamais de confiance au front).

### Front
- Rendu de toute donnée dynamique via `textContent` / création DOM — **jamais `innerHTML`** (anti XSS).
- Validation des formulaires côté client (UX) **et** côté serveur (sécurité).
- **CSP `default-src 'self'`** sur chaque page (bloque tout script/style/ressource externe) → aucun style inline, aucun script inline.
- Défense CSRF : cookie de session `SameSite=Lax` + `Content-Type: application/json` exigé par l'API.

### Configuration
- Secrets dans `.env` (jamais commité — voir `.gitignore`).
- `.env.example` maintenu à jour à chaque nouvelle variable.
- **Ne jamais servir les fichiers sensibles** : en dev via `router.php` (liste blanche), en prod via `.htaccess`. Lancer le serveur avec `php -S localhost:8000 router.php`.
- `display_errors` désactivé sur l'API : les erreurs vont au journal, jamais au client.
- Détails et vulnérabilités corrigées : [veille-securite.md](veille-securite.md) ; jeux d'essai : [jeux-essai.md](jeux-essai.md).

### RGPD
- Seules les données nécessaires sont collectées.
- Mentions légales + CGV accessibles depuis le footer.
- Consentement explicite à l'inscription.

## 4. Accessibilité (RGAA)

- Attributs `alt` sur toutes les images.
- Labels associés à tous les champs de formulaire.
- Contrastes suffisants (à vérifier dans la charte graphique).
- Navigation clavier possible, ordre de tabulation logique.
- Landmarks ARIA quand le HTML sémantique ne suffit pas.

## 5. Définition de « terminé » (Definition of Done)

Une fonctionnalité est terminée quand :
- [ ] Elle fonctionne en local (parcours testé à la main)
- [ ] Les règles de sécurité ci-dessus sont respectées
- [ ] La ROADMAP est mise à jour
- [ ] La branche est prête à merger → **demander la validation à Alexandre**
