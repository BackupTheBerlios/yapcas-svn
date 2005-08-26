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

class help {
	function __construct ($database,&$config,$lang) {
		include_once ('kernel/help.constants.php');
		$this->database = $database;
		$this->config = $config;
		$this->lang = $lang;
	} /* __construct ($database,&$config,$lang) */

	function getAllCategoriesIDByParent ($parent = 0) {
		$sql = 'SELECT ' . FIELD_HELP_CATEGORY_ID . ' FROM ' . TBL_HELP_CATEGORY;
		$sql .= ' WHERE ' . FIELD_HELP_CATEGORY_PARENT . '=\'' . $parent . '\'';
		$query = $this->database->query ($sql);
		$cat = array ();
		while ($category = $this->database->fetch_array ($query)) {
			$cat[] = $category[FIELD_HELP_CATEGORY_ID];
		}
		return $cat;
	} /* getAllCategoriesIDByParent ($parent = 0) */

	function getCatByIDAndLang ($i,$lang) {
		$sql = 'SELECT * FROM '  . TBL_HELP_TRANSCATEGORY;
		$sql .= ' WHERE ' . FIELD_HELP_TRANSCATEGORY_ID . '=\'' .$i. '\'';
		$sql .= ' AND ' . FIELD_HELP_TRANSCATEGORY_LANG . '=\'' . $lang .'\'';
		$query = $this->database->query ($sql);
		return $this->database->fetch_array ($query);
	}

	function getAllQByCategoryIDAndLang ($cat,$lang) {
		$sql = 'SELECT * FROM ' . TBL_HELP_QUESTIONS;
		$sql .= ' WHERE ' . FIELD_HELP_QUESTION_CATEGORY . '=\'' . $cat .'\'';
		$query = $this->database->query ($sql);
		$q = array ();
		while ($question = $this->database->fetch_array ($query)) {
			$q[] = $question;
		}
		return $q;
	} /* function getAllQByCategoryIDAndLang ($cat,$lang) */

	function getIndexByLanguage ($lang,$parent = 0) {
		$cat = array ();
		$sql = 'SELECT ' . FIELD_HELP_CATEGORY_ID . ' FROM ' . TBL_HELP_CATEGORY;
		$sql .= ' WHERE ' . FIELD_HELP_CATEGORY_PARENT . '=\'' . $parent . '\'';
		$query = $this->database->query ($sql);
		while ($item = $this->database->fetch_array ($query)) {
			$item['id'] = $item[FIELD_HELP_CATEGORY_ID];
			$item['childs'] = $this->getIndexByLanguage ($lang,$item[FIELD_HELP_CATEGORY_ID]);
			$c = $this->getCatByIDAndLang ($item[FIELD_HELP_CATEGORY_ID],$lang);
			$item['name'] = $c[FIELD_HELP_TRANSCATEGORY_NAME];
			$item['questions'] = $this->getAllQByCategoryIDAndLang ($item[FIELD_HELP_CATEGORY_ID],$lang);
			$cat[] = $item;
		}
		return $cat;
	}

	function addCategory ($parent = 0) {
		// TODO
	} /* function addCategory ($parent = 0) */

	function addQuestion ($category,$langcode,$question,$anwswser) {
		// TODO
	} /* function addQuestion ($category,$langcode,$question,$anwswser) */
} /*class help*/

?>
