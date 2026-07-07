// Boîte à outils commune aux trois espaces connectés (utilisateur, employé, admin) :
// garde d'accès par rôle, appels API, formats français, badges de statut.

const VG = (() => {
  // Racine du site quelle que soit la profondeur de la page courante
  const BASE = location.pathname.replace(/\/frontend\/.*$/, '');

  /** Appel API JSON. options = { methode, corps } */
  async function api(chemin, options = {}) {
    const reponse = await fetch(`${BASE}/backend/${chemin}`, {
      method: options.methode || (options.corps ? 'POST' : 'GET'),
      headers: options.corps ? { 'Content-Type': 'application/json' } : undefined,
      credentials: 'same-origin',
      body: options.corps ? JSON.stringify(options.corps) : undefined,
    });
    let donnees = {};
    try { donnees = await reponse.json(); } catch { /* réponse vide */ }
    return { ok: reponse.ok, statut: reponse.status, donnees };
  }

  /**
   * Garde d'accès : redirige vers la connexion si non connecté, ou vers
   * l'espace correspondant si le rôle ne donne pas accès à cette page.
   * Rappel : la sécurité qui fait foi est côté serveur (exigerRole) —
   * cette garde n'est là que pour l'expérience de navigation.
   */
  async function garde(rolesAcceptes) {
    const { ok, donnees } = await api('auth/me.php');
    if (!ok) {
      location.href = `${BASE}/frontend/connexion.html`;
      return null;
    }
    const profil = donnees.profil;
    if (!rolesAcceptes.includes(profil.role)) {
      const espaces = {
        utilisateur: 'espace-utilisateur',
        employe: 'espace-employe',
        administrateur: 'espace-admin',
      };
      location.href = `${BASE}/frontend/${espaces[profil.role]}/dashboard.html`;
      return null;
    }
    return profil;
  }

  /** Crée un élément avec classe et texte (textContent : anti-XSS). */
  function el(balise, classe, texte) {
    const element = document.createElement(balise);
    if (classe) element.className = classe;
    if (texte !== undefined) element.textContent = texte;
    return element;
  }

  const euros = (montant) =>
    Number(montant).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';

  const dateFr = (iso) => {
    const [annee, mois, jour] = String(iso).split(' ')[0].split('-');
    return `${jour}/${mois}/${annee}`;
  };

  const heureFr = (hms) => String(hms).slice(0, 5).replace(':', 'h');

  const STATUTS = {
    cree:             { libelle: 'En attente',            classe: 'statut--cree' },
    accepte:          { libelle: 'Acceptée',              classe: 'statut--accepte' },
    en_preparation:   { libelle: 'En préparation',        classe: 'statut--en-cours' },
    en_livraison:     { libelle: 'En cours de livraison', classe: 'statut--en-cours' },
    livre:            { libelle: 'Livrée',                classe: 'statut--en-cours' },
    attente_materiel: { libelle: 'Matériel à restituer',  classe: 'statut--alerte' },
    terminee:         { libelle: 'Terminée',              classe: 'statut--terminee' },
    annulee:          { libelle: 'Annulée',               classe: 'statut--alerte' },
  };

  function badgeStatut(statut) {
    const info = STATUTS[statut] || { libelle: statut, classe: 'statut--cree' };
    return el('span', `statut ${info.classe}`, info.libelle);
  }

  /** Affiche un message dans la zone d'alerte de la page. */
  function message(texte, estErreur) {
    const zone = document.getElementById('message-espace');
    if (!zone) return;
    zone.textContent = texte;
    zone.className = estErreur ? 'message-erreur' : 'message-succes';
    zone.hidden = !texte;
    zone.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  /** Liste <ol> du suivi historisé d'une commande. */
  function listeSuivi(suivi) {
    const liste = el('ol');
    suivi.forEach((jalon) => {
      const info = STATUTS[jalon.statut] || { libelle: jalon.statut };
      liste.append(el('li', null,
        `${info.libelle} — ${dateFr(jalon.date_heure)} à ${heureFr(jalon.date_heure.split(' ')[1] || '')}`));
    });
    return liste;
  }

  return { api, garde, el, euros, dateFr, heureFr, STATUTS, badgeStatut, message, listeSuivi };
})();
