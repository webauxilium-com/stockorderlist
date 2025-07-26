<?php
/* Copyright (C) 2024 WebAuxilium <https://webauxilium.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

class modStockOrderList extends DolibarrModules
{
    public function __construct($db)
    {
        global $langs, $conf;
        
        $this->db = $db;
        $this->numero = 500000;
        $this->family = "products";
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = "Liste des stocks et commandes";
        $this->descriptionlong = "Module permettant de lister les produits en commande client non livrée avec leur stock et commandes fournisseur";
        $this->version = '1.0.0';
        $this->editor_name = 'WebAuxilium';
        $this->editor_web = 'https://webauxilium.com';
        $this->const_name = 'STOCKORDERLIST_';
        $this->picto = 'stockorderlist@stockorderlist';
        $this->module_parts = array();
        $this->config_page_url = array("setup.php@stockorderlist");
        $this->depends = array('modProduct', 'modCommande', 'modFournisseur', 'modStock');
        $this->requiredby = array();
        $this->conflictwith = array();
        $this->langfiles = array("stockorderlist@stockorderlist");
        $this->warnings_activation = array();
        $this->warnings_activation_ext = array();
        $this->const = array();
        $this->boxes = array();
        
        // Permissions
        $this->rights = array();
        $r = 0;
        
        $this->rights[$r][0] = $this->numero + $r + 1;
        $this->rights[$r][1] = 'Lire les états de stock et commandes';
        $this->rights[$r][4] = 'stockorderlist';
        $this->rights[$r][5] = 'read';
        $r++;
        
        $this->rights[$r][0] = $this->numero + $r + 1;
        $this->rights[$r][1] = 'Configurer le module';
        $this->rights[$r][4] = 'stockorderlist';
        $this->rights[$r][5] = 'write';
        $r++;
        
        // Menu
        $this->menu = array();
        $r = 0;
        
        // Menu principal Supply Chain
        $this->menu[$r]['fk_menu'] = '';
        $this->menu[$r]['type'] = 'top';
        $this->menu[$r]['titre'] = 'Supply Chain';
        $this->menu[$r]['mainmenu'] = 'supplychain';
        $this->menu[$r]['leftmenu'] = '';
        $this->menu[$r]['url'] = '/stockorderlist/list.php';
        $this->menu[$r]['langs'] = 'stockorderlist@stockorderlist';
        $this->menu[$r]['position'] = 500;
        $this->menu[$r]['enabled'] = '$conf->stockorderlist->enabled';
        $this->menu[$r]['perms'] = '$user->rights->stockorderlist->read';
        $this->menu[$r]['target'] = '';
        $this->menu[$r]['user'] = 2;
        $r++;
        
        // Sous-menu État Stock - Commande
        $this->menu[$r]['fk_menu'] = 'fk_mainmenu=supplychain';
        $this->menu[$r]['type'] = 'left';
        $this->menu[$r]['titre'] = 'État Stock - Commande';
        $this->menu[$r]['mainmenu'] = 'supplychain';
        $this->menu[$r]['leftmenu'] = 'stockorderlist';
        $this->menu[$r]['url'] = '/stockorderlist/list.php';
        $this->menu[$r]['langs'] = 'stockorderlist@stockorderlist';
        $this->menu[$r]['position'] = 100;
        $this->menu[$r]['enabled'] = '$conf->stockorderlist->enabled';
        $this->menu[$r]['perms'] = '$user->rights->stockorderlist->read';
        $this->menu[$r]['target'] = '';
        $this->menu[$r]['user'] = 2;
        $r++;
    }
    
    public function init($options = '')
    {
        $sql = array();
        return $this->_init($sql, $options);
    }
    
    public function remove($options = '')
    {
        $sql = array();
        return $this->_remove($sql, $options);
    }
}
?>