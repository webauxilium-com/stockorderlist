<?php
/* Copyright (C) 2024 WebAuxilium <https://webauxilium.com>
 */

// Load Dolibarr environment
$res = 0;
if (! $res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

// Load translation files
$langs->loadLangs(array("admin", "stockorderlist@stockorderlist"));

// Security check
if (!$user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');

if ($action == 'setvar') {
    $error = 0;
    
    // Sauvegarde des paramètres
    foreach ($_POST as $key => $value) {
        if (preg_match('/^STOCKORDERLIST_/', $key)) {
            $result = dolibarr_set_const($db, $key, $value, 'chaine', 0, '', $conf->entity);
            if ($result < 0) {
                $error++;
                setEventMessages($db->lasterror(), null, 'errors');
            }
        }
    }
    
    if (!$error) {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }
}

// Output
$title = $langs->trans("StockOrderListSetup");
llxHeader('', $title);

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($title, $linkback, 'title_setup');

// Formulaire de configuration
print '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="setvar">';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '</tr>';

// Paramètre exemple
print '<tr class="oddeven">';
print '<td>'.$langs->trans("StockOrderListExample").'</td>';
print '<td>';
print '<input type="text" name="STOCKORDERLIST_EXAMPLE" value="'.dol_escape_htmltag($conf->global->STOCKORDERLIST_EXAMPLE).'" size="30">';
print '</td>';
print '</tr>';

print '</table>';

print '<div class="tabsAction">';
print '<input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
print '</div>';

print '</form>';

llxFooter();
$db->close();
?>