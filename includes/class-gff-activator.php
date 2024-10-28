<?php

class GFF_Activator
{

    public static function activate()
    {
        self::create_table();
        self::add_roles();
    }

    private static function create_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'gff_factures';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            fournisseur_id bigint(20) NOT NULL,
            societe_id bigint(20) NOT NULL,
            numero_commande varchar(50) NOT NULL,
            numero_facture varchar(50) NOT NULL,
            date_facture date NOT NULL,
            montant_ht decimal(10,2) NOT NULL,
            montant_ttc decimal(10,2) NOT NULL,
            part_transport_ht decimal(10,2) NOT NULL,  /* Nouvelle colonne */
            fichier varchar(255) NOT NULL,
            statut varchar(20) DEFAULT 'en attente' NOT NULL,
            export_securise tinyint(1) DEFAULT 0 NOT NULL COMMENT 'Pour export sécurisé',
            date_submission datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }



    private static function add_roles()
    {
        add_role(
            'fournisseur',
            'Fournisseur',
            array(
                'read'         => true,
                'upload_files' => true,
            )
        );
    }
}
