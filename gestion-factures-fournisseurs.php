<?php
/*
Plugin Name: Gestion des Factures Fournisseurs
Description: Plugin pour centraliser les factures des fournisseurs avec accès sécurisé.
Version: 1.0
Author: Emmanuel
Text Domain: gestion-factures-fournisseurs
Domain Path: /languages
*/

if (! defined('ABSPATH')) {
    exit; // Empêcher l'accès direct
}

// Inclure les classes nécessaires
require_once plugin_dir_path(__FILE__) . 'includes/class-gff-activator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-gff-deactivator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-gff.php';

// Enregistrer les hooks d'activation et de désactivation
register_activation_hook(__FILE__, array('GFF_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('GFF_Deactivator', 'deactivate'));

// Exécuter le plugin
function run_gff_plugin()
{
    $plugin = new GFF();
    $plugin->run();
}
run_gff_plugin();
