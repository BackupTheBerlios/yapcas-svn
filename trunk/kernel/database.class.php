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
 * GNU Library General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. 
*/
/**
* File that take care of the database SubSystem
*
* @package database
* @author Nathan Samson
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
function checkDatabase ($database,$tables) {
	// TODO
	return true;
}

/**
* interface that take care off the generic database SubSystem
*
* @version 0.4cvs
* @author Nathan Samson
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
interface IDatabase {
	public function __construct ();
	public function connect ($host,$user,$password,$database);
	public function close ();
	public function query ($sql,$fatal = false);
	public function fetch_array ($result);
	public function num_rows ($result);
	public function get_all_tables ();
}

/**
* Class that take care off the database SubSystem
*
* @version 0.4cvs
* @author Nathan Samson
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class CDatabase {
	private $loadedDatabase = NULL;
	private $supported;

	public function __construct () {
		$this->getAllSupportedDatabases ();
	}

	public function getAllSupportedDatabases () {
		if ($this->supported == NULL) {
			$files = scandir ('kernel/databases/');
			$supported = array ();
			foreach ($files as $file) {
				if ((preg_match ('/^\w.*\.database\.php$/i',$file) == 1) and
					(is_file ('kernel/databases/' . $file))) {
					include_once ('kernel/databases/' . $file);
				}
			}
			$this->supported = $supported;
		}
		return $this->supported;
	}

	public function load ($type) {
		if (array_key_exists ($type,$this->supported)) {
			$classname = $this->supported[$type];
			$this->loadedDatabase = new $classname ();
			return $this->loadedDatabase;
		} else {
			throw new Exception ('Database not supported');
		}
	}
} /*class CDatabase */
?>
