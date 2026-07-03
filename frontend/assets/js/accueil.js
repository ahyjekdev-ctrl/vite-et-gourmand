// Page d'accueil : charge les avis clients VALIDÉS depuis l'API.
// Si l'API est indisponible, les avis statiques de la page restent affichés.

(() => {
  const zone = document.getElementById('liste-avis');
  if (!zone) return;

  function el(balise, classe, texte) {
    const element = document.createElement(balise);
    if (classe) element.className = classe;
    if (texte !== undefined) element.textContent = texte;
    return element;
  }

  fetch('../backend/avis/get-avis.php')
    .then((reponse) => (reponse.ok ? reponse.json() : Promise.reject(reponse.status)))
    .then(({ avis }) => {
      if (!avis.length) return;
      zone.replaceChildren(
        ...avis.map((entree) => {
          const carte = el('article', 'carte');

          const note = el('p', 'avis__note', '★'.repeat(entree.note) + '☆'.repeat(5 - entree.note));
          note.setAttribute('aria-hidden', 'true');

          const texte = el('p');
          const lecteurEcran = el('span', 'sr-only', `Note : ${entree.note} sur 5. `);
          texte.append(lecteurEcran, document.createTextNode(`« ${entree.description} »`));

          carte.append(note, texte, el('p', 'avis__auteur', `${entree.auteur} — ${entree.ville}`));
          return carte;
        })
      );
    })
    .catch(() => { /* avis statiques conservés */ });
})();
