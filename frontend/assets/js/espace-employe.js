// Espace employé : gestion des commandes (statuts, annulation motivée),
// modération des avis, CRUD menus / plats / horaires.
// L'administrateur a accès à tout (il peut faire tout ce qu'un employé fait).

(async () => {
  const profil = await VG.garde(['employe', 'administrateur']);
  if (!profil) return;

  document.getElementById('salutation').textContent =
    `Connecté(e) : ${profil.prenom || profil.email} — ${profil.role}`;

  // Un administrateur qui visite l'espace employé doit pouvoir revenir au sien
  if (profil.role === 'administrateur') {
    document.getElementById('nav-retour-admin')?.removeAttribute('hidden');
  }

  /* ============================================================
     COMMANDES : filtres, transitions de statut, annulation motivée
     ============================================================ */

  const TRANSITIONS = {
    cree: ['accepte'],
    accepte: ['en_preparation'],
    en_preparation: ['en_livraison'],
    en_livraison: ['livre'],
    livre: ['attente_materiel', 'terminee'],
    attente_materiel: ['terminee'],
  };

  const tbodyCommandes = document.getElementById('tbody-commandes');

  function ligneDetail(colonnes, contenu) {
    const tr = VG.el('tr');
    const td = VG.el('td');
    td.colSpan = colonnes;
    td.append(contenu);
    tr.append(td);
    return tr;
  }

  function basculerDetail(ligne, colonnes, construire) {
    if (ligne.nextElementSibling?.dataset.detail === '1') {
      ligne.nextElementSibling.remove();
      return;
    }
    document.querySelectorAll('tr[data-detail="1"]').forEach((tr) => tr.remove());
    const detail = ligneDetail(colonnes, construire());
    detail.dataset.detail = '1';
    ligne.after(detail);
  }

  function lienAction(libelle, surClic) {
    const lien = VG.el('a', null, libelle);
    lien.href = '#';
    lien.addEventListener('click', (evenement) => { evenement.preventDefault(); surClic(); });
    return lien;
  }

  function formulaireAnnulation(commande) {
    const form = VG.el('form', 'filtres');
    form.append(VG.el('p', null,
      '⚠ Contactez d\'abord le client, puis indiquez le motif et le mode de contact :'));

    const blocMotif = VG.el('div', 'champ');
    const labelMotif = VG.el('label', null, 'Motif');
    const motif = VG.el('textarea');
    motif.rows = 2;
    motif.id = `annul-motif-${commande.commande_id}`;
    labelMotif.htmlFor = motif.id;
    blocMotif.append(labelMotif, motif);

    const blocMode = VG.el('div', 'champ');
    const labelMode = VG.el('label', null, 'Mode de contact');
    const mode = VG.el('select');
    mode.id = `annul-mode-${commande.commande_id}`;
    labelMode.htmlFor = mode.id;
    mode.append(new Option('Appel GSM', 'gsm'), new Option('Mail', 'mail'));
    blocMode.append(labelMode, mode);

    const bouton = VG.el('button', 'btn btn--primaire', 'Annuler la commande');
    bouton.type = 'submit';
    form.append(blocMotif, blocMode, bouton);

    form.addEventListener('submit', async (evenement) => {
      evenement.preventDefault();
      const { ok, donnees } = await VG.api('commandes/update-commande.php', {
        corps: {
          commande_id: commande.commande_id,
          action: 'annuler',
          motif: motif.value.trim(),
          mode_contact: mode.value,
        },
      });
      VG.message(donnees.message || donnees.erreur, !ok);
      if (ok) chargerCommandes();
    });

    return form;
  }

  async function changerStatut(commande, statut) {
    const { ok, donnees } = await VG.api('commandes/update-commande.php', {
      corps: { commande_id: commande.commande_id, action: 'changer_statut', statut },
    });
    VG.message(donnees.message || donnees.erreur, !ok);
    if (ok) chargerCommandes();
  }

  async function chargerCommandes() {
    const parametres = new URLSearchParams();
    const statut = document.getElementById('fc-statut').value;
    const client = document.getElementById('fc-client').value.trim();
    if (statut) parametres.set('statut', statut);
    if (client) parametres.set('client', client);

    const { ok, donnees } = await VG.api(`commandes/get-commandes.php?${parametres}`);
    if (!ok) return;

    tbodyCommandes.replaceChildren();
    if (donnees.total === 0) {
      tbodyCommandes.append(ligneDetail(7, VG.el('p', null, 'Aucune commande ne correspond à ces critères.')));
      return;
    }

    donnees.commandes.forEach((commande) => {
      const tr = VG.el('tr');
      tr.append(
        VG.el('td', null, commande.numero_commande),
        VG.el('td', null, `${commande.client_prenom} ${commande.client_nom}`),
        VG.el('td', null, commande.menu),
        VG.el('td', null, `${VG.dateFr(commande.date_prestation)} à ${VG.heureFr(commande.heure_livraison)}`),
        VG.el('td', null, VG.euros(commande.prix_total)),
      );
      const tdStatut = VG.el('td');
      tdStatut.append(VG.badgeStatut(commande.statut));
      tr.append(tdStatut);

      const tdActions = VG.el('td');
      const actions = [];

      (TRANSITIONS[commande.statut] || []).forEach((cible) => {
        actions.push(lienAction(`→ ${VG.STATUTS[cible].libelle}`, () => changerStatut(commande, cible)));
      });
      if (!['terminee', 'annulee'].includes(commande.statut)) {
        actions.push(lienAction('Annuler', () => basculerDetail(tr, 7, () => formulaireAnnulation(commande))));
      }
      actions.push(lienAction('Suivi', () => basculerDetail(tr, 7, () => VG.listeSuivi(commande.suivi))));

      actions.forEach((action, indice) => {
        if (indice > 0) tdActions.append(document.createTextNode(' · '));
        tdActions.append(action);
      });
      tr.append(tdActions);
      tbodyCommandes.append(tr);
    });
  }

  let minuteur = null;
  document.getElementById('fc-statut').addEventListener('change', chargerCommandes);
  document.getElementById('fc-client').addEventListener('input', () => {
    clearTimeout(minuteur);
    minuteur = setTimeout(chargerCommandes, 300);
  });

  /* ============================================================
     AVIS : modération (seuls les avis validés paraissent à l'accueil)
     ============================================================ */

  const zoneAvis = document.getElementById('zone-avis');

  async function chargerAvis() {
    const { ok, donnees } = await VG.api('avis/gestion-avis.php?statut=en_attente');
    if (!ok) return;

    zoneAvis.replaceChildren();
    if (donnees.avis.length === 0) {
      zoneAvis.append(VG.el('p', null, 'Aucun avis en attente de modération. ✨'));
      return;
    }

    donnees.avis.forEach((avis) => {
      const carte = VG.el('article', 'carte');
      const note = VG.el('p', 'avis__note', '★'.repeat(avis.note) + '☆'.repeat(5 - avis.note));
      note.setAttribute('aria-hidden', 'true');
      carte.append(
        note,
        VG.el('p', null, `« ${avis.description} »`),
        VG.el('p', 'avis__auteur', `${avis.auteur} — ${avis.menu} (${avis.numero_commande})`),
      );

      const boutons = VG.el('p');
      const valider = VG.el('button', 'btn btn--primaire', 'Valider');
      const refuser = VG.el('button', 'btn btn--secondaire', 'Refuser');
      [['valide', valider], ['refuse', refuser]].forEach(([decision, bouton]) => {
        bouton.type = 'button';
        bouton.addEventListener('click', async () => {
          const { ok: fait, donnees: retour } = await VG.api('avis/gestion-avis.php', {
            corps: { action: 'moderer', avis_id: avis.avis_id, decision },
          });
          VG.message(retour.message || retour.erreur, !fait);
          if (fait) chargerAvis();
        });
      });
      boutons.append(valider, document.createTextNode(' '), refuser);
      carte.append(boutons);
      zoneAvis.append(carte);
    });
  }

  /* ============================================================
     MENUS : catalogue complet (actifs + désactivés), édition rapide
     ============================================================ */

  const tbodyMenus = document.getElementById('tbody-menus');

  function formulaireEditionMenu(menu) {
    const form = VG.el('form', 'filtres');
    const grille = VG.el('div', 'grille grille--3');
    const saisies = {};
    [['titre', 'Titre', 'text', menu.titre],
     ['prix_min', 'Prix (€)', 'number', menu.prix_min],
     ['quantite_restante', 'Stock', 'number', menu.quantite_restante]].forEach(([nom, libelle, type, valeur]) => {
      const bloc = VG.el('div', 'champ');
      const label = VG.el('label', null, libelle);
      const champ = VG.el('input');
      champ.type = type;
      champ.value = valeur;
      if (type === 'number') champ.step = nom === 'prix_min' ? '0.01' : '1';
      champ.id = `menu-${nom}-${menu.menu_id}`;
      label.htmlFor = champ.id;
      saisies[nom] = champ;
      bloc.append(label, champ);
      grille.append(bloc);
    });
    const bouton = VG.el('button', 'btn btn--primaire', 'Enregistrer');
    bouton.type = 'submit';
    form.append(grille, bouton);

    form.addEventListener('submit', async (evenement) => {
      evenement.preventDefault();
      const { ok, donnees } = await VG.api('menus/update-menu.php', {
        corps: {
          menu_id: menu.menu_id,
          titre: saisies.titre.value.trim(),
          prix_min: Number(saisies.prix_min.value),
          quantite_restante: Number(saisies.quantite_restante.value),
        },
      });
      VG.message(donnees.message || donnees.erreur, !ok);
      if (ok) chargerMenusGestion();
    });
    return form;
  }

  async function chargerMenusGestion() {
    const { ok, donnees } = await VG.api('menus/get-menus.php?tous=1');
    if (!ok) return;

    tbodyMenus.replaceChildren();
    donnees.menus.forEach((menu) => {
      const tr = VG.el('tr');
      tr.append(
        VG.el('td', null, menu.titre),
        VG.el('td', null, VG.euros(menu.prix_min)),
        VG.el('td', null, `≥ ${menu.nb_personnes_min} pers.`),
        VG.el('td', null, String(menu.quantite_restante)),
      );
      const tdEtat = VG.el('td');
      tdEtat.append(VG.el('span', `statut ${Number(menu.actif) ? 'statut--accepte' : 'statut--alerte'}`,
        Number(menu.actif) ? 'En ligne' : 'Désactivé'));
      tr.append(tdEtat);

      const tdActions = VG.el('td');
      const modifier = lienAction('Modifier', () => basculerDetail(tr, 6, () => formulaireEditionMenu(menu)));
      const bascule = lienAction(Number(menu.actif) ? 'Désactiver' : 'Réactiver', async () => {
        const appel = Number(menu.actif)
          ? VG.api('menus/delete-menu.php', { corps: { menu_id: menu.menu_id } })
          : VG.api('menus/update-menu.php', { corps: { menu_id: menu.menu_id, actif: 1 } });
        const { ok: fait, donnees: retour } = await appel;
        VG.message(retour.message || retour.erreur, !fait);
        if (fait) chargerMenusGestion();
      });
      tdActions.append(modifier, document.createTextNode(' · '), bascule);
      tr.append(tdActions);
      tbodyMenus.append(tr);
    });
  }

  /* ---------- Création d'un menu ---------- */

  async function preparerFormulaireCreation() {
    const { donnees: referentiels } = await VG.api('menus/get-filtres.php');
    const themes = document.getElementById('nm-theme');
    const regimes = document.getElementById('nm-regime');
    referentiels.themes.forEach((t) => themes.append(new Option(t.libelle, t.theme_id)));
    referentiels.regimes.forEach((r) => regimes.append(new Option(r.libelle, r.regime_id)));

    const { donnees: catalogue } = await VG.api('plats/get-plats.php');
    const zone = document.getElementById('nm-plats');
    catalogue.plats.forEach((plat) => {
      const label = VG.el('label', 'badge');
      const case_ = VG.el('input');
      case_.type = 'checkbox';
      case_.value = plat.plat_id;
      case_.name = 'nm-plat';
      label.append(case_, document.createTextNode(` ${plat.titre} (${plat.type})`));
      zone.append(label, document.createTextNode(' '));
    });
  }

  document.getElementById('form-nouveau-menu').addEventListener('submit', async (evenement) => {
    evenement.preventDefault();
    const valeur = (id) => document.getElementById(id).value.trim();
    const plats = [...document.querySelectorAll('input[name="nm-plat"]:checked')].map((c) => Number(c.value));

    const { ok, donnees } = await VG.api('menus/create-menu.php', {
      corps: {
        titre: valeur('nm-titre'),
        description: valeur('nm-description'),
        conditions: valeur('nm-conditions'),
        nb_personnes_min: Number(valeur('nm-min')),
        prix_min: Number(valeur('nm-prix')),
        quantite_restante: Number(valeur('nm-stock')),
        theme_id: Number(valeur('nm-theme')),
        regime_id: Number(valeur('nm-regime')),
        plats,
        images: [],
      },
    });
    VG.message(donnees.message || donnees.erreur ||
      (donnees.champs ? Object.values(donnees.champs).join(' ') : 'Erreur.'), !ok);
    if (ok) {
      evenement.target.reset();
      chargerMenusGestion();
    }
  });

  /* ============================================================
     PLATS : catalogue, création/édition, suppression
     ============================================================ */

  const tbodyPlats = document.getElementById('tbody-plats');
  const TYPES = { entree: 'Entrée', plat: 'Plat', dessert: 'Dessert' };
  let allergenesReferentiel = [];

  async function chargerPlats() {
    const { ok, donnees } = await VG.api('plats/get-plats.php');
    if (!ok) return;
    allergenesReferentiel = donnees.allergenes;

    // Cases à cocher du formulaire plat (une seule fois)
    const zone = document.getElementById('pl-allergenes');
    if (zone.childElementCount === 0) {
      allergenesReferentiel.forEach((allergene) => {
        const label = VG.el('label', 'badge');
        const case_ = VG.el('input');
        case_.type = 'checkbox';
        case_.value = allergene.allergene_id;
        case_.name = 'pl-allergene';
        label.append(case_, document.createTextNode(` ${allergene.libelle}`));
        zone.append(label, document.createTextNode(' '));
      });
    }

    tbodyPlats.replaceChildren();
    donnees.plats.forEach((plat) => {
      const tr = VG.el('tr');
      tr.append(
        VG.el('td', null, plat.titre),
        VG.el('td', null, TYPES[plat.type] || plat.type),
        VG.el('td', null, plat.allergenes.map((a) => a.libelle).join(', ') || '—'),
      );
      const tdActions = VG.el('td');
      const modifier = lienAction('Modifier', () => {
        document.getElementById('pl-id').value = plat.plat_id;
        document.getElementById('pl-titre').value = plat.titre;
        document.getElementById('pl-type').value = plat.type;
        const coches = plat.allergenes.map((a) => String(a.allergene_id));
        document.querySelectorAll('input[name="pl-allergene"]').forEach((c) => {
          c.checked = coches.includes(c.value);
        });
        document.getElementById('pl-titre').focus();
      });
      const supprimer = lienAction('Supprimer', async () => {
        if (!confirm(`Supprimer « ${plat.titre} » ? Il sera retiré de tous les menus.`)) return;
        const { ok: fait, donnees: retour } = await VG.api('plats/delete-plat.php', {
          corps: { plat_id: plat.plat_id },
        });
        VG.message(retour.message || retour.erreur, !fait);
        if (fait) chargerPlats();
      });
      tdActions.append(modifier, document.createTextNode(' · '), supprimer);
      tr.append(tdActions);
      tbodyPlats.append(tr);
    });
  }

  document.getElementById('form-plat').addEventListener('submit', async (evenement) => {
    evenement.preventDefault();
    const platId = document.getElementById('pl-id').value;
    const allergenes = [...document.querySelectorAll('input[name="pl-allergene"]:checked')]
      .map((c) => Number(c.value));

    const corps = {
      titre: document.getElementById('pl-titre').value.trim(),
      type: document.getElementById('pl-type').value,
      allergenes,
    };
    if (platId) corps.plat_id = Number(platId);

    const { ok, donnees } = await VG.api('plats/save-plat.php', { corps });
    VG.message(donnees.message || donnees.erreur, !ok);
    if (ok) {
      evenement.target.reset();
      document.getElementById('pl-id').value = '';
      chargerPlats();
    }
  });

  /* ============================================================
     HORAIRES : lundi → dimanche (vide = fermé)
     ============================================================ */

  const zoneHoraires = document.getElementById('zone-horaires');

  async function chargerHoraires() {
    const { ok, donnees } = await VG.api('horaires/get-horaires.php');
    if (!ok) return;

    zoneHoraires.replaceChildren();
    donnees.horaires.forEach((horaire) => {
      const bloc = VG.el('div', 'champ filtres__fourchette');
      const label = VG.el('label', null, horaire.jour[0].toUpperCase() + horaire.jour.slice(1));
      label.htmlFor = `h-${horaire.jour}-ouverture`;

      const ouverture = VG.el('input');
      ouverture.type = 'time';
      ouverture.id = `h-${horaire.jour}-ouverture`;
      ouverture.value = horaire.heure_ouverture ? String(horaire.heure_ouverture).slice(0, 5) : '';

      const fermeture = VG.el('input');
      fermeture.type = 'time';
      fermeture.id = `h-${horaire.jour}-fermeture`;
      fermeture.setAttribute('aria-label', `${horaire.jour} : heure de fermeture`);
      fermeture.value = horaire.heure_fermeture ? String(horaire.heure_fermeture).slice(0, 5) : '';

      const enveloppe = VG.el('div');
      enveloppe.append(label, ouverture, fermeture);
      bloc.append(enveloppe);
      zoneHoraires.append(bloc);
    });
  }

  document.getElementById('btn-horaires').addEventListener('click', async () => {
    const jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
    const horaires = jours.map((jour) => ({
      jour,
      heure_ouverture: document.getElementById(`h-${jour}-ouverture`)?.value || null,
      heure_fermeture: document.getElementById(`h-${jour}-fermeture`)?.value || null,
    }));
    const { ok, donnees } = await VG.api('horaires/save-horaires.php', { corps: { horaires } });
    VG.message(donnees.message || donnees.erreur, !ok);
  });

  /* ============================================================
     Déconnexion + initialisation
     ============================================================ */

  document.getElementById('lien-deconnexion').addEventListener('click', async (evenement) => {
    evenement.preventDefault();
    await VG.api('auth/logout.php', { corps: {} });
    location.href = '../index.html';
  });

  chargerCommandes();
  chargerAvis();
  chargerMenusGestion();
  preparerFormulaireCreation();
  chargerPlats();
  chargerHoraires();
})();
