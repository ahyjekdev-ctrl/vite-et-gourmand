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
