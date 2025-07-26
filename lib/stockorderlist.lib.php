<?php
/* Copyright (C) 2024 WebAuxilium <https://webauxilium.com> */

/**
 * Formate une quantité pour l'affichage
 */
function formatQuantity($qty, $decimals = 0)
{
    if ($qty == 0) return '0';
    return price($qty, 0, '', 0, $decimals);
}

/**
 * Retourne la classe CSS selon la valeur du stock calculé
 */
function getStockCalculatedClass($calculated_stock)
{
    if ($calculated_stock < 0) {
        return 'stockorderlist-negative';
    } elseif ($calculated_stock > 0) {
        return 'stockorderlist-positive';
    }
    return 'stockorderlist-neutral';
}

/**
 * Génère les liens vers les commandes
 */
function generateOrderLinks($orders, $type = 'customer')
{
    global $langs;
    
    if (empty($orders) || !is_array($orders)) {
        return '';
    }
    
    $links = array();
    foreach ($orders as $order_info) {
        if (empty($order_info)) continue;
        
        $parts = explode(':', $order_info);
        if (count($parts) < 2) continue;
        
        $ref = $parts[0];
        $client_name = $parts[1];
        
        if ($type == 'customer') {
            $url = DOL_URL_ROOT.'/commande/card.php?ref='.urlencode($ref);
            $title = $langs->trans("CustomerOrder").' '.$ref.' - '.$client_name;
            $picto = 'order';
        } else {
            $url = DOL_URL_ROOT.'/fourn/commande/card.php?ref='.urlencode($ref);
            $title = $langs->trans("SupplierOrder").' '.$ref.' - '.$client_name;
            $picto = 'supplier_order';
        }
        
        $links[] = '<a href="'.$url.'" title="'.dol_escape_htmltag($title).'">';
        $links[] = img_picto('', $picto);
        $links[] = '</a>';
    }
    
    return implode(' ', $links);
}

/**
 * Calcule les totaux pour affichage en bas de tableau
 */
function calculateTotals($products)
{
    $totals = array(
        'qty_client_ordered' => 0,
        'qty_supplier_ordered' => 0,
        'stock_reel' => 0,
        'calculated_stock' => 0,
        'nb_manufacturing_orders' => 0
    );
    
    foreach ($products as $product) {
        $totals['qty_client_ordered'] += $product['qty_client_ordered'];
        $totals['qty_supplier_ordered'] += $product['qty_supplier_ordered'];
        $totals['stock_reel'] += $product['stock_reel'];
        $totals['calculated_stock'] += $product['calculated_stock'];
        $totals['nb_manufacturing_orders'] += $product['nb_manufacturing_orders'];
    }
    
    return $totals;
}
?>