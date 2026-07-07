// Espace administrateur : comptes employés + statistiques MongoDB
// (nombre de commandes par menu en graphique, chiffre d'affaires filtrable).
// Graphique dessiné en canvas natif : aucune librairie externe à charger,
// les valeurs précises restent accessibles dans le tableau voisin.
// Rappel : impossible de créer un compte administrateur depuis l'application.

(async () => {
  const profil = await VG.garde(['administrateur']);
  if (!profil) return;

  document.getElementById('salutation').textContent =
    `Connecté : ${profil.prenom || profil.email} — administrateur`;

  /* ---------- Comptes employés ---------- */

  const tbody = document.getElementById('tbody-employes');

  async function chargerEmployes() {
    const { ok, donnees } = await VG.api('admin/gestion-employes.php');
    if (!ok) return;

    tbody.replaceChildren();
    if (donnees.employes.length === 0) {
      const tr = VG.el('tr');
      const td = VG.el('td', null, 'Aucun compte employé.');
      td.colSpan = 3;
      tr.append(td);
      tbody.append(tr);
      return;
    }

    donnees.employes.forEach((employe) => {
      const tr = VG.el('tr');
      tr.append(VG.el('td', null, employe.email));

      const tdEtat = VG.el('td');
      tdEtat.append(VG.el('span', `statut ${Number(employe.actif) ? 'statut--accepte' : 'statut--alerte'}`,
        Number(employe.actif) ? 'Actif' : 'Désactivé'));
      tr.append(tdEtat);

      const tdAction = VG.el('td');
      const lien = VG.el('a', null, Number(employe.actif) ? 'Désactiver' : 'Réactiver');
      lien.href = '#';
      lien.addEventListener('click', async (evenement) => {
        evenement.preventDefault();
        const { ok: fait, donnees: retour } = await VG.api('admin/gestion-employes.php', {
          corps: { action: 'activer', utilisateur_id: employe.utilisateur_id, actif: !Number(employe.actif) },
        });
        VG.message(retour.message || retour.erreur, !fait);
        if (fait) chargerEmployes();
      });
      tdAction.append(lien);
      tr.append(tdAction);
      tbody.append(tr);
    });
  }

  document.getElementById('form-employe').addEventListener('submit', async (evenement) => {
    evenement.preventDefault();
    const { ok, donnees } = await VG.api('admin/gestion-employes.php', {
      corps: {
        action: 'creer',
        email: document.getElementById('emp-email').value.trim(),
        password: document.getElementById('emp-password').value,
      },
    });
    VG.message(donnees.message || donnees.erreur, !ok);
    if (ok) {
      evenement.target.reset();
      chargerEmployes();
    }
  });

  /* ---------- Statistiques (base NoSQL MongoDB) ---------- */

  const COULEURS = { barre: '#7B2D35', axe: '#C9C2B8', texte: '#2D2A26', valeur: '#C99B3F' };

  function dessinerGraphique(stats) {
    const canvas = document.getElementById('graphique-stats');
    const vide = document.getElementById('graphique-vide');
    const contexte = canvas.getContext('2d');
    contexte.clearRect(0, 0, canvas.width, canvas.height);

    vide.hidden = stats.length > 0;
    if (stats.length === 0) return;

    const marge = { haut: 24, bas: 64, gauche: 36, droite: 12 };
    const largeurUtile = canvas.width - marge.gauche - marge.droite;
    const hauteurUtile = canvas.height - marge.haut - marge.bas;
    const maximum = Math.max(...stats.map((s) => s.nb_commandes));
    const pas = largeurUtile / stats.length;
    const largeurBarre = Math.min(pas * 0.6, 64);

    // Axes
    contexte.strokeStyle = COULEURS.axe;
    contexte.lineWidth = 1.5;
    contexte.beginPath();
    contexte.moveTo(marge.gauche, marge.haut);
    contexte.lineTo(marge.gauche, canvas.height - marge.bas);
    contexte.lineTo(canvas.width - marge.droite, canvas.height - marge.bas);
    contexte.stroke();

    stats.forEach((stat, indice) => {
      const hauteur = (stat.nb_commandes / maximum) * hauteurUtile;
      const x = marge.gauche + indice * pas + (pas - largeurBarre) / 2;
      const y = canvas.height - marge.bas - hauteur;

      // Barre
      contexte.fillStyle = COULEURS.barre;
      contexte.fillRect(x, y, largeurBarre, hauteur);

      // Valeur au-dessus de la barre
      contexte.fillStyle = COULEURS.texte;
      contexte.font = 'bold 13px Arial';
      contexte.textAlign = 'center';
      contexte.fillText(String(stat.nb_commandes), x + largeurBarre / 2, y - 6);

      // Libellé du menu (tronqué), incliné sous l'axe
      const libelle = stat.titre.replace(/^Menu /, '');
      const court = libelle.length > 16 ? `${libelle.slice(0, 15)}…` : libelle;
      contexte.save();
      contexte.translate(x + largeurBarre / 2, canvas.height - marge.bas + 12);
      contexte.rotate(-Math.PI / 7);
      contexte.textAlign = 'right';
      contexte.font = '11px Arial';
      contexte.fillText(court, 0, 8);
      contexte.restore();
    });
  }

  async function chargerStats() {
    const parametres = new URLSearchParams();
    const menu = document.getElementById('st-menu').value;
    const debut = document.getElementById('st-debut').value;
    const fin = document.getElementById('st-fin').value;
    if (menu) parametres.set('menu', menu);
    if (debut) parametres.set('debut', debut);
    if (fin) parametres.set('fin', fin);

    const { ok, donnees } = await VG.api(`admin/get-stats.php?${parametres}`);
    const tbody = document.getElementById('tbody-stats');
    if (!ok) {
      tbody.replaceChildren();
      const tr = VG.el('tr');
      const td = VG.el('td', null, donnees.erreur || 'Statistiques indisponibles.');
      td.colSpan = 3;
      tr.append(td);
      tbody.append(tr);
      return;
    }

    tbody.replaceChildren();
    donnees.stats.forEach((stat) => {
      const tr = VG.el('tr');
      tr.append(
        VG.el('td', null, stat.titre),
        VG.el('td', null, String(stat.nb_commandes)),
        VG.el('td', null, VG.euros(stat.chiffre_affaires)),
      );
      tbody.append(tr);
    });
    document.getElementById('stats-total-commandes').textContent = String(donnees.total_commandes);
    document.getElementById('stats-total-ca').textContent = VG.euros(donnees.total_ca);

    dessinerGraphique(donnees.stats);
  }

  async function preparerFiltresStats() {
    // Liste des menus depuis l'API (catalogue complet, menus désactivés compris)
    const { ok, donnees } = await VG.api('menus/get-menus.php?tous=1');
    if (ok) {
      const select = document.getElementById('st-menu');
      select.replaceChildren(new Option('Tous les menus', ''));
      donnees.menus.forEach((menu) => select.append(new Option(menu.titre, menu.menu_id)));
    }
    ['st-menu', 'st-debut', 'st-fin'].forEach((id) => {
      document.getElementById(id).addEventListener('change', chargerStats);
    });
  }

  /* ---------- Déconnexion ---------- */

  document.getElementById('lien-deconnexion').addEventListener('click', async (evenement) => {
    evenement.preventDefault();
    await VG.api('auth/logout.php', { corps: {} });
    location.href = '../index.html';
  });

  chargerEmployes();
  preparerFiltresStats().then(chargerStats);
})();
