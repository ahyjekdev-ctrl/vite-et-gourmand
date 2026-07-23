// Formulaire de contact : envoi de la demande à l'entreprise par mail.

(() => {
  const formulaire = document.getElementById('form-contact');
  if (!formulaire) return;

  function afficherMessage(texte, estErreur) {
    const zone = document.getElementById('message-contact');
    zone.textContent = texte;
    zone.className = estErreur ? 'message-erreur' : 'message-succes';
    zone.hidden = !texte;
  }

  formulaire.addEventListener('submit', async (evenement) => {
    evenement.preventDefault();

    const reponse = await fetch('../backend/contact/envoyer.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        titre: document.getElementById('ct-titre').value.trim(),
        description: document.getElementById('ct-description').value.trim(),
        email: document.getElementById('ct-email').value.trim(),
        site_web: document.getElementById('ct-site-web').value, // honeypot anti-spam
      }),
    });

    let donnees = {};
    try { donnees = await reponse.json(); } catch { /* réponse vide */ }

    if (reponse.ok) {
      afficherMessage(donnees.message, false);
      formulaire.reset();
    } else if (donnees.champs) {
      afficherMessage(Object.values(donnees.champs).join(' '), true);
    } else {
      afficherMessage(donnees.erreur || 'L\'envoi a échoué.', true);
    }
  });
})();
