// Navigation mobile : ouverture/fermeture du menu burger (accessible)
document.addEventListener('DOMContentLoaded', () => {
  const bouton = document.querySelector('.nav__bouton');
  const liste = document.querySelector('.nav__liste');
  if (!bouton || !liste) return;

  bouton.addEventListener('click', () => {
    const ouvert = liste.classList.toggle('est-ouvert');
    bouton.setAttribute('aria-expanded', String(ouvert));
  });
});

// Sur les pages publiques : si une session est déjà ouverte, le lien
// « Connexion » devient « Mon espace » et mène au tableau de bord du rôle.
// (Sans session, l'API répond 401 et le lien reste tel quel.)
document.addEventListener('DOMContentLoaded', async () => {
  const lien = document.querySelector('a.nav__connexion[href$="connexion.html"]');
  if (!lien) return; // page d'espace connecté : le lien est « Déconnexion »

  try {
    const reponse = await fetch(lien.href.replace(/[^/]*$/, '../backend/auth/me.php'));
    if (!reponse.ok) return;
    const { profil } = await reponse.json();

    const espaces = {
      utilisateur: 'espace-utilisateur',
      employe: 'espace-employe',
      administrateur: 'espace-admin',
    };
    if (!espaces[profil.role]) return;
    lien.href = lien.href.replace(/[^/]*$/, `${espaces[profil.role]}/dashboard.html`);
    lien.textContent = 'Mon espace';
  } catch {
    /* hors ligne : on ne change rien */
  }
});
