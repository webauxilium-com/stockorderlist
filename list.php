<?php
/* Copyright (C) 2024 WebAuxilium <https://webauxilium.com> */

// Load Dolibarr environment
$res = 0;
if (! $res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (! $res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once './class/stockorderlist.class.php';
require_once './lib/stockorderlist.lib.php';

// Load translation files
$langs->loadLangs(array("stockorderlist@stockorderlist", "products", "orders", "stocks"));

// Security check
if (!$user->hasRight('stockorderlist', 'read')) {
    accessforbidden();
}

// Initialize technical objects
$object = new StockOrderList($db);
$hookmanager->initHooks(array('stockorderlistlist'));

// Parameters
$action = GETPOST('action', 'aZ09') ? GETPOST('action', 'aZ09') : 'list';
$search_ref = GETPOST('search_ref', 'alpha');
$sortfield = GETPOST('sortfield', 'aZ09comma') ? GETPOST('sortfield', 'aZ09comma') : 'p.ref';
$sortorder = GETPOST('sortorder', 'aZ09comma') ? GETPOST('sortorder', 'aZ09comma') : 'ASC';
$page = GETPOSTINT('page');
if (empty($page) || $page < 0) $page = 0;
$limit = GETPOSTINT('limit') ? GETPOSTINT('limit') : $conf->liste_limit;
$offset = $limit * $page;

// Actions
if (GETPOST('cancel', 'alpha')) {
    $action = 'list';
    $search_ref = '';
}

// Initialize variables
$products = false;
$nbtotalofrecords = 0;
$error = 0;

if ($action == 'list') {
    try {
        // Récupération des données
        $products = $object->getProductOrderList($search_ref, $sortfield, $sortorder, $limit, $offset);
        if ($products === false) {
            $error++;
            setEventMessages($object->error, $object->errors, 'errors');
        } else {
            $nbtotalofrecords = $object->getNbProductOrderList($search_ref);
        }
    } catch (Exception $e) {
        $error++;
        setEventMessages($e->getMessage(), null, 'errors');
        $products = array();
    }
}

// Output
$title = $langs->trans("StockOrderListTitle");
$help_url = '';
$morejs = array();
$morecss = array('/stockorderlist/css/stockorderlist.css.php');

llxHeader('', $title, $help_url, '', 0, 0, $morejs, $morecss);

// Page title
$newcardbutton = '';
print load_fiche_titre($title, $newcardbutton, 'stockorderlist@stockorderlist');

// Show message if error
if ($error) {
    print '<div class="error">'.$langs->trans("ErrorOccurred").'</div>';
}

// Load template
include_once './tpl/stockorderlist_list.tpl.php';

// End of page
llxFooter();
$db->close();
?>