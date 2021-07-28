<?php
/* Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    lib/bibliotheque_livre.lib.php
 * \ingroup bibliotheque
 * \brief   Library files with common functions for Livre
 */

/**
 * Prepare array of tabs for Livre
 *
 * @param	Livre	$object		Livre
 * @return 	array					Array of tabs
 */
function livrePrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("bibliotheque@bibliotheque");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/bibliotheque/livre_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;

	if (isset($object->fields['note_public']) || isset($object->fields['note_private'])) {
		$nbNote = 0;
		if (!empty($object->note_private)) {
			$nbNote++;
		}
		if (!empty($object->note_public)) {
			$nbNote++;
		}
		$head[$h][0] = dol_buildpath('/bibliotheque/livre_note.php', 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans('Notes');
		if ($nbNote > 0) {
			$head[$h][1] .= (empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER) ? '<span class="badge marginleftonlyshort">'.$nbNote.'</span>' : '');
		}
		$head[$h][2] = 'note';
		$h++;
	}

	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
	$upload_dir = $conf->bibliotheque->dir_output."/livre/".dol_sanitizeFileName($object->ref);
	$nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
	$nbLinks = Link::count($db, $object->element, $object->id);
	$head[$h][0] = dol_buildpath("/bibliotheque/livre_document.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans('Documents');
	if (($nbFiles + $nbLinks) > 0) {
		$head[$h][1] .= '<span class="badge marginleftonlyshort">'.($nbFiles + $nbLinks).'</span>';
	}
	$head[$h][2] = 'document';
	$h++;

	$head[$h][0] = dol_buildpath("/bibliotheque/livre_agenda.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Events");
	$head[$h][2] = 'agenda';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@bibliotheque:/bibliotheque/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@bibliotheque:/bibliotheque/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'livre@bibliotheque');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'livre@bibliotheque', 'remove');

	return $head;
}

/**
 *  Show tab footer of a card.
 *  Note: $object->next_prev_filter can be set to restrict select to find next or previous record by $form->showrefnav.
 *
 *  @param	Object	$object			Object to show
 *  @param	string	$paramid   		Name of parameter to use to name the id into the URL next/previous link
 *  @param	string	$morehtml  		More html content to output just before the nav bar
 *  @param	int		$shownav	  	Show Condition (navigation is shown if value is 1)
 *  @param	string	$fieldid   		Nom du champ en base a utiliser pour select next et previous (we make the select max and min on this field). Use 'none' for no prev/next search.
 *  @param	string	$fieldref   	Nom du champ objet ref (object->ref) a utiliser pour select next et previous
 *  @param	string	$morehtmlref  	More html to show after the ref (see $morehtmlleft for before)
 *  @param	string	$moreparam  	More param to add in nav link url.
 *	@param	int		$nodbprefix		Do not include DB prefix to forge table name
 *	@param	string	$morehtmlleft	More html code to show before the ref (see $morehtmlref for after)
 *	@param	string	$morehtmlstatus	More html code to show under navigation arrows
 *  @param  int     $onlybanner     Put this to 1, if the card will contains only a banner (this add css 'arearefnobottom' on div)
 *	@param	string	$morehtmlright	More html code to show before navigation arrows
 *  @return	void
 */
function dol_banner_tab_bibliotheque($object, $paramid, $morehtml = '', $shownav = 1, $fieldid = 'rowid', $fieldref = 'ref', $morehtmlref = '', $moreparam = '', $nodbprefix = 0, $morehtmlleft = '', $morehtmlstatus = '', $onlybanner = 0, $morehtmlright = '')
{
	/**
	 * @var $object Livre
	 */
	global $conf, $form, $user, $langs;

	$error = 0;

	$maxvisiblephotos = 1;
	$showimage = 1;
	$entity = (empty($object->entity) ? $conf->entity : $object->entity);
	$showbarcode = empty($conf->barcode->enabled) ? 0 : (empty($object->barcode) ? 0 : 1);
	if (!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && empty($user->rights->barcode->lire_advance)) {
		$showbarcode = 0;
	}

	$modulepart = $object->element;


	//TODO : Make livre image
	if ($object->element == 'livre') {
		$width = 80;
		$cssclass = 'photoref';
		//$showimage = $object->is_photo_available($conf->product->multidir_output[$entity]);
		$maxvisiblephotos = (isset($conf->global->PRODUCT_MAX_VISIBLE_PHOTO) ? $conf->global->PRODUCT_MAX_VISIBLE_PHOTO : 5);
		if ($conf->browser->layout == 'phone') {
			$maxvisiblephotos = 1;
		}
		if ($showimage) {
			$morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref">'.$object->show_photos('product', $conf->product->multidir_output[$entity], 'small', $maxvisiblephotos, 0, 0, 0, $width, 0).'</div>';
		} else {
			if (!empty($conf->global->PRODUCT_NODISPLAYIFNOPHOTO)) {
				$nophoto = '';
				$morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref"></div>';
			} else {    // Show no photo link
				$nophoto = '/public/theme/common/nophoto.png';
				$morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photo'.$modulepart.($cssclass ? ' '.$cssclass : '').'" alt="No photo"'.($width ? ' style="width: '.$width.'px"' : '').' src="'.DOL_URL_ROOT.$nophoto.'"></div>';
			}
		}
	}

	if ($showbarcode) {
		$morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref">'.$form->showbarcode($object, 100, 'photoref').'</div>';
	}

	if ($object->element == 'product') {
		//$morehtmlstatus.=$langs->trans("Status").' ('.$langs->trans("Sell").') ';
		if (!empty($conf->use_javascript_ajax) && $user->rights->produit->creer && !empty($conf->global->MAIN_DIRECT_STATUS_UPDATE)) {
			$morehtmlstatus .= ajax_object_onoff($object, 'status', 'tosell', 'ProductStatusOnSell', 'ProductStatusNotOnSell');
		} else {
			$morehtmlstatus .= '<span class="statusrefsell">'.$object->getLibStatut(6, 0).'</span>';
		}
		$morehtmlstatus .= ' &nbsp; ';
		//$morehtmlstatus.=$langs->trans("Status").' ('.$langs->trans("Buy").') ';
		if (!empty($conf->use_javascript_ajax) && $user->rights->produit->creer && !empty($conf->global->MAIN_DIRECT_STATUS_UPDATE)) {
			$morehtmlstatus .= ajax_object_onoff($object, 'status_buy', 'tobuy', 'ProductStatusOnBuy', 'ProductStatusNotOnBuy');
		} else {
			$morehtmlstatus .= '<span class="statusrefbuy">'.$object->getLibStatut(6, 1).'</span>';
		}
	} elseif (in_array($object->element, array('facture', 'invoice', 'invoice_supplier', 'chargesociales', 'loan', 'tva', 'salary'))) {
		$tmptxt = $object->getLibStatut(6, $object->totalpaye);
		if (empty($tmptxt) || $tmptxt == $object->getLibStatut(3)) {
			$tmptxt = $object->getLibStatut(5, $object->totalpaye);
		}
		$morehtmlstatus .= $tmptxt;
	} elseif ($object->element == 'contrat' || $object->element == 'contract') {
		if ($object->statut == 0) {
			$morehtmlstatus .= $object->getLibStatut(5);
		} else {
			$morehtmlstatus .= $object->getLibStatut(4);
		}
	} elseif ($object->element == 'facturerec') {
		if ($object->frequency == 0) {
			$morehtmlstatus .= $object->getLibStatut(2);
		} else {
			$morehtmlstatus .= $object->getLibStatut(5);
		}
	} elseif ($object->element == 'project_task') {
		$object->fk_statut = 1;
		if ($object->progress > 0) {
			$object->fk_statut = 2;
		}
		if ($object->progress >= 100) {
			$object->fk_statut = 3;
		}
		$tmptxt = $object->getLibStatut(5);
		$morehtmlstatus .= $tmptxt; // No status on task
	} else { // Generic case
		$tmptxt = $object->getLibStatut(6);
		if (empty($tmptxt) || $tmptxt == $object->getLibStatut(3)) {
			$tmptxt = $object->getLibStatut(5);
		}
		$morehtmlstatus .= $tmptxt;
	}

	// Add if object was dispatched "into accountancy"
	if (!empty($conf->accounting->enabled) && in_array($object->element, array('bank', 'paiementcharge', 'facture', 'invoice', 'invoice_supplier', 'expensereport', 'payment_various'))) {
		// Note: For 'chargesociales', 'salaries'... this is the payments that are dispatched (so element = 'bank')
		if (method_exists($object, 'getVentilExportCompta')) {
			$accounted = $object->getVentilExportCompta();
			$langs->load("accountancy");
			$morehtmlstatus .= '</div><div class="statusref statusrefbis"><span class="opacitymedium">'.($accounted > 0 ? $langs->trans("Accounted") : $langs->trans("NotYetAccounted")).'</span>';
		}
	}

	// Add alias for thirdparty
	if (!empty($object->name_alias)) {
		$morehtmlref .= '<div class="refidno">'.$object->name_alias.'</div>';
	}

	// Add label
	if (in_array($object->element, array('product', 'bank_account', 'project_task'))) {
		if (!empty($object->label)) {
			$morehtmlref .= '<div class="refidno">'.$object->label.'</div>';
		}
	}

	if (method_exists($object, 'getBannerAddress') && !in_array($object->element, array('product', 'bookmark', 'ecm_directories', 'ecm_files'))) {
		$moreaddress = $object->getBannerAddress('refaddress', $object);
		if ($moreaddress) {
			$morehtmlref .= '<div class="refidno">';
			$morehtmlref .= $moreaddress;
			$morehtmlref .= '</div>';
		}
	}
	if (!empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && ($conf->global->MAIN_SHOW_TECHNICAL_ID == '1' || preg_match('/'.preg_quote($object->element, '/').'/i', $conf->global->MAIN_SHOW_TECHNICAL_ID)) && !empty($object->id)) {
		$morehtmlref .= '<div style="clear: both;"></div>';
		$morehtmlref .= '<div class="refidno">';
		$morehtmlref .= $langs->trans("TechnicalID").': '.$object->id;
		$morehtmlref .= '</div>';
	}

	print '<div class="'.($onlybanner ? 'arearefnobottom ' : 'arearef ').'heightref valignmiddle centpercent">';
	print $form->showrefnav($object, $paramid, $morehtml, $shownav, $fieldid, $fieldref, $morehtmlref, $moreparam, $nodbprefix, $morehtmlleft, $morehtmlstatus, $morehtmlright);
	print '</div>';
	print '<div class="underrefbanner clearboth"></div>';
}
