<?php
if (! is_user_logged_in() || ! current_user_can('fournisseur')) {
    echo 'Accès réservé aux fournisseurs.';
    return;
}

global $wpdb;
$table_name = $wpdb->prefix . 'gff_factures';

if (isset($_POST['submit_invoice']) && isset($_FILES['invoice_file'])) {
    if (isset($_POST['gff_nonce']) && wp_verify_nonce($_POST['gff_nonce'], 'gff_upload_invoice')) {
        // Récupérer et valider les données du formulaire
        $societe_id = intval($_POST['societe']);
        $numero_commande = sanitize_text_field($_POST['numero_commande']);
        $numero_facture = sanitize_text_field($_POST['numero_facture']);
        $date_facture = sanitize_text_field($_POST['date_facture']);
        $montant_ht = floatval($_POST['montant_ht']);
        $montant_ttc = floatval($_POST['montant_ttc']);

        // Vérifier que le fichier est un PDF
        $uploaded_file = $_FILES['invoice_file'];
        $file_type = wp_check_filetype($uploaded_file['name']);
        if ($file_type['ext'] !== 'pdf') {
            echo '<div class="error"><p>Veuillez télécharger un fichier PDF.</p></div>';
        } else {

            if (! function_exists('wp_handle_upload')) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }

            $upload_overrides = array('test_form' => false);

            $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

            if ($movefile && ! isset($movefile['error'])) {
                // Insérer les données dans la base
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
                        'fichier'           => $movefile['url'],
                        'statut'            => 'en attente',
                    )
                );
                echo '<div class="updated"><p>Facture envoyée avec succès.</p></div>';
            } else {
                echo '<div class="error"><p>Erreur : ' . esc_html($movefile['error']) . '</p></div>';
            }
        }
    }
}

// Récupérer les sociétés
$societes = get_terms(array(
    'taxonomy'   => 'gff_societe',
    'hide_empty' => false,
));
?>

<h1>Tableau de Bord Fournisseur</h1>

<form method="post" enctype="multipart/form-data">
    <?php wp_nonce_field('gff_upload_invoice', 'gff_nonce'); ?>
    <p>
        <label for="societe">Sélectionnez la société :</label><br>
        <select name="societe" id="societe" required>
            <?php foreach ($societes as $societe) : ?>
                <option value="<?php echo $societe->term_id; ?>"><?php echo esc_html($societe->name); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
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
        <label for="invoice_file">Télécharger votre facture (PDF) :</label><br>
        <input type="file" name="invoice_file" id="invoice_file" accept=".pdf" required>
    </p>
    <p>
        <input type="submit" name="submit_invoice" value="Envoyer" class="button">
    </p>
</form>