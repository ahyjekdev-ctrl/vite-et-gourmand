// Vue globale des menus : filtres dynamiques SANS rechargement de page
// (exigence du cahier des charges). À chaque saisie, la liste est
// redemandée à l'API et le DOM est reconstruit.
//
// Sécurité XSS : tout contenu venant de l'API est injecté via textContent
// (jamais innerHTML avec des données), le HTML ne peut pas être interprété.

(() => {
  const liste = document.getElementById('liste-menus');
  const compteur = document.getElementById('compteur-resultats');
  const formulaire = document.getElementById('form-filtres');
  if (!liste || !formulaire) return;

  /* ---------- Construction d'une carte menu (DOM sûr) ---------- */

  function el(balise, classe, texte) {
    const element = document.createElement(balise);
    if (classe) element.className = classe;
    if (texte !== undefined) element.textContent = texte;
    return element;
  }

  function carteMenu(menu) {
    const item = el('li');
    const article = el('article', 'carte carte--menu');

    // Photo du menu si disponible, sinon visuel de substitution
    let visuel;
    if (menu.image) {
      visuel = el('img', 'photo-plat');
      visuel.src = menu.image;
      visuel.alt = menu.image_alt || menu.titre;
    } else {
      visuel = el('div', 'visuel visuel--assiette');
      visuel.setAttribute('role', 'img');
      visuel.setAttribute('aria-label', `Photo du ${menu.titre} (photo à venir)`);
    }

    const corps = el('div', 'carte__corps');
    corps.append(
      el('h3', null, menu.titre),
      el('p', 'carte__desc', menu.description),
    );

    const meta = el('p', 'carte__meta');
    meta.append(
      el('span', 'badge', `≥ ${menu.nb_personnes_min} pers.`),
      el('span', 'prix', `${Number(menu.prix_min).toLocaleString('fr-FR')} €`),
    );
    corps.append(meta);

    const lien = el('a', 'btn btn--primaire btn--large', 'Voir le détail');
    lien.href = `menu-detail.html?id=${menu.menu_id}`;
    const sr = el('span', 'sr-only', ` du ${menu.titre}`);
    lien.append(sr);
    corps.append(lien);

    article.append(visuel, corps);
    item.append(article);
    return item;
  }

  /* ---------- Chargement des menus selon les filtres ---------- */

  function valeursFiltres() {
    const parametres = new URLSearchParams();
    const valeur = (id) => document.getElementById(id)?.value.trim() ?? '';

    // Le curseur « prix max » ne filtre que s'il a été déplacé
    const curseur = document.getElementById('filtre-prix-max');
    if (curseur && curseur.value !== curseur.max) parametres.set('prix_max', curseur.value);

    if (valeur('filtre-prix-min')) parametres.set('prix_de', valeur('filtre-prix-min'));
    if (valeur('filtre-prix-fourchette-max')) parametres.set('prix_a', valeur('filtre-prix-fourchette-max'));
    if (valeur('filtre-theme')) parametres.set('theme', valeur('filtre-theme'));
    if (valeur('filtre-regime')) parametres.set('regime', valeur('filtre-regime'));
    if (valeur('filtre-personnes')) parametres.set('personnes', valeur('filtre-personnes'));
    return parametres;
  }

  async function chargerMenus() {
    try {
      const reponse = await fetch(`../backend/menus/get-menus.php?${valeursFiltres()}`);
      if (!reponse.ok) throw new Error(`HTTP ${reponse.status}`);
      const { total, menus } = await reponse.json();

      liste.replaceChildren(...menus.map(carteMenu));
      if (compteur) {
        compteur.textContent = total === 0
          ? 'Aucun menu ne correspond à ces critères — élargissez vos filtres.'
          : `${total} menu${total > 1 ? 's' : ''} disponible${total > 1 ? 's' : ''}`;
      }
    } catch (erreur) {
      if (compteur) compteur.textContent = 'Impossible de charger les menus. Réessayez plus tard.';
      console.error('Chargement des menus impossible :', erreur);
    }
  }

  /* ---------- Listes déroulantes alimentées depuis la base ---------- */

  async function chargerReferentiels() {
    try {
      const reponse = await fetch('../backend/menus/get-filtres.php');
      if (!reponse.ok) return;
      const { themes, regimes } = await reponse.json();

      const remplir = (select, lignes, cleId) => {
        const premierChoix = select.options[0];
        select.replaceChildren(premierChoix);
        lignes.forEach((ligne) => {
          const option = document.createElement('option');
          option.value = ligne[cleId];
          option.textContent = ligne.libelle;
          select.append(option);
        });
      };
      remplir(document.getElementById('filtre-theme'), themes, 'theme_id');
      remplir(document.getElementById('filtre-regime'), regimes, 'regime_id');
    } catch (erreur) {
      console.error('Chargement des référentiels impossible :', erreur);
    }
  }

  /* ---------- Écouteurs : actualisation à chaque changement ---------- */

  let minuteur = null;
  function actualiserAvecDelai() {
    clearTimeout(minuteur);
    minuteur = setTimeout(chargerMenus, 300); // attend la fin de la saisie
  }

  formulaire.addEventListener('input', (evenement) => {
    // Affiche la valeur du curseur en direct
    if (evenement.target.id === 'filtre-prix-max') {
      const sortie = document.getElementById('sortie-prix-max');
      if (sortie) sortie.textContent = `${evenement.target.value} €`;
    }
    actualiserAvecDelai();
  });

  formulaire.addEventListener('reset', () => {
    setTimeout(() => {
      const sortie = document.getElementById('sortie-prix-max');
      if (sortie) sortie.textContent = '600 €';
      chargerMenus();
    }, 0);
  });

  formulaire.addEventListener('submit', (evenement) => evenement.preventDefault());

  /* ---------- Initialisation ---------- */
  chargerReferentiels();
  chargerMenus();
})();
