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

dol_include_once('/bibliotheque/class/bookborrowing.class.php');
$bookBorrowing = new BookBorrowing($db);
$res = $bookBorrowing->fetchAll('', '', 0, 0, ['t.fk_member' => $id]);

if (!is_array($res) && $res < 0) {
	setEventMessage($bookBorrowing->error, 'errors');
} else {
	print '<div class="div-table-responsive">';
	print '<table class="tagtable nobottomiftotal liste">';

	foreach ($res as $object) {
		// Show here line of result
		print '<tr class="oddeven">';

		foreach ($object->fields as $key => $val) {
			$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
			if (in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
				$cssforfield .= ($cssforfield ? ' ' : '').'center';
			} elseif ($key == 'status') {
				$cssforfield .= ($cssforfield ? ' ' : '').'center';
			}

			if (in_array($val['type'], array('timestamp'))) {
				$cssforfield .= ($cssforfield ? ' ' : '').'nowrap';
			} elseif ($key == 'ref') {
				$cssforfield .= ($cssforfield ? ' ' : '').'nowrap';
			}

			if (in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real', 'price')) && !in_array($key, array('rowid', 'status')) && empty($val['arrayofkeyval'])) {
				$cssforfield .= ($cssforfield ? ' ' : '').'right';
			}

			print '<td'.($cssforfield ? ' class="'.$cssforfield.'"' : '').'>';
			if ($key == 'status') {
				print $object->getLibStatut(5);
			} elseif ($key == 'rowid') {
				print $object->showOutputField($val, $key, $object->id, '');
			} else {
				print $object->showOutputField($val, $key, $object->$key, '');
			}
			print '</td>';
		}
		print '</tr>';
	}

	print '</table>';
	print '</div>';
}

print '</div>';

// End of page
llxFooter();
$db->close();

