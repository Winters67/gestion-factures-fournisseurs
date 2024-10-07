<?php

class GFF
{

    public function __construct()
    {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies()
    {
        // Inclure les fichiers nécessaires
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-gff-activator.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-gff-deactivator.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-gff-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-gff-public.php';
    }

    private function define_admin_hooks()
    {
        $plugin_admin = new GFF_Admin();
        add_action('admin_menu', array($plugin_admin, 'add_plugin_admin_menu'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
    }

    private function define_public_hooks()
    {
        $plugin_public = new GFF_Public();
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_scripts'));
        add_shortcode('gff_supplier_dashboard', array($plugin_public, 'display_supplier_dashboard'));

        // Enregistrer la taxonomie lors de l'initialisation
        add_action('init', array($this, 'register_taxonomy'));
    }

    public function run()
    {
        // Code à exécuter lors du démarrage du plugin
    }

    public function register_taxonomy()
    {
        $labels = array(
            'name'              => 'Sociétés',
            'singular_name'     => 'Société',
            'search_items'      => 'Rechercher des sociétés',
            'all_items'         => 'Toutes les sociétés',
            'edit_item'         => 'Modifier la société',
            'update_item'       => 'Mettre à jour la société',
            'add_new_item'      => 'Ajouter une nouvelle société',
            'new_item_name'     => 'Nom de la nouvelle société',
            'menu_name'         => 'Sociétés',
        );

        register_taxonomy(
            'gff_societe',
            array('post'), // Associer la taxonomie aux articles pour qu'elle apparaisse dans le menu
            array(
                'hierarchical'      => true,
                'labels'            => $labels,
                'show_ui'           => true,
                'show_in_menu'      => true, // S'assurer que c'est défini à true
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array('slug' => 'societe'),
            )
        );
    }
}
