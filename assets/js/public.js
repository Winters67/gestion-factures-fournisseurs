document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("invoice_form");
  const popupOverlay = document.getElementById("confirmationPopupOverlay");
  const closePopupButton = document.getElementById("closePopupButton");

  // Vérifier si la soumission a réussi en vérifiant l'URL
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get("success") === "1") {
    popupOverlay.style.display = "flex"; // Afficher la popup si la soumission a réussi
  }

  // Fermer la popup
  closePopupButton.addEventListener("click", function () {
    popupOverlay.style.display = "none";
    // Nettoyer l'URL en supprimant le paramètre "success"
    const newUrl = window.location.href.split("?")[0];
    window.history.replaceState({}, document.title, newUrl);
  });

  // Réinitialiser le formulaire après soumission
  form.addEventListener("submit", function (event) {
    // Laisser le formulaire se soumettre normalement
    setTimeout(function () {
      form.reset(); // Réinitialiser le formulaire après un délai
    }, 500);
  });
});
