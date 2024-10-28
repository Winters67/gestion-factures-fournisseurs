<?php
if (!is_user_logged_in() || !current_user_can('fournisseur')) {
    echo 'Accès réservé aux fournisseurs.';
    return;
}

global $wpdb;
$table_name = $wpdb->prefix . 'gff_factures';

$submission_success = false;

if (isset($_POST['submit_invoice']) && isset($_FILES['invoice_file'])) {
    if (isset($_POST['gff_nonce']) && wp_verify_nonce($_POST['gff_nonce'], 'gff_upload_invoice')) {
        // Récupérer et valider les données du formulaire
        $societe_id = get_field('Id_Frn', 'user_' . get_current_user_id());
        $numero_commande = sanitize_text_field($_POST['numero_commande']);
        $numero_facture = sanitize_text_field($_POST['numero_facture']);
        $date_facture = sanitize_text_field($_POST['date_facture']);
        $montant_ht = floatval($_POST['montant_ht']);
        $montant_ttc = floatval($_POST['montant_ttc']);
        $part_transport_ht = floatval($_POST['part_transport_ht']);

        if (!empty($numero_facture)) {
            // Vérifier si le fichier est un PDF
            $uploaded_file = $_FILES['invoice_file'];
            $file_type = wp_check_filetype($uploaded_file['name']);
            if ($file_type['ext'] !== 'pdf') {
                echo '<div class="error"><p>Veuillez télécharger un fichier PDF.</p></div>';
            } else {
                if (!function_exists('wp_handle_upload')) {
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                }

                $upload_overrides = array('test_form' => false);
                $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

                if ($movefile && !isset($movefile['error'])) {
                    // Vérifier si la facture existe déjà
                    $existing_facture = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_name WHERE numero_facture = %s AND fournisseur_id = %d",
                        $numero_facture,
                        get_current_user_id()
                    ));

                    if ($existing_facture == 0) {
                        // Insérer les données si la facture n'existe pas
                        $wpdb->insert(
                            $table_name,
                            array(
                                'fournisseur_id'    => get_current_user_id(),
                                'societe_id'        => $societe_id,
                                'numero_commande'   => $numero_commande,
                                'numero_facture'    => $numero_facture,
                                'date_facture'      => $date_facture,
                                'montant_ht'        => $montant_ht,
                                'montant_ttc'       => $montant_ttc,
                                'part_transport_ht' => $part_transport_ht,
                                'fichier'           => $movefile['url'],
                                'statut'            => 'en attente',
                            )
                        );

                        // Indiquer que la soumission a réussi
                        $submission_success = true;
                    } else {
                        echo '<div class="error"><p>Une facture avec ce numéro existe déjà pour ce fournisseur.</p></div>';
                    }
                } else {
                    echo '<div class="error"><p>Erreur : ' . esc_html($movefile['error']) . '</p></div>';
                }
            }
        } else {
            echo '<div class="error"><p>Le numéro de facture est obligatoire.</p></div>';
        }
    }
}

// Réinitialiser les variables après une soumission réussie
if ($submission_success) {
    echo '<script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            const popupOverlay = document.getElementById("confirmationPopupOverlay");
            popupOverlay.style.display = "flex";
        });
    </script>';
    // Réinitialiser les valeurs des champs à vide
    $_POST = array();
}
?>

<h1>Tableau de Bord Fournisseur</h1>

<!-- Formulaire -->
<form id="invoice_form" method="post" enctype="multipart/form-data">
    <?php wp_nonce_field('gff_upload_invoice', 'gff_nonce'); ?>
    <p>
        <label for="numero_commande">Numéro de commande :</label><br>
        <input type="text" name="numero_commande" id="numero_commande" required>
    </p>
    <p>
        <label for="numero_facture">Numéro de facture :</label><br>
        <input type="text" name="numero_facture" id="numero_facture" required>
    </p>
    <p>
        <label for="date_facture">Date de la facture :</label><br>
        <input type="date" name="date_facture" id="date_facture" required>
    </p>
    <p>
        <label for="montant_ht">Montant HT :</label><br>
        <input type="number" step="0.01" name="montant_ht" id="montant_ht" required>
    </p>
    <p>
        <label for="montant_ttc">Montant TTC :</label><br>
        <input type="number" step="0.01" name="montant_ttc" id="montant_ttc" required>
    </p>
    <p>
        <label for="part_transport_ht">Part de transport HT :</label><br>
        <input type="number" step="0.01" name="part_transport_ht" id="part_transport_ht" required>
    </p>
    <p>
        <label for="invoice_file">Télécharger votre facture (PDF) :</label><br>
        <input type="file" name="invoice_file" id="invoice_file" accept=".pdf" required>
    </p>
    <p>
        <input type="submit" name="submit_invoice" value="Envoyer" class="button">
    </p>
</form>

<!-- Overlay et Popup de confirmation -->
<div id="confirmationPopupOverlay" style="display: none;">
    <div id="confirmationPopup">
        <p>Facture envoyée avec succès !</p>
        <button id="closePopupButton">Fermer</button>
    </div>
</div>