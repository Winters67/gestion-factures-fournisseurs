<?php

class GFF_Public
{

    public function enqueue_styles()
    {
        wp_enqueue_style(
            'gff-public-css',
            plugin_dir_url(__FILE__) . '../assets/css/public.css',
            array(),
            '1.0.0',
            'all'
        );
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script(
            'gff-public-js',
            plugin_dir_url(__FILE__) . '../assets/js/public.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }

    public function display_supplier_dashboard()
    {
        ob_start();
        include_once plugin_dir_path(__FILE__) . 'partials/gff-public-display.php';
        return ob_get_clean();
    }
}
