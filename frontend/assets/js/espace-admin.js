// Espace administrateur : création et désactivation des comptes employés.
// (Les statistiques MongoDB — nb de commandes et CA par menu — arrivent en
// phase 7b avec l'extension PHP mongodb.)
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

  /* ---------- Déconnexion ---------- */

  document.getElementById('lien-deconnexion').addEventListener('click', async (evenement) => {
    evenement.preventDefault();
    await VG.api('auth/logout.php', { corps: {} });
    location.href = '../index.html';
  });

  chargerEmployes();
})();
