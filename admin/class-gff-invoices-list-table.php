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
            'part_transport_ht' => 'Part de transport HT',
            'montant_ttc'       => 'Montant TTC',
            'fichier'           => 'PDF',
            'statut'            => 'Statut',
            'date_submission'   => 'Date de soumission',
            'export_securise'   => 'Export',
            'actions'           => 'Actions',
        );
        return $columns;
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'id'                => array('id', true),
            'fournisseur'       => array('fournisseur_id', false),
            'societe'           => array('societe_id', false),
            'date_facture'      => array('date_facture', false),
            'montant_ht'        => array('montant_ht', false),
            'part_transport_ht' => array('part_transport_ht', false),
            'montant_ttc'       => array('montant_ttc', false),
            'statut'            => array('statut', false),
            'date_submission'   => array('date_submission', true),
        );
        return $sortable_columns;
    }

    public function prepare_items()
    {
        // Traiter les actions groupées
        $this->process_bulk_action();

        // Obtenir les colonnes, les colonnes masquées et les colonnes triables
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

        // Définir la propriété _column_headers
        $this->_column_headers = array($columns, $hidden, $sortable);

        global $wpdb;
        $table_name = $wpdb->prefix . 'gff_factures';

        $per_page     = 20;
        $current_page = $this->get_pagenum();

        // Liste des colonnes triables et correspondance avec les champs de la base de données
        $columns_map = array(
            'id'                => 'id',
            'fournisseur'       => 'fournisseur_id',
            'societe'           => 'societe_id',
            'date_facture'      => 'date_facture',
            'montant_ht'        => 'montant_ht',
            'montant_ttc'       => 'montant_ttc',
            'statut'            => 'statut',
            'date_submission'   => 'date_submission',
        );

        // Par défaut
        $orderby = 'date_submission';
        $order   = 'DESC';

        // Valider le 'orderby'
        if (! empty($_REQUEST['orderby']) && array_key_exists($_REQUEST['orderby'], $columns_map)) {
            $orderby = $columns_map[$_REQUEST['orderby']];
        }

        // Valider le 'order'
        if (! empty($_REQUEST['order']) && in_array(strtoupper($_REQUEST['order']), array('ASC', 'DESC'))) {
            $order = strtoupper($_REQUEST['order']);
        }

        // Déterminer si on affiche les factures actives ou archivées
        $statut_filter = isset($_GET['page']) && $_GET['page'] == 'gff-archives' ? 'archivée' : 'active';

        // Construire le WHERE en fonction des filtres
        $where = $statut_filter == 'archivée' ? "WHERE statut = 'archivée'" : "WHERE statut != 'archivée'";

        if (isset($_REQUEST['filter_societe']) && ! empty($_REQUEST['filter_societe'])) {
            $societe_id = intval($_REQUEST['filter_societe']);
            $where     .= $wpdb->prepare(" AND societe_id = %d", $societe_id);
        }

        if (isset($_REQUEST['filter_month']) && ! empty($_REQUEST['filter_month'])) {
            $month = sanitize_text_field($_REQUEST['filter_month']);
            $where .= $wpdb->prepare(" AND DATE_FORMAT(date_facture, '%%Y-%%m') = %s", $month);
        }

        // Calculer le total des éléments
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where");

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));

        $offset = ($current_page - 1) * $per_page;

        // Requête pour récupérer les éléments
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name $where ORDER BY $orderby $order LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );

        $this->items = $wpdb->get_results($query, OBJECT);
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
                return $item->id;
            case 'fournisseur':
                $fournisseur_nom = get_field('Lbl_Frn', 'user_' . $item->fournisseur_id);
                return $fournisseur_nom ? esc_html($fournisseur_nom) : 'Inconnu';
            case 'societe':
                $code_entreprise = get_field('Id_Frn', 'user_' . $item->fournisseur_id);
                return $code_entreprise ? esc_html($code_entreprise) : 'Non définie';
            case 'numero_commande':
                return esc_html($item->numero_commande);
            case 'numero_facture':
                return esc_html($item->numero_facture);
            case 'date_facture':
                return esc_html($item->date_facture);
            case 'montant_ht':
                return number_format($item->montant_ht, 2, ',', ' ') . ' €';
            case 'part_transport_ht':
                return number_format($item->part_transport_ht, 2, ',', ' ') . ' €';
            case 'montant_ttc':
                return number_format($item->montant_ttc, 2, ',', ' ') . ' €';
            case 'fichier':
                return sprintf(
                    '<a href="%s" target="_blank">Télécharger le PDF</a>',
                    esc_url($item->fichier)
                );
            case 'statut':
                return esc_html($item->statut);
            case 'date_submission':
                return esc_html($item->date_submission);
            case 'actions':
                return $this->column_actions($item);
            case 'export_securise':
                return $item->export_securise ? '1' : '0';
            default:
                return '';
        }
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="facture_id[]" value="%s" />',
            $item->id
        );
    }

    public function column_actions($item)
    {
        $nonce_update = wp_create_nonce('gff_update_facture_status');
        $nonce_delete = wp_create_nonce('gff_delete_facture');

        // Définir les statuts disponibles
        $statuts = array(
            'en_attente_validation' => 'En attente de validation',
            'doublon_detecte'       => 'Doublon détecté',
            'en_attente_paiement'   => 'En attente de règlement',
            'payee'                 => 'Réglement effectuée',
            'facture_rejetee'       => 'Facture rejetée',
            'archivee'              => 'Classée'
        );

        $current_statut = $item->statut;

        // Commencer la construction du formulaire pour mettre à jour le statut
        $form_update = '';

        if ($current_statut !== 'archivée' && (!isset($_GET['page']) || $_GET['page'] != 'gff-archives')) {
            $form_update = sprintf(
                '<form method="post" action="%s" style="display:inline;margin-right:5px;">',
                esc_url(admin_url('admin.php?page=gff-admin'))
            );

            $form_update .= sprintf(
                '<input type="hidden" name="facture_id" value="%s" />',
                esc_attr($item->id)
            );
            $form_update .= '<input type="hidden" name="action" value="update_facture_status" />';
            $form_update .= sprintf('<input type="hidden" name="_wpnonce" value="%s" />', $nonce_update);

            // Ajouter le menu déroulant pour sélectionner le nouveau statut
            $form_update .= '<select name="new_statut" onchange="if(this.value == \'facture_rejetee\') { document.getElementById(\'rejection_comment_' . $item->id . '\').style.display = \'inline-block\'; } else { document.getElementById(\'rejection_comment_' . $item->id . '\').style.display = \'none\'; }">';
            foreach ($statuts as $value => $label) {
                $selected = selected($current_statut, $value, false);
                $form_update .= sprintf('<option value="%s" %s>%s</option>', esc_attr($value), $selected, esc_html($label));
            }
            $form_update .= '</select>';

            // Ajouter le champ de commentaire qui s'affiche seulement pour "facture rejetée"
            $form_update .= '<input type="text" name="rejection_comment" id="rejection_comment_' . $item->id . '" placeholder="Ajouter un commentaire" style="display:none; margin-left:5px;" />';
            $form_update .= '<button type="button" id="submit_comment_' . $item->id . '" style="display:none; margin-left:5px; background:none; border:none; cursor:pointer;" title="Envoyer le commentaire">
                <img src="' . plugin_dir_url(__FILE__) . '../images/paper-plane-regular.svg" alt="Envoyer" style="width:16px; height:16px;">
            </button>';

            // Fermer le formulaire
            $form_update .= '</form>';
        }

        // Initialiser le formulaire de suppression
        $form_delete = '';
        if ($current_statut === 'archivée' && isset($_GET['page']) && $_GET['page'] == 'gff-archives') {
            $form_delete = sprintf(
                '<form method="post" action="%s" style="display:inline;">',
                esc_url(admin_url('admin.php?page=gff-archives&action=-1'))
            );

            $form_delete .= sprintf(
                '<input type="hidden" name="facture_id[]" value="%s" />',
                esc_attr($item->id)
            );
            $form_delete .= '<input type="hidden" name="action2" value="delete_facture" />';
            $form_delete .= sprintf('<input type="hidden" name="_wpnonce" value="%s" />', $nonce_delete);
            $form_delete .= '<input type="submit" value="Supprimer" class="button button-danger" onclick="return confirm(\'Etes-vous sûr de vouloir supprimer cette facture ? Cette action est irréversible.\')" />';
            $form_delete .= '</form>';
        }

        // Retourner soit le formulaire de mise à jour de statut, soit celui de suppression, ou les deux
        return $form_update . $form_delete;
    }


    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Supprimer'
        );
        return $actions;
    }

    public function process_bulk_action()
    {
        $action = $this->current_action();

        // Gestion de la suppression
        if ('delete' === $action || 'delete_facture' === $action) {
            // Vérification du nonce pour sécuriser l'action
            $nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';

            if ('delete' === $action) {
                // Nonce pour la suppression en masse
                if (!wp_verify_nonce($nonce, 'bulk-' . $this->_args['plural'])) {
                    wp_die('Nonce verification failed');
                }
            } elseif ('delete_facture' === $action) {
                // Nonce pour la suppression individuelle
                if (!wp_verify_nonce($nonce, 'gff_delete_facture')) {
                    wp_die('Nonce verification failed');
                }
            }

            // Récupérer les ID des factures à supprimer
            $facture_ids = isset($_REQUEST['facture_id']) ? $_REQUEST['facture_id'] : array();

            if (!is_array($facture_ids)) {
                $facture_ids = array($facture_ids); // S'assurer qu'on a un tableau
            }

            // Suppression des factures sélectionnées
            global $wpdb;
            $table_name = $wpdb->prefix . 'gff_factures'; // Nom de la table des factures
            foreach ($facture_ids as $id) {
                $id = intval($id); // S'assurer que l'ID est un entier
                $wpdb->delete($table_name, array('id' => $id), array('%d'));
            }

            // Redirection après suppression pour éviter la resoumission du formulaire
            $redirect_url = remove_query_arg(array('_wpnonce', 'action', 'action2', 'facture_id'));
            wp_redirect($redirect_url);
            exit;
        }

        // Gestion de la mise à jour de statut avec envoi d'email pour "facture rejetée"
        if ('update_facture_status' === $action) {
            $nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';
            if (!wp_verify_nonce($nonce, 'gff_update_facture_status')) {
                wp_die('Nonce verification failed');
            }

            $facture_id = isset($_POST['facture_id']) ? intval($_POST['facture_id']) : 0;
            $new_statut = sanitize_text_field($_POST['new_statut']);
            $comment = isset($_POST['rejection_comment']) ? sanitize_text_field($_POST['rejection_comment']) : '';

            global $wpdb;
            $table_name = $wpdb->prefix . 'gff_factures';

            // Mise à jour du statut dans la base de données
            $wpdb->update(
                $table_name,
                array('statut' => $new_statut),
                array('id' => $facture_id),
                array('%s'),
                array('%d')
            );

            // Appeler la fonction d'envoi d'email uniquement si le statut est "facture rejetée"
            if ($new_statut === 'facture_rejetee') {
                gff_envoyer_email_facture_rejetee($facture_id, $comment);
            }

            // Redirection pour éviter la resoumission du formulaire
            wp_redirect(remove_query_arg(array('_wpnonce', 'action', 'facture_id')));
            exit;
        }
    }


    public function display_rows_or_placeholder()
    {
        parent::display_rows_or_placeholder();
    }

    public function display_rows()
    {
        parent::display_rows();
    }

    public function single_row($item)
    {
        // Définir la couleur de fond en fonction du statut
        $background_color = '';
        switch ($item->statut) {
            case 'payee':
                $background_color = '#d4edda'; // Vert clair pour "Réglement effectuée"
                break;
            case 'en_attente_validation':
                $background_color = '#cce5ff'; // Bleu clair pour "En attente de validation"
                break;
            case 'en_attente_paiement':
                $background_color = '#f8d7da'; // Rose clair pour "En attente de règlement"
                break;
            case 'facture_rejetee':
                $background_color = '#f5c6cb'; // Rose plus foncé pour "Facture rejetée"
                break;
            case 'archivee':
                $background_color = '#e2e3e5'; // Gris pour "Classée"
                break;
            case 'doublon_detecte':
                $background_color = '#f5c6cb'; // Rose plus foncé pour "Doublon détecté"
                break;
            default:
                $background_color = '#cfe2ff'; // Bleu très clair pour les statuts non spécifiés
                break;
        }

        // Ajouter l'ID et appliquer le style directement
        $row_id = 'facture-' . esc_attr($item->id); // ID unique pour chaque ligne
        echo '<tr id="' . $row_id . '" style="background-color: ' . $background_color . ';">';
        $this->single_row_columns($item);
        echo '</tr>';
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
            $months     = $wpdb->get_results("SELECT DISTINCT DATE_FORMAT(date_facture, '%Y-%m') as month FROM $table_name ORDER BY month DESC");

            echo '<select name="filter_month">';
            echo '<option value="">Tous les mois</option>';
            foreach ($months as $month) {
                printf(
                    '<option value="%s"%s>%s</option>',
                    $month->month,
                    selected($month->month, $selected_month, false),
                    date_i18n('F Y', strtotime($month->month . '-01'))
                );
            }
            echo '</select>';

            submit_button('Filtrer', '', 'filter_action', false);
            echo '</div>';
        }
    }
}
