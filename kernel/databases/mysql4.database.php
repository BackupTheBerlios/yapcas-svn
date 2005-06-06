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
if (! defined ('EXCEPTION_CLASS')) {
	include ('kernel/exception.class.php');
}

class database {
	public function __construct (&$config,$configfile) {
		$config->addConfigByFileName ($configfile,TYPE_STRING,'database/host',0);
		$config->addConfigByFileName ($configfile,TYPE_STRING,'database/user',0);
		$config->addConfigByFileName ($configfile,TYPE_STRING,'database/password',0);
		$config->addConfigByFileName ($configfile,TYPE_STRING,'database/name',0);

		$this->config = $config;
	} /* function database (&$config,$configfile) */

	private function error () {
		//if ($this->config->getConfigByNameType ('general/errorreporting',TYPE_INT) == E_ALL) {
			return mysql_errno () . ': ' . mysql_error () . ' ';
		//}
	} /* function error () */

	public function connect () {
		$this->connection = mysql_connect (
			$this->config->getConfigByNameType ('database/host',TYPE_STRING),
			$this->config->getConfigByNameType ('database/user',TYPE_STRING),
			$this->config->getConfigByNameType ('database/password',TYPE_STRING));
		if ($this->connection == false) {
			throw new exceptionlist ("No database connection established", 
				': '.$this->error ().': '.__FILE__.': '.__FUNCTION__.': '.
				__LINE__,1,true,true);
		} else {
			$succeed = mysql_select_db (
				$this->config->getConfigByNameType('database/name',TYPE_STRING),
				$this->connection );
			if ($succeed == true) {
				return true;
			} else {
				throw new exceptionlist ("No database could be selected",
					': '.$this->error ().': '.__FILE__.': '.__FUNCTION__.': '.
					__LINE__,2,true,true);
			}
		}
	} /* function connect () */

	public function close () {
		mysql_close ($this->connection);
	} /* function close () */

	private function sql2mysql ($sql) {
		if (ereg ('( serial )',$sql)) {
			$sql = ereg_replace ('( serial )',' int AUTO_INCREMENT ',$sql);
		}
		return $sql;
	} /* function sql2mysql ($sql) */

	public function query ($sql,$fatal = true) {
		//echo "SQL: " . $sql . '<br />';
		if (!isset ($this->connection)) {
			throw new exceptionlist ("No database connection",': '.$sql.': '.
				__FILE__.': '.__FUNCTION__.': '.__LINE__,$fatal,-1,true);
		} else {
			$sql = $this->sql2mysql ($sql);
			$result = mysql_query ($sql,$this->connection);
			if ($result == false) {
				throw new exceptionlist ("Query not executed",': '.$this->error ().
					': '.$sql.': '.__FILE__.': '.__FUNCTION__.': '.__LINE__,$fatal,3,true);
			} else {
				return $result;
			}
		}
	} /* function query ($sql,$fatal = true) */

	public function fetch_array ($result) {
		return mysql_fetch_array ($result,MYSQL_BOTH); // MYSQL_BOTH is the standard
	} /* function fetch_array ($result) */

	public function list_tables () {
		return mysql_list_tables ($this->config->getConfigByNameType('database/name',
			TYPE_STRING));
	} /* function list_tables () */

	public function table_name ($result,$i) {
		return mysql_table_name ($result,$i);
	} /* function table_name ($result,$i) */

	public function get_all_tables () {
		$tables_list = $this->list_tables ();
		$tables = NULL;
		$i = 0;
		while ($row = $this->fetch_array ($tables_list)) {
			$tables .= $this->table_name ($tables_list,$i) . ',';
			$i++;
		}
		$tables = explode (',',$tables);
		return $tables;
	} /* function get_all_tables () */

	public function num_rows ($result) {
		return mysql_num_rows ($result);
	} /* function num_rows ($result) */
} /* class database */
?>
