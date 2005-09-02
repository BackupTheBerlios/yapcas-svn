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
* File that take care of the database (PostgreSQL) SubSystem
*
* @package database
* @author Nathan Samson
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
if (function_exists ('pg_connect')) {
	$supported['PostgreSQL 6.5'] = 'Database_postgresql';
	$supported['PostgreSQL 7.x'] = 'Database_postgresql';
	$supported['PostgreSQL 8.x'] = 'Database_postgresql';
}

if (array_search ('Database_postgresql',$supported,true)) {
/**
* class that take care of the database (PostgreSQL) SubSystem
*
* @author Nathan Samson
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class Database_postgresql implements IDatabase {
	public function __construct () {
		include_once ('kernel/exception.class.php');
	}

	private function error () {
		//if ($this->config->getConfigByNameType ('general/errorreporting',TYPE_INT) == E_ALL) {
			return pg_last_error () . '<br \>';
		//}
	} /* private function error () */

	public function connect ($host,$user,$password,$database) {
		$link = 'user='.$user;
		$link .=' password='.$password;
		$link .= ' dbname='.$database;
		$link .= ' host='.$host;
		$this->connection = pg_connect ($link);
		if ($this->connection == false) {
			throw new exceptionlist ('No db connection established',$this->error (),1);
		} else {
			return true;
		}
	} /* public function connect () */

	public function close () {
		pg_close ($this->connection);
	} /* public function close () */

	public function query ($sql,$fatal = true) {
		//echo $sql . '<br />';
		if (! isset ($this->connection)) {
			throw new exceptionlist ('There is no db-connection');
		} else {
			$result =  pg_query ($this->connection,$sql);
			if ($result == false) {
				throw new exceptionlist ('Query not executed',$this->error ());
			} else {
				return $result;
			}
		}
	} /* public function query ($sql,$fatal = true) */

	public function list_tables () {
		$sql = 'SELECT relname FROM pg_stat_user_tables ORDER by relname';
		$result = $this->query ($sql);
		return $result;
	} /* public function list_tables */

	public function get_all_tables () {
		$tables = array ();
		$tables_list = $this->list_tables ();
		while ($row = $this->fetch_array ($tables_list)) {
			array_push ($tables,$row['relname']);
		}
		return $tables;
	} /* public function get_all_tables () */

	public function fetch_array ($result) {
		return pg_fetch_array ($result);
	} /* public  function fetch_array */

	public function num_rows ($result) {
		return pg_num_rows ($result);
	} /* public function num_rows */
} // class Database_postgresql
}
?>
