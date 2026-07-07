// Authentification côté client : connexion, inscription, mot de passe oublié.
// La validation côté client améliore l'expérience ; la validation qui fait foi
// est TOUJOURS celle du serveur.

/** Appelle l'API en JSON et retourne { ok, statut, donnees }. */
async function appelerApi(chemin, corps) {
  const reponse = await fetch(chemin, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify(corps),
  });
  let donnees = {};
  try { donnees = await reponse.json(); } catch { /* réponse sans corps */ }
  return { ok: reponse.ok, statut: reponse.status, donnees };
}

/** Affiche un message (erreur ou succès) dans la zone d'alerte de la page. */
function afficherMessage(texte, estErreur) {
  const zone = document.getElementById('message-auth');
  if (!zone) return;
  zone.textContent = texte;
  zone.className = estErreur ? 'message-erreur' : 'message-succes';
  zone.hidden = !texte;
}

/** Politique de mot de passe du cahier des charges (miroir du serveur). */
function motDePasseValide(mdp) {
  return mdp.length >= 10
    && /[A-Z]/.test(mdp)
    && /[a-z]/.test(mdp)
    && /[0-9]/.test(mdp)
    && /[^A-Za-z0-9]/.test(mdp);
}

/** Redirige vers l'espace correspondant au rôle après connexion. */
function redirigerSelonRole(role) {
  const destinations = {
    utilisateur: 'espace-utilisateur/dashboard.html',
    employe: 'espace-employe/dashboard.html',
    administrateur: 'espace-admin/dashboard.html',
  };
  location.href = destinations[role] || 'index.html';
}

document.addEventListener('DOMContentLoaded', () => {
  /* ---------- Connexion ---------- */
  const formConnexion = document.getElementById('form-connexion');
  if (formConnexion) {
    formConnexion.addEventListener('submit', async (evenement) => {
      evenement.preventDefault();
      const email = document.getElementById('con-email').value.trim();
      const password = document.getElementById('con-password').value;

      if (!email || !password) {
        afficherMessage('Renseignez votre mail et votre mot de passe.', true);
        return;
      }

      const { ok, donnees } = await appelerApi('../backend/auth/login.php', { email, password });
      if (ok) {
        redirigerSelonRole(donnees.utilisateur.role);
      } else {
        afficherMessage(donnees.erreur || 'Connexion impossible.', true);
      }
    });
  }

  /* ---------- Mot de passe oublié : demande de lien ---------- */
  const boutonReset = document.getElementById('btn-reset');
  if (boutonReset) {
    boutonReset.addEventListener('click', async () => {
      const email = document.getElementById('con-email-reset').value.trim();
      if (!email) {
        afficherMessage('Indiquez votre adresse mail pour recevoir le lien.', true);
        return;
      }
      const { ok, donnees } = await appelerApi('../backend/auth/reset-password.php', { action: 'demander', email });
      afficherMessage(donnees.message || donnees.erreur || 'Demande envoyée.', !ok);
    });
  }

  /* ---------- Mot de passe oublié : nouveau mot de passe (lien du mail) ---------- */
  const sectionReset = document.getElementById('section-reset');
  const token = new URLSearchParams(location.search).get('token');
  if (sectionReset && token) {
    sectionReset.hidden = false;
    document.getElementById('form-reset').addEventListener('submit', async (evenement) => {
      evenement.preventDefault();
      const password = document.getElementById('rst-password').value;
      const confirmation = document.getElementById('rst-password2').value;

      if (!motDePasseValide(password)) {
        afficherMessage('Mot de passe trop faible : 10 caractères minimum, avec majuscule, minuscule, chiffre et caractère spécial.', true);
        return;
      }
      if (password !== confirmation) {
        afficherMessage('La confirmation ne correspond pas au mot de passe.', true);
        return;
      }

      const { ok, donnees } = await appelerApi('../backend/auth/reset-password.php', {
        action: 'reinitialiser',
        token,
        password,
        password_confirmation: confirmation,
      });
      afficherMessage(donnees.message || donnees.erreur || 'Erreur inattendue.', !ok);
      if (ok) {
        sectionReset.hidden = true;
        history.replaceState(null, '', 'connexion.html'); // retire le token de l'URL
      }
    });
  }

  /* ---------- Inscription ---------- */
  const formInscription = document.getElementById('form-inscription');
  if (formInscription) {
    formInscription.addEventListener('submit', async (evenement) => {
      evenement.preventDefault();

      const valeur = (id) => document.getElementById(id).value.trim();
      const password = document.getElementById('ins-password').value;
      const confirmation = document.getElementById('ins-password2').value;

      if (!motDePasseValide(password)) {
        afficherMessage('Mot de passe trop faible : 10 caractères minimum, avec majuscule, minuscule, chiffre et caractère spécial.', true);
        return;
      }
      if (password !== confirmation) {
        afficherMessage('La confirmation ne correspond pas au mot de passe.', true);
        return;
      }

      const { ok, donnees } = await appelerApi('../backend/auth/register.php', {
        nom: valeur('ins-nom'),
        prenom: valeur('ins-prenom'),
        telephone: valeur('ins-gsm'),
        email: valeur('ins-email'),
        adresse: valeur('ins-adresse'),
        ville: valeur('ins-ville'),
        code_postal: valeur('ins-cp'),
        password,
        password_confirmation: confirmation,
        consentement: formInscription.elements.consentement.checked,
      });

      if (ok) {
        afficherMessage(donnees.message + ' Redirection vers la connexion…', false);
        setTimeout(() => { location.href = 'connexion.html'; }, 2000);
      } else if (donnees.champs) {
        afficherMessage(Object.values(donnees.champs).join(' '), true);
      } else {
        afficherMessage(donnees.erreur || 'Inscription impossible.', true);
      }
    });
  }
});
