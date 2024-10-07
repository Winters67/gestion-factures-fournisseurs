<?php

if (! class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class GFF_Invoices_List_Table extends WP_List_Table
{

    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'facture',
            'plural'   => 'factures',
            'ajax'     => false,
        ));
    }

    public function get_columns()
    {
        $columns = array(
            'cb'                => '<input type="checkbox" />',
            'id'                => 'ID',
            'fournisseur'       => 'Fournisseur',
            'societe'           => 'Société',
            'numero_commande'   => 'N° de commande',
            'numero_facture'    => 'N° de facture',
            'date_facture'      => 'Date de la facture',
            'montant_ht'        => 'Montant HT',
            'montant_ttc'       => 'Montant TTC',
            'statut'            => 'Statut',
            'date_submission'   => 'Date de soumission',
            'actions'           => 'Actions', // Ajout de la colonne 'Actions'
        );
        return $columns;
    }

    public function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gff_factures';

        $per_page = 20;
        $current_page = $this->get_pagenum();

        $orderby = ! empty($_REQUEST['orderby']) ? esc_sql($_REQUEST['orderby']) : 'date_submission';
        $order   = ! empty($_REQUEST['order']) ? esc_sql($_REQUEST['order']) : 'DESC';

        // Déterminer si on affiche les factures actives ou archivées
        $statut = isset($_GET['page']) && $_GET['page'] == 'gff-archives' ? 'archivée' : 'active';

        // Construire le WHERE en fonction des filtres
        $where = $statut == 'archivée' ? "WHERE statut = 'archivée'" : "WHERE statut != 'archivée'";

        if (isset($_REQUEST['filter_societe']) && ! empty($_REQUEST['filter_societe'])) {
            $societe_id = intval($_REQUEST['filter_societe']);
            $where     .= $wpdb->prepare(" AND societe_id = %d", $societe_id);
        }

        if (isset($_REQUEST['filter_month']) && ! empty($_REQUEST['filter_month'])) {
            $month = sanitize_text_field($_REQUEST['filter_month']);
            $where .= $wpdb->prepare(" AND DATE_FORMAT(date_facture, '%%Y-%%m') = %s", $month);
        }

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where");

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));

        $offset = ($current_page - 1) * $per_page;

        $this->items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name $where ORDER BY $orderby $order LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));
        error_log('Nombre de factures récupérées : ' . count($this->items));
    }

    public function extra_tablenav($which)
    {
        if ($which == 'top') {
            $selected_societe = isset($_REQUEST['filter_societe']) ? intval($_REQUEST['filter_societe']) : '';
            $selected_month   = isset($_REQUEST['filter_month']) ? sanitize_text_field($_REQUEST['filter_month']) : '';

            // Filtre de société
            $societes = get_terms(array(
                'taxonomy'   => 'gff_societe',
                'hide_empty' => false,
            ));

            echo '<div class="alignleft actions">';
            echo '<select name="filter_societe">';
            echo '<option value="">Toutes les sociétés</option>';
            foreach ($societes as $societe) {
                printf(
                    '<option value="%s"%s>%s</option>',
                    $societe->term_id,
                    selected($societe->term_id, $selected_societe, false),
                    $societe->name
                );
            }
            echo '</select>';

            // Filtre de mois
            global $wpdb;
            $table_name = $wpdb->prefix . 'gff_factures';
            $months     = $wpdb->get_results("SELECT DISTINCT DATE_FORMAT(date_facture, '%Y-%m') as month FROM $table_name WHERE statut = 'archivée' ORDER BY month DESC");

            echo '<select name="filter_month">';
            echo '<option value="">Tous les mois</option>';
            foreach ($months as $month) {
                printf(
                    '<option value="%s"%s>%s</option>',
                    $month->month,
                    selected($month->month, $selected_month, false),
                    date('F Y', strtotime($month->month . '-01'))
                );
            }
            echo '</select>';

            submit_button('Filtrer', '', 'filter_action', false);
            echo '</div>';
        }
    }

    public function get_views()
    {
        return array();
    }

    public function column_default($item, $column_name)
    {
        error_log('Affichage de la colonne : ' . $column_name . ' pour la facture ID : ' . $item->id);
        switch ($column_name) {
            case 'id':
                return $item->id;
            case 'fournisseur':
                $user_info = get_userdata($item->fournisseur_id);
                return $user_info ? esc_html($user_info->user_login) : 'Inconnu';
            case 'societe':
                $societe = get_term($item->societe_id, 'gff_societe');
                return $societe ? esc_html($societe->name) : 'Non définie';
            case 'numero_commande':
                return esc_html($item->numero_commande);
            case 'numero_facture':
                return esc_html($item->numero_facture);
            case 'date_facture':
                return esc_html($item->date_facture);
            case 'montant_ht':
                return number_format($item->montant_ht, 2, ',', ' ') . ' €';
            case 'montant_ttc':
                return number_format($item->montant_ttc, 2, ',', ' ') . ' €';
            case 'statut':
                return esc_html($item->statut);
            case 'date_submission':
                return esc_html($item->date_submission);
            case 'actions':
                return $this->column_actions($item); // Appeler la méthode column_actions()
            default:
                return '';
        }
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'id'                => array('id', true),
            'fournisseur'       => array('fournisseur_id', false),
            'societe'           => array('societe_id', false),
            'date_facture'      => array('date_facture', false),
            'montant_ht'        => array('montant_ht', false),
            'montant_ttc'       => array('montant_ttc', false),
            'statut'            => array('statut', false),
            'date_submission'   => array('date_submission', true),
        );
        return $sortable_columns;
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="facture_id[]" value="%s" />',
            $item->id
        );
    }

    public function column_id($item)
    {
        return $item->id;
    }

    public function column_actions($item)
    {
        $archive_nonce = wp_create_nonce('gff_archive_facture');

        $actions = array(
            'archiver' => sprintf(
                '<a href="?page=%s&action=%s&facture=%s&_wpnonce=%s" onclick="return confirm(\'Êtes-vous sûr de vouloir archiver cette facture ?\')">Archiver</a>',
                esc_attr($_REQUEST['page']),
                'archiver',
                absint($item->id),
                $archive_nonce
            ),
        );

        return $this->row_actions($actions);
    }
}
