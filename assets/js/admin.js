document.addEventListener("DOMContentLoaded", function () {
  const selectElements = document.querySelectorAll('select[name="new_statut"]');

  selectElements.forEach((select) => {
    select.addEventListener("change", function (event) {
      const form = event.target.closest("form");
      const commentField = form.querySelector(
        'input[name="rejection_comment"]'
      );
      const sendButton = form.querySelector('button[id^="submit_comment_"]');

      if (event.target.value === "facture_rejetee") {
        const confirmation = confirm(
          "Voulez-vous vraiment envoyer un email au fournisseur pour indiquer que la facture est rejetée ?"
        );
        if (!confirmation) {
          // Réinitialiser le champ de commentaire et masquer si l’administrateur annule
          event.target.value = "";
          commentField.value = "";
          commentField.style.display = "none";
          sendButton.style.display = "none";
        } else {
          // Afficher le champ de commentaire et le bouton d'envoi si confirmé
          commentField.style.display = "inline-block";
          sendButton.style.display = "inline-block";
        }
      } else {
        // Réinitialiser et masquer le champ et l'icône si un autre statut est sélectionné
        commentField.style.display = "none";
        commentField.value = "";
        sendButton.style.display = "none";
      }
    });

    // Détecter le clic sur l'icône d'envoi pour soumettre le formulaire
    const form = select.closest("form");
    const sendButton = form.querySelector('button[id^="submit_comment_"]');
    if (sendButton) {
      sendButton.addEventListener("click", function () {
        form.submit(); // Soumettre le formulaire
      });
    }
  });
});
