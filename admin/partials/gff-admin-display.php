<?php
if (! class_exists('GFF_Invoices_List_Table')) {
    require_once plugin_dir_path(dirname(__FILE__)) . 'class-gff-invoices-list-table.php';
}

// Traitement des actions (mise à jour du statut, archivage, etc.)
global $wpdb;
$table_name = $wpdb->prefix . 'gff_factures';

// Mise à jour du statut de la facture
if (isset($_POST['mettre_a_jour_statut']) && isset($_POST['facture_id'])) {
    if (isset($_POST['mettre_a_jour_statut_nonce']) && wp_verify_nonce($_POST['mettre_a_jour_statut_nonce'], 'mettre_a_jour_statut_action')) {
        $facture_id = intval($_POST['facture_id']);
        $nouveau_statut = sanitize_text_field($_POST['nouveau_statut']);

        $wpdb->update(
            $table_name,
            array('statut' => $nouveau_statut),
            array('id' => $facture_id)
        );
        echo '<div class="updated"><p>Statut mis à jour avec succès.</p></div>';
    }
}

// Traitement de l'action d'archivage
if (isset($_GET['action']) && $_GET['action'] == 'archiver' && isset($_GET['facture']) && isset($_GET['_wpnonce'])) {
    if (wp_verify_nonce($_GET['_wpnonce'], 'gff_archive_facture')) {
        $facture_id = intval($_GET['facture']);
        $wpdb->update(
            $table_name,
            array('statut' => 'archivée'),
            array('id' => $facture_id)
        );
        echo '<div class="updated"><p>La facture a été archivée avec succès.</p></div>';
    }
}

// Initialiser la table
$factures_list_table = new GFF_Invoices_List_Table();
$factures_list_table->prepare_items();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Tableau de Bord des Factures Fournisseurs</h1>
    <form method="post">
        <?php $factures_list_table->display(); ?>
    </form>
</div>