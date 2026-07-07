# 🔒 Veille sécurité — Vite & Gourmand

> Livrable du dossier projet : « description de la veille sur les vulnérabilités
> de sécurité, des vulnérabilités trouvées et des failles corrigées ».
> Référentiel de veille : **OWASP Top 10** (2021) et **RGAA 4.1** pour l'accessibilité.

## 1. Sources de veille suivies

| Source | Usage |
|---|---|
| [OWASP Top 10](https://owasp.org/Top10/) | Grille de lecture des risques applicatifs |
| [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/) | Bonnes pratiques concrètes (mots de passe, sessions, CSP) |
| [MDN Web Docs — Web Security](https://developer.mozilla.org/fr/docs/Web/Security) | En-têtes HTTP, CSP, cookies |
| [Snyk Vulnerability DB](https://security.snyk.io/) | Suivi des CVE (peu de dépendances ici : PHP vanilla) |
| [CNIL](https://www.cnil.fr/) | Conformité RGPD |

## 2. Mesures en place, par risque OWASP

| Risque OWASP | Mesure dans l'application |
|---|---|
| **A01 Contrôle d'accès défaillant** | `exigerRole()` côté serveur sur chaque endpoint sensible ; un client ne voit/modifie que ses commandes ; pas de création d'admin possible depuis l'app |
| **A02 Défaillances cryptographiques** | Mots de passe hachés **bcrypt** (`password_hash`) ; token de réinitialisation via `random_bytes` (64 hex), à usage unique, expiration 1 h |
| **A03 Injection** | **Requêtes préparées PDO réelles** partout (`EMULATE_PREPARES` désactivé) ; aucune concaténation de variable en SQL ; rendu front en `textContent` (jamais `innerHTML`) |
| **A04 Conception non sécurisée** | Prix recalculé côté serveur (le front n'est pas de confiance) ; verrou `FOR UPDATE` anti-survente ; transitions de statut en liste blanche |
| **A05 Mauvaise configuration** | `display_errors` désactivé (les erreurs ne fuitent pas au client) ; en-têtes `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Content-Security-Policy` ; fichiers sensibles non servis (routeur + `.htaccess`) |
| **A07 Identification/authentification** | Message de connexion unique (anti-énumération) ; `session_regenerate_id` à la connexion ; cookie `httponly` + `SameSite=Lax` + `secure` en HTTPS ; délai anti-force-brute |
| **A08 Intégrité des données** | Corps `application/json` exigé (`Content-Type` 415 sinon) — défense CSRF supplémentaire |
| **A09 Journalisation** | Erreurs serveur écrites dans le journal (`error_log`), jamais renvoyées ; mails journalisés en dev |
| **A10 SSRF** | Aucune requête serveur construite à partir d'une URL fournie par l'utilisateur |

## 3. Vulnérabilités trouvées et corrigées pendant le projet

### 3.1 🔴 Exposition de fichiers sensibles (critique)

**Trouvée le 08/07** pendant la passe sécurité. Le serveur de développement
`php -S` sert **l'intégralité du dépôt** : une simple requête `GET /.env`
renvoyait les identifiants de la base ; `GET /backend/mail/mails.log` exposait
les tokens de réinitialisation de mot de passe ; `GET /database/fixtures.sql`
les hashs.

**Preuve (avant correction)** : `HTTP 200` sur `/.env`, `/backend/mail/mails.log`,
`/database/schema.sql`, `/database/fixtures.sql`.

**Correction** :
- `router.php` (dev) — **liste blanche** : seuls `/frontend/**` et les endpoints
  `/backend/<module>/<script>.php` sont servis ; tout le reste → 404.
- `.htaccess` (production Apache) — mêmes règles + en-têtes de sécurité.
- **Vérifié après** : `HTTP 404` sur tous les chemins sensibles, application intacte.

> ⚠ Rappel d'exploitation : toujours lancer le serveur avec
> `php -S localhost:8000 router.php` (documenté dans le README).

### 3.2 🟠 Réutilisation d'un paramètre préparé (moyenne — robustesse)

Le filtre client de l'espace employé réutilisait le paramètre `:client` trois
fois ; avec les requêtes préparées réelles, MySQL renvoie une erreur → 500.
Corrigé en trois paramètres distincts. (Détail dans [jeux-essai.md](jeux-essai.md) §4.)

### 3.3 🟡 Durcissements préventifs (défense en profondeur)

- Ajout du délai anti-force-brute sur la connexion.
- `Content-Type: application/json` exigé sur l'API.
- CSP `default-src 'self'` sur les 12 pages → tout script/style/ressource
  externe est bloqué (limite fortement l'impact d'une éventuelle injection).
- Suppression de tous les styles inline et du dernier `innerHTML` pour rester
  compatible avec une CSP stricte (sans `unsafe-inline`).

## 4. Accessibilité (RGAA 4.1) — points vérifiés

Audit automatisé sur les 12 pages : `lang="fr"`, `<title>` unique, lien
d'évitement, `<main>` identifié, navigation avec `aria-label`, un seul `<h1>`,
**chaque champ de formulaire associé à un `<label>`** (0 champ orphelin).
Complété par : contrastes conformes (charte graphique), focus visible,
`aria-live` sur le compteur de résultats, textes alternatifs sur les visuels,
`aria-current` sur la page active.

## 5. Points de vigilance pour la suite

- **Rate limiting** plus robuste (par IP, stockage persistant) en production.
- **HTTPS obligatoire** au déploiement (le cookie `secure` en dépend).
- **Jeton CSRF** explicite si des formulaires HTML non-JSON étaient ajoutés.
- Rotation régulière de la veille CVE une fois des dépendances ajoutées.
