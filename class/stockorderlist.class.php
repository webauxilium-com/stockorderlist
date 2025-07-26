<?php
/* Copyright (C) 2024 WebAuxilium <https://webauxilium.com>
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

class StockOrderList extends CommonObject
{
    public $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * Récupère la liste des produits avec leurs statuts de commandes et stock
     */
    public function getProductOrderList($search_ref = '', $sortfield = 'p.ref', $sortorder = 'ASC', $limit = 0, $offset = 0)
    {
        $sql = "SELECT p.rowid, p.ref, p.label,";
        $sql .= " SUM(COALESCE(cd_client.qty, 0)) as qty_client_ordered,";
        $sql .= " SUM(COALESCE(cd_fournisseur.qty, 0)) as qty_supplier_ordered,";
        $sql .= " SUM(COALESCE(ps.reel, 0)) as stock_reel,";
        $sql .= " COUNT(DISTINCT mo.rowid) as nb_manufacturing_orders,";
        $sql .= " GROUP_CONCAT(DISTINCT CONCAT(c_client.ref, ':', s_client.nom) SEPARATOR '|') as client_orders,";
        $sql .= " GROUP_CONCAT(DISTINCT CONCAT(cf.ref, ':', s_fournisseur.nom) SEPARATOR '|') as supplier_orders";
        $sql .= " FROM ".MAIN_DB_PREFIX."product p";
        
        // Commandes clients non livrées
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."commandedet cd_client ON cd_client.fk_product = p.rowid";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."commande c_client ON c_client.rowid = cd_client.fk_commande";
        $sql .= " AND c_client.fk_statut IN (1,2) AND cd_client.qty > COALESCE((";
        $sql .= "   SELECT SUM(ed.qty) FROM ".MAIN_DB_PREFIX."expeditiondet ed";
        $sql .= "   INNER JOIN ".MAIN_DB_PREFIX."expedition e ON e.rowid = ed.fk_expedition";
        $sql .= "   WHERE ed.fk_origin_line = cd_client.rowid AND e.fk_statut >= 1), 0)";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe s_client ON s_client.rowid = c_client.fk_soc";
        
        // Commandes fournisseurs non livrées
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."commande_fournisseurdet cd_fournisseur ON cd_fournisseur.fk_product = p.rowid";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."commande_fournisseur cf ON cf.rowid = cd_fournisseur.fk_commande";
        $sql .= " AND cf.fk_statut IN (3,4) AND cd_fournisseur.qty > COALESCE((";
        $sql .= "   SELECT SUM(rd.qty) FROM ".MAIN_DB_PREFIX."commande_fournisseur_dispatch rd";
        $sql .= "   WHERE rd.fk_commandefourndet = cd_fournisseur.rowid), 0)";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe s_fournisseur ON s_fournisseur.rowid = cf.fk_soc";
        
        // Stock physique
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_stock ps ON ps.fk_product = p.rowid";
        
        // Ordres de fabrication non terminés
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."mrp_mo mo ON mo.fk_product = p.rowid AND mo.status < 3";
        
        $sql .= " WHERE p.fk_product_type = 0"; // Seulement les produits physiques
        
        if ($search_ref) {
            $sql .= " AND p.ref LIKE '%".$this->db->escape($search_ref)."%'";
        }
        
        // Filtre sur les produits ayant des commandes clients non livrées
        $sql .= " AND EXISTS (";
        $sql .= "   SELECT 1 FROM ".MAIN_DB_PREFIX."commandedet cd";
        $sql .= "   INNER JOIN ".MAIN_DB_PREFIX."commande c ON c.rowid = cd.fk_commande";
        $sql .= "   WHERE cd.fk_product = p.rowid AND c.fk_statut IN (1,2)";
        $sql .= "   AND cd.qty > COALESCE((";
        $sql .= "     SELECT SUM(ed.qty) FROM ".MAIN_DB_PREFIX."expeditiondet ed";
        $sql .= "     INNER JOIN ".MAIN_DB_PREFIX."expedition e ON e.rowid = ed.fk_expedition";
        $sql .= "     WHERE ed.fk_origin_line = cd.rowid AND e.fk_statut >= 1), 0)";
        $sql .= " )";
        
        $sql .= " GROUP BY p.rowid, p.ref, p.label";
        
        if ($sortfield && $sortorder) {
            $sql .= " ORDER BY ".$this->db->escape($sortfield)." ".$this->db->escape($sortorder);
        }
        
        if ($limit > 0) {
            $sql .= " LIMIT ".(int)$offset.",".(int)$limit;
        }
        
        $result = $this->db->query($sql);
        if (!$result) {
            $this->errors[] = $this->db->lasterror();
            return false;
        }
        
        $products = array();
        while ($obj = $this->db->fetch_object($result)) {
            $products[] = array(
                'rowid' => $obj->rowid,
                'ref' => $obj->ref,
                'label' => $obj->label,
                'qty_client_ordered' => (float)$obj->qty_client_ordered,
                'qty_supplier_ordered' => (float)$obj->qty_supplier_ordered,
                'stock_reel' => (float)$obj->stock_reel,
                'nb_manufacturing_orders' => (int)$obj->nb_manufacturing_orders,
                'calculated_stock' => (float)$obj->stock_reel + (float)$obj->qty_supplier_ordered - (float)$obj->qty_client_ordered,
                'client_orders' => $obj->client_orders ? explode('|', $obj->client_orders) : array(),
                'supplier_orders' => $obj->supplier_orders ? explode('|', $obj->supplier_orders) : array()
            );
        }
        
        return $products;
    }
    
    /**
     * Compte le nombre total de produits correspondant aux critères
     */
    public function getNbProductOrderList($search_ref = '')
    {
        $sql = "SELECT COUNT(DISTINCT p.rowid) as nb";
        $sql .= " FROM ".MAIN_DB_PREFIX."product p";
        $sql .= " WHERE p.fk_product_type = 0";
        
        if ($search_ref) {
            $sql .= " AND p.ref LIKE '%".$this->db->escape($search_ref)."%'";
        }
        
        // Filtre sur les produits ayant des commandes clients non livrées
        $sql .= " AND EXISTS (";
        $sql .= "   SELECT 1 FROM ".MAIN_DB_PREFIX."commandedet cd";
        $sql .= "   INNER JOIN ".MAIN_DB_PREFIX."commande c ON c.rowid = cd.fk_commande";
        $sql .= "   WHERE cd.fk_product = p.rowid AND c.fk_statut IN (1,2)";
        $sql .= "   AND cd.qty > COALESCE((";
        $sql .= "     SELECT SUM(ed.qty) FROM ".MAIN_DB_PREFIX."expeditiondet ed";
        $sql .= "     INNER JOIN ".MAIN_DB_PREFIX."expedition e ON e.rowid = ed.fk_expedition";
        $sql .= "     WHERE ed.fk_origin_line = cd.rowid AND e.fk_statut >= 1), 0)";
        $sql .= " )";
        
        $result = $this->db->query($sql);
        if (!$result) {
            return 0;
        }
        
        $obj = $this->db->fetch_object($result);
        return $obj->nb;
    }
}
?>