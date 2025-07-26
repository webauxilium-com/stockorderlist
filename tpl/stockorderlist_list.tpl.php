<?php
/* Copyright (C) 2024 WebAuxilium <https://webauxilium.com> */

// Protection contre l'accès direct
if (!defined('NOREQUIREUSER')) die('Forbidden');

// Inclusion du CSS spécifique
print '<link rel="stylesheet" type="text/css" href="'.dol_buildpath('/stockorderlist/css/stockorderlist.css.php', 1).'">';

// Formulaire de recherche amélioré
print '<div class="stockorderlist-search-form">';
print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

print '<table class="noborder centpercent">';
print '<tr>';
print '<td class="fieldrequired">'.$langs->trans("SearchRef").'</td>';
print '<td>';
print '<input type="text" class="flat minwidth200" name="search_ref" value="'.dol_escape_htmltag($search_ref).'" placeholder="'.$langs->trans("SearchRef").'">';
print '</td>';
print '<td>';
print '<input type="submit" class="button buttongen" name="button_search" value="'.$langs->trans("Search").'">';
print '<input type="submit" class="button buttongen" name="cancel" value="'.$langs->trans("Reset").'" style="margin-left: 5px;">';
print '</td>';
print '</tr>';
print '</table>';

print '</form>';
print '</div>';

if ($products !== false && is_array($products)) {
    // Affichage du résumé
    $totals = calculateTotals($products);
    print '<div class="stockorderlist-summary">';
    print '<strong>'.$langs->trans("Summary").': </strong>';
    print count($products).' '.$langs->trans("Products");
    print ' | '.$langs->trans("TotalClientOrdered").': '.formatQuantity($totals['qty_client_ordered']);
    print ' | '.$langs->trans("TotalSupplierOrdered").': '.formatQuantity($totals['qty_supplier_ordered']);
    print ' | '.$langs->trans("TotalPhysicalStock").': '.formatQuantity($totals['stock_reel']);
    print '</div>';
    
    // Navigation pagination
    $param = '';
    if (!empty($search_ref)) $param .= '&search_ref='.urlencode($search_ref);
    
    print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', count($products), $nbtotalofrecords, 'stockorderlist@stockorderlist', 0, '', '', $limit);

    // Tableau des résultats amélioré
    print '<div class="div-table-responsive">';
    print '<table class="tagtable liste stockorderlist-table">'."\n";
    
    // En-têtes de colonnes
    print '<tr class="liste_titre">';
    print_liste_field_titre($langs->trans("Ref"), $_SERVER["PHP_SELF"], "p.ref", "", $param, '', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans("Label"), $_SERVER["PHP_SELF"], "p.label", "", $param, '', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans("ClientOrderQty"), $_SERVER["PHP_SELF"], "qty_client_ordered", "", $param, 'class="center"', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans("SupplierOrderQty"), $_SERVER["PHP_SELF"], "qty_supplier_ordered", "", $param, 'class="center"', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans("PhysicalStock"), $_SERVER["PHP_SELF"], "stock_reel", "", $param, 'class="center"', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans("ManufacturingOrders"), $_SERVER["PHP_SELF"], "nb_manufacturing_orders", "", $param, 'class="center"', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans("CalculatedStock"), $_SERVER["PHP_SELF"], "calculated_stock", "", $param, 'class="center"', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans("Actions"), $_SERVER["PHP_SELF"], "", "", $param, 'class="center stockorderlist-actions"', '', '');
    print '</tr>';
    
    // Lignes de données
    $var = false;
    foreach ($products as $product) {
        $var = !$var;
        
        print '<tr class="oddeven">';
        
        // Référence produit avec lien
        print '<td class="nowrap">';
        print '<a href="'.DOL_URL_ROOT.'/product/card.php?id='.$product['rowid'].'" target="_blank">';
        print img_object($langs->trans("ShowProduct"), 'product').' <strong>'.$product['ref'].'</strong>';
        print '</a>';
        print '</td>';
        
        // Libellé
        print '<td>'.dol_escape_htmltag($product['label']).'</td>';
        
        // Quantité en commande client
        print '<td class="center stockorderlist-qty">'.formatQuantity($product['qty_client_ordered']).'</td>';
        
        // Quantité en commande fournisseur
        print '<td class="center stockorderlist-qty">'.formatQuantity($product['qty_supplier_ordered']).'</td>';
        
        // Stock physique
        print '<td class="center stockorderlist-qty">'.formatQuantity($product['stock_reel']).'</td>';
        
        // Ordres de fabrication
        print '<td class="center">';
        if ($product['nb_manufacturing_orders'] > 0) {
            print '<a href="'.DOL_URL_ROOT.'/mrp/mo_list.php?search_fk_product='.$product['rowid'].'" target="_blank">';
            print '<span class="badge badge-info">'.$product['nb_manufacturing_orders'].'</span>';
            print '</a>';
        } else {
            print '0';
        }
        print '</td>';
        
        // Stock calculé avec couleur
        $calculated = $product['calculated_stock'];
        $css_class = getStockCalculatedClass($calculated);
        print '<td class="center stockorderlist-qty">';
        print '<span class="'.$css_class.'">';
        print formatQuantity($calculated);
        print '</span>';
        print '</td>';
        
        // Actions
        print '<td class="center stockorderlist-actions">';
        
        // Liens vers commandes clients
        if (!empty($product['client_orders'])) {
            print generateOrderLinks($product['client_orders'], 'customer');
        }
        
        // Liens vers commandes fournisseurs
        if (!empty($product['supplier_orders'])) {
            print generateOrderLinks($product['supplier_orders'], 'supplier');
        }
        
        print '</td>';
        print '</tr>';
    }
    
    // Ligne de totaux
    if (!empty($products)) {
        print '<tr class="liste_total">';
        print '<td><strong>'.$langs->trans("Total").'</strong></td>';
        print '<td>&nbsp;</td>';
        print '<td class="center stockorderlist-qty"><strong>'.formatQuantity($totals['qty_client_ordered']).'</strong></td>';
        print '<td class="center stockorderlist-qty"><strong>'.formatQuantity($totals['qty_supplier_ordered']).'</strong></td>';
        print '<td class="center stockorderlist-qty"><strong>'.formatQuantity($totals['stock_reel']).'</strong></td>';
        print '<td class="center"><strong>'.$totals['nb_manufacturing_orders'].'</strong></td>';
        print '<td class="center stockorderlist-qty"><strong><span class="'.getStockCalculatedClass($totals['calculated_stock']).'">'.formatQuantity($totals['calculated_stock']).'</span></strong></td>';
        print '<td>&nbsp;</td>';
        print '</tr>';
    }
    
    if (empty($products)) {
        print '<tr class="oddeven"><td colspan="8" class="opacitymedium center">'.$langs->trans("NoRecordFound").'</td></tr>';
    }
    
    print '</table>';
    print '</div>';
    
} else {
    print '<div class="warning">'.$langs->trans("ErrorLoadingData").'</div>';
}
?>