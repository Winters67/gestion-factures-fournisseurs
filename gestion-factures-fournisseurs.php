<?php
/*
Plugin Name: Gestion des Factures Fournisseurs
Description: Plugin pour centraliser les factures des fournisseurs avec accès sécurisé.
Version: 1.0
Author: Emmanuel
Text Domain: gestion-factures-fournisseurs
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit; // Empêcher l'accès direct
}

// Inclure les classes nécessaires
require_once plugin_dir_path(__FILE__) . 'includes/class-gff-activator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-gff-deactivator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-gff.php';
require_once plugin_dir_path(__FILE__) . 'includes/gff-email-handler.php';
include_once plugin_dir_path(__FILE__) . 'includes/gff-factures-history.php';

// Enregistrer les hooks d'activation et de désactivation
register_activation_hook(__FILE__, array('GFF_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('GFF_Deactivator', 'deactivate'));

// Ajouter le CSS public uniquement si le shortcode est présent
function gff_enqueue_public_styles()
{
    if (is_page() && has_shortcode(get_post()->post_content, 'tableau_factures')) {
        wp_enqueue_style('gff-public-styles', plugin_dir_url(__FILE__) . 'assets/css/public.css');
    }
}
add_action('wp_enqueue_scripts', 'gff_enqueue_public_styles');

// Ajouter le CSS admin pour l'interface d'administration
function gff_enqueue_admin_styles()
{
    wp_enqueue_style('gff-admin-styles', plugin_dir_url(__FILE__) . 'assets/css/admin.css');
}
add_action('admin_enqueue_scripts', 'gff_enqueue_admin_styles');

// Ajouter le JavaScript public uniquement si le shortcode est présent
function gff_enqueue_public_scripts()
{
    if (is_page() && has_shortcode(get_post()->post_content, 'tableau_factures')) {
        wp_enqueue_script('gff-public-scripts', plugin_dir_url(__FILE__) . 'assets/js/public.js', array(), '1.0', true);
        wp_localize_script('gff-public-scripts', 'submissionData', array(
            'submissionSuccess' => isset($submission_success) ? $submission_success : false
        ));
    }
}
add_action('wp_enqueue_scripts', 'gff_enqueue_public_scripts');

function gff_enqueue_admin_scripts($hook)
{
    if ($hook !== 'toplevel_page_gff-admin') { // Remplacez avec le hook approprié pour votre page d'admin
        return;
    }

    wp_enqueue_script('gff-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'gff_enqueue_admin_scripts');




// Exécuter le plugin principal
function run_gff_plugin()
{
    $plugin = new GFF();
    $plugin->run();
}
run_gff_plugin();

// Shortcode pour afficher le tableau des factures sur une page ou un article
function afficher_tableau_factures()
{
    // Inclure la classe du tableau si elle n'est pas déjà chargée
    if (!class_exists('GFF_Invoices_List_Table')) {
        include_once plugin_dir_path(__FILE__) . 'includes/class-gff-invoices-list-table.php';
    }

    // Initialiser la table
    $table = new GFF_Invoices_List_Table();
    $table->prepare_items(); // Charger les éléments

    // Capturer la sortie dans un buffer pour la retourner
    ob_start();
?>
    <div class="gff-invoices-table">
        <form method="get">
            <input type="hidden" name="page" value="gff-invoices">
            <?php
            // Afficher la barre de recherche et le tableau
            $table->search_box('Rechercher', 'search_id'); // Optionnel : Ajouter une barre de recherche
            $table->display(); // Afficher la table
            ?>
        </form>
    </div>
<?php

    // Retourner le contenu pour affichage sur la page
    return ob_get_clean();
}

// Enregistrer le shortcode
add_shortcode('tableau_factures', 'afficher_tableau_factures');
// Shortcode [tableau_factures]