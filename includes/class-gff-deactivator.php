<?php

class GFF_Deactivator
{

    public static function deactivate()
    {
        // Supprimer le rôle "Fournisseur"
        remove_role('fournisseur');
        // Optionnel : Supprimer la table des factures si nécessaire
    }
}
