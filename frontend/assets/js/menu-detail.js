// Vue détaillée d'un menu : charge toutes les informations depuis l'API
// selon l'identifiant présent dans l'URL (menu-detail.html?id=X).

(() => {
  const parametres = new URLSearchParams(location.search);
  const id = Number(parametres.get('id')) || 1;

  const TYPES = { entree: 'Entrées', plat: 'Plats', dessert: 'Desserts' };

  function el(balise, classe, texte) {
    const element = document.createElement(balise);
    if (classe) element.className = classe;
    if (texte !== undefined) element.textContent = texte;
    return element;
  }

  async function chargerMenu() {
    let reponse;
    try {
      reponse = await fetch(`../backend/menus/get-menu.php?id=${id}`);
    } catch {
      return; // hors ligne : la page statique reste affichée
    }
    if (!reponse.ok) {
      document.getElementById('titre-menu').textContent = 'Menu introuvable';
      document.getElementById('detail-description').textContent =
        'Ce menu n\'existe pas ou n\'est plus proposé. Retournez à la liste des menus.';
      return;
    }
    const { menu } = await reponse.json();

    /* ---------- En-tête ---------- */
    document.title = `${menu.titre} — Vite & Gourmand`;
    document.getElementById('titre-menu').textContent = menu.titre;
    document.getElementById('fil-ariane-menu').textContent = menu.titre;
    document.getElementById('badge-theme').textContent = `Thème : ${menu.theme}`;
    document.getElementById('badge-regime').textContent = `Régime : ${menu.regime}`;
    document.getElementById('detail-description').textContent = menu.description;

    /* ---------- Prix, réduction, stock ---------- */
    document.getElementById('detail-prix').textContent =
      `${Number(menu.prix_min).toLocaleString('fr-FR')} €`;
    document.getElementById('detail-personnes').textContent =
      ` pour ${menu.nb_personnes_min} personnes (minimum)`;
    document.getElementById('detail-reduction').textContent =
      `−10 % à partir de ${menu.seuil_reduction} personnes`;
    const stock = document.getElementById('detail-stock');
    if (Number(menu.quantite_restante) > 0) {
      stock.textContent = `⏳ Plus que ${menu.quantite_restante} commande${menu.quantite_restante > 1 ? 's' : ''} disponible${menu.quantite_restante > 1 ? 's' : ''}`;
    } else {
      stock.textContent = '❌ Ce menu est épuisé pour le moment.';
    }

    /* ---------- Conditions (mise en évidence obligatoire) ---------- */
    const listeConditions = document.getElementById('detail-conditions');
    listeConditions.replaceChildren(
      ...menu.conditions.split(/\.\s+/).filter(Boolean).map((phrase) =>
        el('li', null, phrase.endsWith('.') ? phrase : `${phrase}.`))
    );

    /* ---------- Galerie ---------- */
    const galeriePrincipale = document.getElementById('galerie-principale');
    if (menu.images[0]) galeriePrincipale.setAttribute('aria-label', `${menu.images[0].alt} (photo à venir)`);
    const vignettes = document.getElementById('galerie-vignettes');
    vignettes.replaceChildren(
      ...menu.images.slice(1).map((image) => {
        const visuel = el('div', 'visuel');
        visuel.setAttribute('role', 'img');
        visuel.setAttribute('aria-label', `${image.alt} (photo à venir)`);
        return visuel;
      })
    );

    /* ---------- Composition par type de plat ---------- */
    const composition = document.getElementById('detail-composition');
    const groupes = { entree: [], plat: [], dessert: [] };
    menu.plats.forEach((plat) => groupes[plat.type]?.push(plat));

    composition.replaceChildren(
      ...Object.entries(groupes)
        .filter(([, plats]) => plats.length > 0)
        .map(([type, plats]) => {
          const carte = el('article', 'carte');
          carte.append(el('h3', null, TYPES[type]));
          const listePlats = el('ul');
          plats.forEach((plat) => {
            const item = el('li');
            item.append(el('span', 'plat__titre', plat.titre));
            plat.allergenes.forEach((allergene) => {
              item.append(el('span', 'pastille', allergene), document.createTextNode(' '));
            });
            listePlats.append(item);
          });
          carte.append(listePlats);
          return carte;
        })
    );

    /* ---------- Bouton commander : menu pré-rempli sur la page de commande ---------- */
    const boutonCommander = document.getElementById('btn-commander');
    boutonCommander.href = `commande.html?menu=${menu.menu_id}`;
    if (Number(menu.quantite_restante) === 0) {
      boutonCommander.setAttribute('aria-disabled', 'true');
      boutonCommander.classList.remove('btn--primaire');
      boutonCommander.classList.add('btn--secondaire');
      boutonCommander.textContent = 'Menu épuisé';
      boutonCommander.removeAttribute('href');
    }
  }

  chargerMenu();
})();
