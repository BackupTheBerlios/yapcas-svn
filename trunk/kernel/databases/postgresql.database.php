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
if (function_exists ('pg_connect')) {
	$supported['postgresql 7.4'] = 'Database_postgresql';
	$supported['postgresql 8'] = 'Database_postgresql';
}

class Database_postgresql implements IDatabase {
	public function __construct (&$config,$file) {
		$config->addConfigByFileName ($file,TYPE_STRING,'database/host',0);
		$config->addConfigByFileName ($file,TYPE_STRING,'database/user',0);
		$config->addConfigByFileName ($file,TYPE_STRING,'database/password',0);
		$config->addConfigByFileName ($file,TYPE_STRING,'database/name',0);

		$this->config = $config;
	} /* public function __construct (&$config,$file) */

	private function error () {
		//if ($this->config->getConfigByNameType ('general/errorreporting',TYPE_INT) == E_ALL) {
			return pg_last_error () . '<br \>';
		//}
	} /* private function error () */

	public function connect () {
		$link = 'user='.$this->config->getConfigByNameType ('database/user',TYPE_STRING);
		$link .=' password='.$this->config->getConfigByNameType ('database/password',TYPE_STRING);
		$link .= ' dbname='.$this->config->getConfigByNameType ('database/name',TYPE_STRING);
		$link .= ' host=localhost';
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
} // class database
?>
