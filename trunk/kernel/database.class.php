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

function checkDatabase ($database,$tables) {
	// TODO
	return true;
}


interface IDatabase {
	public function __construct (&$config,$file);
	public function connect ();
	public function close ();
	public function query ($sql,$fatal = false);
	public function fetch_array ($result);
	public function num_rows ($result);
	public function get_all_tables ();
}

class CDatabase {
	private $loadedDatabase = NULL;
	private $supported;

	public function getAllSupportedDatabases () {
		$d = dir ('kernel/databases/');
		$supported = array ();
		$entry = $d->read ();
		while ($entry !== false) {
			if (preg_match('/(.+?).database.php/i',$entry) == 1) {
				include_once ('kernel/databases/' . $entry);
			}
			$entry = $d->read ();
		}
		$d->close ();
		$this->supported = $supported;
	}

	public function __construct () {
		$this->getAllSupportedDatabases ();
	}

	public function load ($type,&$config,$file) {
		if (array_key_exists ($type,$this->supported)) {
			$classname = $this->supported[$type];
			$this->loadedDatabase = new $classname ($config,$file);
			return $this->loadedDatabase;
		} else {
			throw new Exception ('Database not supported');
		}
	}
} /*class CDatabase */
?>
