<?php

class GFF_Admin
{

    public function __construct()
    {
        // Ajouter les hooks nécessaires
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function add_plugin_admin_menu()
    {
        add_menu_page(
            'Gestion des Factures',
            'Factures Fournisseurs',
            'manage_options',
            'gff-admin',
            array($this, 'display_admin_dashboard'),
            'dashicons-media-document',
            6
        );

        add_submenu_page(
            'gff-admin',
            'Factures Archivées',
            'Archives',
            'manage_options',
            'gff-archives',
            array($this, 'display_archives_page')
        );
    }

    public function display_admin_dashboard()
    {
        // Vérifier et traiter l'action d'archivage
        if (isset($_GET['action']) && $_GET['action'] == 'archiver' && isset($_GET['facture']) && isset($_GET['_wpnonce'])) {
            if (wp_verify_nonce($_GET['_wpnonce'], 'gff_archive_facture')) {
                $facture_id = intval($_GET['facture']);
                $this->archiver_facture($facture_id);
            }
        }

        include_once plugin_dir_path(__FILE__) . 'partials/gff-admin-display.php';
    }

    public function display_archives_page()
    {
        include_once plugin_dir_path(__FILE__) . 'partials/gff-admin-archives.php';
    }

    private function archiver_facture($facture_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gff_factures';

        // Mettre à jour le statut de la facture à "archivée"
        $wpdb->update(
            $table_name,
            array('statut' => 'archivée'),
            array('id' => $facture_id)
        );

        echo '<div class="updated"><p>La facture a été archivée avec succès.</p></div>';
    }

    public function enqueue_styles()
    {
        wp_enqueue_style(
            'gff-admin-css',
            plugins_url('assets/css/admin.css', __FILE__),
            array(),
            '1.0.0',
            'all'
        );
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script(
            'gff-admin-js',
            plugins_url('assets/js/admin.js', __FILE__),
            array('jquery'),
            '1.0.0',
            true
        );
    }
}
