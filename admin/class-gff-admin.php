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
            'edit_posts', // Capacité de l'utilisateur pour accéder à cette page
            'gff-admin', // Slug de la page parent
            array($this, 'display_admin_dashboard'),
            'dashicons-media-document',
            6
        );

        add_submenu_page(
            'gff-admin', // Le slug de la page parent
            'Factures Archivées',
            'Archives',
            'edit_posts', // Capacité de l'utilisateur pour accéder à cette page
            'gff-archives', // Slug du sous-menu
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

        // Redirection après archivage
        wp_redirect(admin_url('admin.php?page=gff-admin&archived=success'));
        exit;
    }

    public function enqueue_styles()
    {
        error_log('enqueue_styles called'); // Test pour voir si la méthode est appelée
        wp_enqueue_style(
            'gff-admin-css',
            plugin_dir_url(__FILE__) . 'assets/css/admin.css',
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
