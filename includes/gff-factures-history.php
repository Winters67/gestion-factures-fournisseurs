<?php
function gff_display_fournisseur_invoices()
{
    if (!is_user_logged_in()) {
        return '<p>Vous devez être connecté pour voir l\'historique de vos factures.</p>';
    }

    $current_user = wp_get_current_user();

    // Vérifie que l'utilisateur est un fournisseur
    if (!in_array('fournisseur', $current_user->roles)) {
        return '<p>Vous n\'êtes pas autorisé à accéder à cette page.</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'gff_factures';

    // Tableau associatif pour les statuts
    $statuts = array(
        'en_attente_validation' => 'En attente de validation',
        'doublon_detecte'       => 'Doublon détecté',
        'en_attente_paiement'   => 'En attente de règlement',
        'payee'                 => 'Réglement effectuée',
        'facture_rejetee'       => 'Facture rejetée',
        'archivee'              => 'Classée'
    );

    // Récupérer les factures du fournisseur connecté
    $factures = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE fournisseur_id = %d ORDER BY date_facture DESC",
            $current_user->ID
        )
    );

    if (empty($factures)) {
        return '<p>Aucune facture trouvée.</p>';
    }

    // Générer le tableau d'affichage des factures avec un titre
    $output = '<h2>Historique de vos factures</h2>';
    $output .= '<table class="gff-factures-table">';
    $output .= '<thead><tr><th>N° de facture</th><th>Date</th><th>Montant HT</th><th>Montant TTC</th><th>Pièce jointe</th><th>Statut</th><th>Commentaire de l\'administrateur</th></tr></thead><tbody>';

    foreach ($factures as $facture) {
        $output .= '<tr>';
        $output .= '<td>' . esc_html($facture->numero_facture) . '</td>';
        $output .= '<td>' . esc_html($facture->date_facture) . '</td>';
        $output .= '<td>' . number_format($facture->montant_ht, 2, ',', ' ') . ' €</td>';
        $output .= '<td>' . number_format($facture->montant_ttc, 2, ',', ' ') . ' €</td>';

        // Vérifier si une pièce jointe est associée et récupérer l'URL du fichier
        $file_url = !empty($facture->fichier) ? esc_url($facture->fichier) : '';
        if ($file_url) {
            $output .= '<td><a href="' . $file_url . '" target="_blank">Télécharger</a></td>';
        } else {
            $output .= '<td>Aucune pièce jointe</td>';
        }

        // Afficher le texte du statut
        $output .= '<td>' . (isset($statuts[$facture->statut]) ? esc_html($statuts[$facture->statut]) : 'Statut inconnu') . '</td>';

        // Afficher le commentaire de l'administrateur s'il existe
        $output .= '<td>' . (!empty($facture->commentaire_admin) ? esc_html($facture->commentaire_admin) : 'Pas de commentaire') . '</td>';

        $output .= '</tr>';
    }

    $output .= '</tbody></table>';

    return $output;
}

add_shortcode('gff_fournisseur_factures', 'gff_display_fournisseur_invoices');
