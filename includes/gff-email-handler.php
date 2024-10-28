<?php
// includes/gff-email-handler.php

if (!defined('ABSPATH')) exit; // Sécurité

/**
 * Fonction pour envoyer un email au fournisseur lorsque la facture est rejetée
 *
 * @param int $facture_id L'ID de la facture
 * @param string $commentaire Le commentaire de l'administrateur (optionnel)
 */
function gff_envoyer_email_facture_rejetee($facture_id, $commentaire = '')
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'gff_factures';

    // Récupérer les informations de la facture et du fournisseur
    $facture = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $facture_id));
    $fournisseur_id = $facture->fournisseur_id;
    $fournisseur_email = get_userdata($fournisseur_id)->user_email;

    if (!$fournisseur_email) {
        return false; // Pas d'email trouvé
    }

    // Construire l'email
    $subject = 'Votre facture a été rejetée';
    $message = "Bonjour,\n\nNous vous informons que votre facture n°" . $facture->numero_facture . " a été rejetée.";
    if (!empty($commentaire)) {
        $message .= "\n\nRaison : " . sanitize_text_field($commentaire);
    }
    $message .= "\n\nMerci de vérifier et de soumettre à nouveau la facture si nécessaire.\n\nCordialement,\nL'équipe de gestion des factures";

    // Envoyer l'email
    return wp_mail($fournisseur_email, $subject, $message);
}
