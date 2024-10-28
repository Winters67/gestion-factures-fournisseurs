<?php

if (! class_exists('GFF_Invoices_List_Table')) {
    require_once plugin_dir_path(dirname(__FILE__)) . 'class-gff-invoices-list-table.php';
}

// Traitement de la suppression d'une seule facture
if (isset($_POST['action']) && $_POST['action'] === 'delete_facture') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gff_factures';

    // Vérifier le nonce pour sécuriser la soumission
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'gff_delete_facture')) {
        wp_die('Nonce verification failed.');
    }

    // Vérifier si l'ID de la facture est présent
    if (isset($_POST['facture_id']) && is_array($_POST['facture_id'])) {
        $facture_id = intval($_POST['facture_id'][0]);

        // Supprimer la facture dans la base de données
        $delete_query = $wpdb->prepare("DELETE FROM $table_name WHERE id = %d", $facture_id);
        $result = $wpdb->query($delete_query);

        if ($result !== false) {
            echo '<div class="updated"><p>La facture a été supprimée avec succès.</p></div>';
        } else {
            echo '<div class="error"><p>Erreur lors de la suppression de la facture.</p></div>';
        }
    } else {
        echo '<div class="error"><p>ID de facture invalide.</p></div>';
    }
}


// Traitement de la suppression des factures archivées
if (isset($_POST['delete_selected'])) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gff_factures';

    // Vérifier le nonce pour sécuriser la soumission
    if (!check_admin_referer('gff_bulk_delete_factures')) {
        wp_die('Nonce verification failed.');
    }

    // Vérifiez si $_POST['facture_id'] est un tableau
    if (isset($_POST['facture_id']) && is_array($_POST['facture_id'])) {
        // Récupérer les IDs des factures à supprimer
        $facture_ids = array_map('intval', $_POST['facture_id']);

        // Debug : Vérifier ce qui est transmis
        error_log('Facture IDs reçus : ' . print_r($facture_ids, true));

        // Si aucune facture sélectionnée
        if (empty($facture_ids)) {
            echo '<div class="error"><p>Aucune facture valide sélectionnée pour suppression.</p></div>';
        } else {
            // Préparer la requête SQL
            $in_placeholders = implode(',', array_fill(0, count($facture_ids), '%d'));
            $query = $wpdb->prepare("DELETE FROM $table_name WHERE id IN ($in_placeholders)", ...$facture_ids); // Utiliser "..." pour passer les IDs

            // Debug : Vérifier la requête SQL générée
            error_log('Requête SQL : ' . $query);

            // Exécuter la suppression
            $result = $wpdb->query($query);

            // Vérification du résultat de la requête
            if ($result !== false) {
                echo '<div class="updated"><p>Les factures sélectionnées ont été supprimées avec succès.</p></div>';
            } else {
                echo '<div class="error"><p>Erreur lors de la suppression des factures.</p></div>';
                error_log('Erreur SQL : ' . $wpdb->last_error);
            }
        }
    } else {
        echo '<div class="error"><p>Aucune facture sélectionnée.</p></div>';
        error_log('Aucune facture sélectionnée ou $_POST[\'facture_id\'] n\'est pas un tableau.');
    }
}

// Initialiser la table des factures archivées
$factures_list_table = new GFF_Invoices_List_Table();
$factures_list_table->prepare_items();

?>
<div class="wrap">
    <h1 class="wp-heading-inline">Factures Archivées</h1>
    <form method="post">
        <?php
        // Afficher la table des factures archivées
        $factures_list_table->display();

        // Ajouter un nonce pour sécuriser le formulaire
        wp_nonce_field('gff_bulk_delete_factures');
        ?>

        <!-- Ajouter un bouton pour la suppression des factures sélectionnées -->
        <input type="submit" name="delete_selected" class="button button-danger" value="Supprimer les factures sélectionnées">
    </form>
</div>