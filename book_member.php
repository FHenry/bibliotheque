<?php


/**
 *  \file       htdocs/adherents/card.php
 *  \ingroup    member
 *  \brief      Page of a member
 */


// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/member.lib.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent_type.class.php';

$langs->loadLangs(array("companies", "bills", "members", "users", "other", "paypal", "bibliotheque@bibliotheque"));

$id = GETPOST('id', 'int');

$object = new Adherent($db);

//TODO : load BookBorrowing



include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';

$adht = new AdherentType($db);
$res = $adht->fetch($object->typeid);
if ($res < 0) {
	dol_print_error($db); exit;
}

// Security check
$result = restrictedArea($user, 'adherent', $object->id, '', '', 'socid', 'rowid', 0);


/*
 * View
 */

$form = new Form($db);
$title = $langs->trans("Member")." - ".$langs->trans("BiblioBook");
llxHeader('', $title);

/*
		 * Show tabs
		 */
$head = member_prepare_head($object);

print dol_get_fiche_head($head, 'tabBookBorrowing', $langs->trans("BiblioBookBorrowing"), -1, 'user');

$linkback = '<a href="'.DOL_URL_ROOT.'/adherents/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

$morehtmlref = '<a href="'.DOL_URL_ROOT.'/adherents/vcard.php?id='.$object->id.'" class="refid">';
$morehtmlref .= img_picto($langs->trans("Download").' '.$langs->trans("VCard"), 'vcard.png', 'class="valignmiddle marginleftonly paddingrightonly"');
$morehtmlref .= '</a>';


dol_banner_tab($object, 'id', $linkback, 1, 'rowid', 'ref', $morehtmlref);

print '<div class="fichecenter">';

//TODO : fetChAll de BookBorrwing pour le memer courrant

//TODO :
//Print table list of records



print '</div>';

// End of page
llxFooter();
$db->close();

