<?php
/* Copyright (C) 2023 Alice Adminson <aadminson@example.com>
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
 * \file    bibliotheque/lib/bibliotheque.lib.php
 * \ingroup bibliotheque
 * \brief   Library files with common functions for Bibliotheque
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function bibliothequeAdminPrepareHead()
{
	global $langs, $conf;

	global $db;
	$extrafields = new ExtraFields($db);
	$extrafields->fetch_name_optionals_label('bibliotheque_book');

	$langs->load("bibliotheque@bibliotheque");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/bibliotheque/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	$head[$h][0] = dol_buildpath("/bibliotheque/admin/book_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields").' ('.$langs->trans("Book").')';
	$nbExtrafields = is_countable($extrafields->attributes['bibliotheque_book']['label']) ? count($extrafields->attributes['bibliotheque_book']['label']) : 0;
	if ($nbExtrafields > 0) {
		$head[$h][1] .= ' <span class="badge">' . $nbExtrafields . '</span>';
	}
	$head[$h][2] = 'book_extrafields';
	$h++;


	$head[$h][0] = dol_buildpath("/bibliotheque/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@bibliotheque:/bibliotheque/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@bibliotheque:/bibliotheque/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'bibliotheque@bibliotheque');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'bibliotheque@bibliotheque', 'remove');

	return $head;
}
