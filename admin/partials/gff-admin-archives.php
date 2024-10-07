<?php
if (! class_exists('GFF_Invoices_List_Table')) {
    require_once plugin_dir_path(dirname(__FILE__)) . 'class-gff-invoices-list-table.php';
}

// Traitement des actions spécifiques aux archives si nécessaire

// Initialiser la table
$factures_list_table = new GFF_Invoices_List_Table();
$factures_list_table->prepare_items();
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Factures Archivées</h1>
    <form method="post">
        <?php $factures_list_table->display(); ?>
    </form>
</div>