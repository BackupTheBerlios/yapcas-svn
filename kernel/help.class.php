<?php
/* YaPCaS is a Content Admins System written in PHP
 * Copyright (C) 2005 Nathan Samson
 * This program is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * any later version.
 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. 
*/

include ('kernel/help.constants.php');
class help {

	function __construct ($database,&$config,$lang) {
		$this->database = $database;
		$this->config = $config;
		$this->lang = $lang;
	} /* __construct ($database,&$config,$lang) */

	function getAllCategoriesIDByParent ($parent = 0) {
		try {
			$sql = 'SELECT ' . FIELD_HELP_CATEGORY_ID . ' FROM ' . TBL_HELP_CATEGORY;
			$sql .= ' WHERE ' . FIELD_HELP_CATEGORY_PARENT . '=\'' . $parent . '\'';
			$query = $this->database->query ($sql);
			$cat = array ();
			while ($category = $this->database->fetch_array ($query)) {
				$cat[] = $category[FIELD_HELP_CATEGORY_ID];
			}
			return $cat;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* getAllCategoriesIDByParent ($parent = 0) */

	function getCatByIDAndLang ($i,$lang) {
		try {
			$sql = 'SELECT * FROM '  . TBL_HELP_TRANSCATEGORY;
			$sql .= ' WHERE ' . FIELD_HELP_TRANSCATEGORY_ID . '=\'' .$i. '\'';
			$sql .= ' AND ' . FIELD_HELP_TRANSCATEGORY_LANG . '=\'' . $lang .'\'';
			$query = $this->database->query ($sql);
			return $this->database->fetch_array ($query);
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	}

	function getAllQByCategoryIDAndLang ($cat,$lang) {
		try {
			$sql = 'SELECT * FROM ' . TBL_HELP;
			$sql .= ' WHERE ' . FIELD_HELP_QUESTION_CATEGORY . '=\'' . $cat .'\'';
			$query = $this->database->query ($sql);
			$q = array ();
			while ($question = $this->database->fetch_array ($query)) {
				$q[] = $question;
			}
			return $q;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* function getAllQByCategoryIDAndLang ($cat,$lang) */

	function addCategory ($parent = 0) {
		// TODO
	} /* function addCategory ($parent = 0) */

	function addQuestion ($category,$langcode,$question,$anwswser) {
		// TODO
	} /* function addQuestion ($category,$langcode,$question,$anwswser) */
} /*class help*/

?>
