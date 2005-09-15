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
/**
* File that take care of the language SubSystem
*
* @package language
* @author Nathan Samson
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
/**
* class that take care of the lang SubSystem
*
* @package lang
* @author Nathan Samson
* @todo fix loading of other languages
*/
class CLang {
	function __construct ($lang = 'english') {
		$this->lang = array ();
		$this->update ($lang);
	}

	function update ($l) {
		if ($l == 'english') {
			unset ($this->lang);
			$this->lang = array ();
		} else {
			/**/
		}
		$this->prefLang = $l;
	}

	function translate ($string) {
		if (array_key_exists($string,$this->lang)) {
			return $this->lang[$string];
		} else {
			return $string;
		}
	}

	function lang2code ($lang) {
		switch (strtolower ($lang)) {
			case 'english':
				return 'en';
			case 'dutch':
				return 'nl';
			default:
				return 'en';
		}
	}

	function code2lang ($code) {
		switch (strtolower ($code)) {
			case 'en':
				return 'english';
			case 'nl':
				return 'dutch';
			default:
				return 'english';
		}
	}

	public function installed () {
		$files = scandir ('lang');
		$installed = array ('english');
		foreach ($files as $file) {
			if ((preg_match ('/^\w.*\.language\.php$/i',$file) == 1) and
				(is_file ('kernel/languages/' . $file))) {
				$installed[] = $file;
			}
		}
		return $installed;
	}

	public function getPrefLang () {
		return $this->prefLang;
	}

	public function getPrefLangCode () {
		return $this->lang2code ($this->getPrefLang ());
	}
}
?>
