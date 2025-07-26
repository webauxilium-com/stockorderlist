<?php
/* Copyright (C) 2024 WebAuxilium <https://webauxilium.com> */

// Load Dolibarr environment
$res = 0;
if (! $res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (! $res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (! $res) die("Include of main fails");

// Load translation files
$langs->loadLangs(array("admin", "stockorderlist@stockorderlist"));

// Security check
if (!$user->admin) {
    accessforbidden();
}

// Output
$title = $langs->trans("About").' - '.$langs->trans("Module500000Name");
llxHeader('', $title);

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($title, $linkback, 'title_setup');

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("ModuleName").'</td>';
print '<td>StockOrderList</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("Version").'</td>';
print '<td>1.0.0</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("Publisher").'</td>';
print '<td>WebAuxilium</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("Website").'</td>';
print '<td><a href="https://webauxilium.com" target="_blank">https://webauxilium.com</a></td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("Description").'</td>';
print '<td>'.$langs->trans("Module500000Desc").'</td>';
print '</tr>';

print '</table>';
print '</div>';

llxFooter();
$db->close();
?>