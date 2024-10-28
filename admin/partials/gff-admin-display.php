<?php

if (! class_exists('GFF_Invoices_List_Table')) {
    require_once plugin_dir_path(dirname(__FILE__)) . 'class-gff-invoices-list-table.php';
}

// Traitement des actions (changement de statut et suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Mise à jour du statut
        if ($_POST['action'] === 'update_facture_status') {
            error_log('Action update_facture_status détectée');

            // Vérifier le nonce
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'gff_update_facture_status')) {
                error_log('Nonce vérifié pour update_facture_status');

                // Vérifier les capacités de l'utilisateur
                if (current_user_can('manage_options')) {
                    error_log('Utilisateur a les capacités nécessaires pour update_facture_status');
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'gff_factures';

                    // Récupérer les données
                    $facture_id = isset($_POST['facture_id']) ? intval($_POST['facture_id']) : 0;
                    $new_statut = isset($_POST['new_statut']) ? sanitize_text_field($_POST['new_statut']) : '';

                    error_log("Mise à jour de la facture ID: $facture_id vers le statut: $new_statut");

                    if ($facture_id > 0 && ! empty($new_statut)) {
                        // Mettre à jour le statut de la facture
                        $updated = $wpdb->update(
                            $table_name,
                            array('statut' => $new_statut),
                            array('id' => $facture_id),
                            array('%s'),
                            array('%d')
                        );

                        if ($updated !== false) {
                            error_log("Statut mis à jour avec succès pour la facture ID $facture_id");
                            echo '<div class="updated"><p>Le statut de la facture a été mis à jour avec succès.</p></div>';
                        } else {
                            error_log("Échec de la mise à jour du statut pour la facture ID $facture_id");
                            echo '<div class="error"><p>Une erreur est survenue lors de la mise à jour du statut.</p></div>';
                        }
                    }
                } else {
                    error_log("Utilisateur n'a pas les capacités nécessaires pour update_facture_status");
                    echo '<div class="error"><p>Vous n\'avez pas les permissions nécessaires pour effectuer cette action.</p></div>';
                }
            } else {
                error_log("Vérification du nonce échouée pour update_facture_status");
                echo '<div class="error"><p>Action non autorisée.</p></div>';
            }
        }

        // Suppression de la facture
        elseif ($_POST['action'] === 'delete_facture') {
            error_log('Action delete_facture détectée');

            // Vérifier le nonce
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'gff_delete_facture')) {
                error_log('Nonce vérifié pour delete_facture');

                // Vérifier les capacités de l'utilisateur
                if (current_user_can('manage_options')) {
                    error_log('Utilisateur a les capacités nécessaires pour delete_facture');
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'gff_factures';

                    // Récupérer les données
                    $facture_id = isset($_POST['facture_id']) ? intval($_POST['facture_id']) : 0;

                    error_log("Suppression de la facture ID: $facture_id");

                    if ($facture_id > 0) {
                        // Supprimer la facture de la base de données
                        $deleted = $wpdb->delete(
                            $table_name,
                            array('id' => $facture_id),
                            array('%d')
                        );

                        if ($deleted !== false) {
                            error_log("Facture ID $facture_id supprimée avec succès");
                            echo '<div class="updated"><p>La facture a été supprimée avec succès.</p></div>';
                        } else {
                            error_log("Échec de la suppression de la facture ID $facture_id");
                            echo '<div class="error"><p>Une erreur est survenue lors de la suppression de la facture.</p></div>';
                        }
                    }
                } else {
                    error_log("Utilisateur n'a pas les capacités nécessaires pour delete_facture");
                    echo '<div class="error"><p>Vous n\'avez pas les permissions nécessaires pour effectuer cette action.</p></div>';
                }
            } else {
                error_log("Vérification du nonce échouée pour delete_facture");
                echo '<div class="error"><p>Action non autorisée.</p></div>';
            }
        }
    }
}

// Initialiser la table
$factures_list_table = new GFF_Invoices_List_Table();
$factures_list_table->prepare_items();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Tableau de Bord des Factures Fournisseurs</h1>
    <?php $factures_list_table->display(); ?>
</div>