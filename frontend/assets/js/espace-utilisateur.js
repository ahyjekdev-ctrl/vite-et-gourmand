// Espace utilisateur : commandes réelles (annulation/modification tant que
// non acceptée, suivi daté, dépôt d'avis quand terminée) + infos personnelles.

(async () => {
  const profil = await VG.garde(['utilisateur']);
  if (!profil) return;

  document.getElementById('salutation').textContent = `Bonjour ${profil.prenom} ${profil.nom}`;

  /* ---------- Mes informations personnelles ---------- */

  const champs = { nom: 'mi-nom', prenom: 'mi-prenom', telephone: 'mi-gsm',
                   adresse: 'mi-adresse', ville: 'mi-ville', code_postal: 'mi-cp' };
  Object.entries(champs).forEach(([cle, id]) => {
    document.getElementById(id).value = profil[cle] ?? '';
  });

  document.getElementById('form-infos').addEventListener('submit', async (evenement) => {
    evenement.preventDefault();
    const corps = {};
    Object.entries(champs).forEach(([cle, id]) => { corps[cle] = document.getElementById(id).value.trim(); });
    const { ok, donnees } = await VG.api('auth/update-profil.php', { corps });
    VG.message(donnees.message || donnees.erreur ||
      (donnees.champs ? Object.values(donnees.champs).join(' ') : 'Erreur.'), !ok);
  });

  /* ---------- Mes commandes ---------- */

  const tbody = document.getElementById('tbody-commandes');

  function ligneDetail(colonnes, contenu) {
    const tr = VG.el('tr');
    const td = VG.el('td');
    td.colSpan = colonnes;
    td.append(contenu);
    tr.append(td);
    return tr;
  }

  function formulaireModification(commande) {
    const form = VG.el('form', 'filtres');
    form.append(VG.el('p', null, 'Modifier la commande (tout sauf le menu) :'));

    const grille = VG.el('div', 'grille grille--3');
    const champsModif = [
      ['nb_personnes', 'Personnes', 'number', commande.nb_personnes],
      ['date_prestation', 'Date', 'date', String(commande.date_prestation).split(' ')[0]],
      ['heure_livraison', 'Heure', 'time', String(commande.heure_livraison).slice(0, 5)],
      ['adresse_livraison', 'Adresse', 'text', commande.adresse_livraison],
      ['ville_livraison', 'Ville', 'text', commande.ville_livraison],
      ['distance_km', 'Distance (km si hors Bordeaux)', 'number', commande.distance_km],
    ];
    const saisies = {};
    champsModif.forEach(([nom, libelle, type, valeur]) => {
      const bloc = VG.el('div', 'champ');
      const label = VG.el('label', null, libelle);
      const champ = VG.el('input');
      champ.type = type;
      champ.value = valeur;
      champ.id = `modif-${nom}-${commande.commande_id}`;
      label.htmlFor = champ.id;
      saisies[nom] = champ;
      bloc.append(label, champ);
      grille.append(bloc);
    });
    form.append(grille);

    const bouton = VG.el('button', 'btn btn--primaire', 'Enregistrer');
    bouton.type = 'submit';
    form.append(bouton);

    form.addEventListener('submit', async (evenement) => {
      evenement.preventDefault();
      const corps = { commande_id: commande.commande_id, action: 'modifier' };
      Object.entries(saisies).forEach(([nom, champ]) => { corps[nom] = champ.value; });
      corps.nb_personnes = Number(corps.nb_personnes);
      corps.distance_km = Number(corps.distance_km) || 0;
      const { ok, donnees } = await VG.api('commandes/update-commande.php', { corps });
      VG.message(donnees.message || donnees.erreur, !ok);
      if (ok) chargerCommandes();
    });

    return form;
  }

  function formulaireAvis(commande) {
    const form = VG.el('form', 'filtres');
    form.append(VG.el('p', null, `Votre avis sur « ${commande.menu} » :`));

    const blocNote = VG.el('div', 'champ');
    const labelNote = VG.el('label', null, 'Note');
    const note = VG.el('select');
    note.id = `avis-note-${commande.commande_id}`;
    labelNote.htmlFor = note.id;
    [5, 4, 3, 2, 1].forEach((valeur) => note.append(new Option(`${valeur} / 5`, valeur)));
    blocNote.append(labelNote, note);

    const blocTexte = VG.el('div', 'champ');
    const labelTexte = VG.el('label', null, 'Commentaire');
    const texte = VG.el('textarea');
    texte.rows = 3;
    texte.id = `avis-texte-${commande.commande_id}`;
    labelTexte.htmlFor = texte.id;
    blocTexte.append(labelTexte, texte);

    const bouton = VG.el('button', 'btn btn--primaire', 'Envoyer mon avis');
    bouton.type = 'submit';
    form.append(blocNote, blocTexte, bouton);

    form.addEventListener('submit', async (evenement) => {
      evenement.preventDefault();
      const { ok, donnees } = await VG.api('avis/gestion-avis.php', {
        corps: {
          action: 'deposer',
          commande_id: commande.commande_id,
          note: Number(note.value),
          description: texte.value.trim(),
        },
      });
      VG.message(donnees.message || donnees.erreur, !ok);
      if (ok) chargerCommandes();
    });

    return form;
  }

  function lienAction(libelle, surClic) {
    const lien = VG.el('a', null, libelle);
    lien.href = '#';
    lien.addEventListener('click', (evenement) => { evenement.preventDefault(); surClic(); });
    return lien;
  }

  /** Affiche/masque une ligne de détail sous la ligne de commande. */
  function basculerDetail(ligne, construire) {
    if (ligne.nextElementSibling?.dataset.detail === '1') {
      ligne.nextElementSibling.remove();
      return;
    }
    document.querySelectorAll('tr[data-detail="1"]').forEach((tr) => tr.remove());
    const detail = ligneDetail(6, construire());
    detail.dataset.detail = '1';
    ligne.after(detail);
  }

  async function chargerCommandes() {
    const { ok, donnees } = await VG.api('commandes/get-commandes.php');
    if (!ok) return;

    tbody.replaceChildren();
    if (donnees.total === 0) {
      tbody.append(ligneDetail(6, VG.el('p', null,
        'Aucune commande pour le moment — découvrez nos menus !')));
      return;
    }

    donnees.commandes.forEach((commande) => {
      const tr = VG.el('tr');
      tr.append(
        VG.el('td', null, commande.numero_commande),
        VG.el('td', null, commande.menu),
        VG.el('td', null, `${VG.dateFr(commande.date_prestation)} à ${VG.heureFr(commande.heure_livraison)}`),
        VG.el('td', null, VG.euros(commande.prix_total)),
      );
      const tdStatut = VG.el('td');
      tdStatut.append(VG.badgeStatut(commande.statut));
      tr.append(tdStatut);

      const tdActions = VG.el('td');
      const actions = [];

      if (commande.statut === 'cree') {
        actions.push(lienAction('Modifier', () => basculerDetail(tr, () => formulaireModification(commande))));
        actions.push(lienAction('Annuler', async () => {
          if (!confirm(`Annuler la commande ${commande.numero_commande} ?`)) return;
          const { ok: fait, donnees: retour } = await VG.api('commandes/update-commande.php', {
            corps: { commande_id: commande.commande_id, action: 'annuler' },
          });
          VG.message(retour.message || retour.erreur, !fait);
          if (fait) chargerCommandes();
        }));
      } else {
        actions.push(lienAction('Suivi', () => basculerDetail(tr, () => VG.listeSuivi(commande.suivi))));
      }
      if (commande.statut === 'terminee' && Number(commande.avis_depose) === 0) {
        actions.push(lienAction('Donner mon avis', () => basculerDetail(tr, () => formulaireAvis(commande))));
      }

      actions.forEach((action, indice) => {
        if (indice > 0) tdActions.append(document.createTextNode(' · '));
        tdActions.append(action);
      });
      if (actions.length === 0) tdActions.textContent = '—';
      tr.append(tdActions);
      tbody.append(tr);
    });
  }

  chargerCommandes();

  /* ---------- Déconnexion ---------- */
  document.getElementById('lien-deconnexion').addEventListener('click', async (evenement) => {
    evenement.preventDefault();
    await VG.api('auth/logout.php', { corps: {} });
    location.href = '../index.html';
  });
})();
