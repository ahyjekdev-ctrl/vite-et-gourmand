// Page de commande :
//  - accès réservé aux personnes connectées (sinon redirection vers la connexion)
//  - coordonnées pré-remplies depuis le compte
//  - menu pré-sélectionné si arrivée depuis une fiche menu (?menu=X)
//  - prix mis à jour en direct (mêmes règles que le serveur, qui reste seul juge)

(() => {
  const formulaire = document.getElementById('form-commande');
  if (!formulaire) return;

  const REGLES = { forfaitLivraison: 5, parKm: 0.59, seuilReduction: 5, tauxReduction: 0.10 };

  const champ = (id) => document.getElementById(id);
  const enEuros = (montant) =>
    montant.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
  const estBordeaux = () => champ('cmd-ville').value.trim().toLowerCase() === 'bordeaux';

  function afficherMessage(texte, estErreur) {
    const zone = champ('message-commande');
    zone.textContent = texte;
    zone.className = estErreur ? 'message-erreur' : 'message-succes';
    zone.hidden = !texte;
    zone.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  /* ---------- 1. Personne connectée ? Pré-remplissage des coordonnées ---------- */

  async function chargerProfil() {
    const reponse = await fetch('../backend/auth/me.php');
    if (reponse.status === 401) {
      // Visiteur non authentifié : connexion (ou création de compte) d'abord
      location.href = 'connexion.html';
      return null;
    }
    const { profil } = await reponse.json();
    champ('cmd-nom').value = profil.nom;
    champ('cmd-prenom').value = profil.prenom;
    champ('cmd-email').value = profil.email;
    champ('cmd-gsm').value = profil.telephone;
    // Coordonnées issues du compte : modifiables depuis l'espace utilisateur
    ['cmd-nom', 'cmd-prenom', 'cmd-email', 'cmd-gsm'].forEach((id) => {
      champ(id).readOnly = true;
    });
    return profil;
  }

  /* ---------- 2. Menus proposés (depuis l'API) + pré-sélection ---------- */

  async function chargerMenus() {
    const reponse = await fetch('../backend/menus/get-menus.php');
    const { menus } = await reponse.json();
    const select = champ('cmd-menu');

    select.replaceChildren(new Option('— Choisir un menu —', ''));
    menus.forEach((menu) => {
      const option = new Option(
        `${menu.titre} — ${enEuros(Number(menu.prix_min))} (${menu.nb_personnes_min} pers. min)`,
        menu.menu_id
      );
      option.dataset.prix = menu.prix_min;
      option.dataset.min = menu.nb_personnes_min;
      option.disabled = Number(menu.quantite_restante) === 0;
      select.append(option);
    });

    // Menu pré-rempli si on vient du bouton « Commander » d'une fiche
    const preSelection = new URLSearchParams(location.search).get('menu');
    if (preSelection && select.querySelector(`option[value="${CSS.escape(preSelection)}"]`)) {
      select.value = preSelection;
    }
    appliquerMinimum();
  }

  function menuChoisi() {
    const option = champ('cmd-menu').selectedOptions[0];
    if (!option || !option.value) return null;
    return { prix: Number(option.dataset.prix), min: Number(option.dataset.min) };
  }

  function appliquerMinimum() {
    const menu = menuChoisi();
    const personnes = champ('cmd-personnes');
    if (!menu) return;
    personnes.min = menu.min;
    if (Number(personnes.value) < menu.min) personnes.value = menu.min;
    document.getElementById('aide-personnes').innerHTML =
      `Minimum pour ce menu&nbsp;: ${menu.min} personnes. <strong>−10&nbsp;% dès ${menu.min + REGLES.seuilReduction} personnes.</strong>`;
    calculer();
  }

  /* ---------- 3. Calcul du prix en direct ---------- */

  function calculer() {
    const menu = menuChoisi();
    if (!menu) return;

    const personnes = Math.max(Number(champ('cmd-personnes').value) || menu.min, menu.min);
    const prixParPersonne = menu.prix / menu.min;
    const brut = prixParPersonne * personnes;
    const reduction = personnes >= menu.min + REGLES.seuilReduction ? brut * REGLES.tauxReduction : 0;

    const kilometres = Number(champ('cmd-distance').value) || 0;
    const livraison = estBordeaux() ? 0 : REGLES.forfaitLivraison + REGLES.parKm * kilometres;

    document.getElementById('recap-libelle-menu').textContent =
      `Menu (${personnes} personnes × ${enEuros(prixParPersonne)})`;
    document.getElementById('recap-menu').textContent = enEuros(brut);
    document.getElementById('recap-reduction').textContent = reduction ? `− ${enEuros(reduction)}` : '0,00 €';
    document.getElementById('recap-livraison').textContent =
      estBordeaux() ? 'Offerte (Bordeaux)' : enEuros(livraison);
    document.getElementById('recap-total').textContent = enEuros(brut - reduction + livraison);
  }

  function basculerDistance() {
    const bloc = document.getElementById('champ-distance');
    const distance = champ('cmd-distance');
    const horsBordeaux = champ('cmd-ville').value.trim() !== '' && !estBordeaux();
    bloc.hidden = !horsBordeaux;
    distance.required = horsBordeaux;
    if (!horsBordeaux) distance.value = '';
    calculer();
  }

  /* ---------- 4. Envoi : le serveur revalide et recalcule tout ---------- */

  formulaire.addEventListener('submit', async (evenement) => {
    evenement.preventDefault();

    const reponse = await fetch('../backend/commandes/create-commande.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({
        menu_id: champ('cmd-menu').value,
        nb_personnes: Number(champ('cmd-personnes').value),
        date_prestation: champ('cmd-date').value,
        heure_livraison: champ('cmd-heure').value,
        adresse_livraison: champ('cmd-adresse').value.trim(),
        ville_livraison: champ('cmd-ville').value.trim(),
        distance_km: Number(champ('cmd-distance').value) || 0,
      }),
    });

    let donnees = {};
    try { donnees = await reponse.json(); } catch { /* réponse vide */ }

    if (reponse.ok) {
      afficherMessage(`${donnees.message} (n° ${donnees.commande.numero_commande}) — redirection vers votre espace…`, false);
      formulaire.querySelector('button[type="submit"]').disabled = true;
      setTimeout(() => { location.href = 'espace-utilisateur/dashboard.html'; }, 2500);
    } else if (donnees.champs) {
      afficherMessage(Object.values(donnees.champs).join(' '), true);
    } else {
      afficherMessage(donnees.erreur || 'La commande n\'a pas pu être envoyée.', true);
    }
  });

  /* ---------- Écouteurs ---------- */

  champ('cmd-menu').addEventListener('change', appliquerMinimum);
  champ('cmd-personnes').addEventListener('input', calculer);
  champ('cmd-ville').addEventListener('input', basculerDistance);
  champ('cmd-distance').addEventListener('input', calculer);

  // La date de prestation ne peut pas être dans le passé (miroir du serveur)
  champ('cmd-date').min = new Date().toISOString().split('T')[0];

  /* ---------- Initialisation ---------- */

  chargerProfil().then((profil) => {
    if (profil) chargerMenus();
  });
})();
