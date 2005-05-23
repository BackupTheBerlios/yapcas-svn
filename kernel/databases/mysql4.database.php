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

class database {
	function database (&$config) {
		$this->config = $config;

		$this->config->addConfigByFileName ('site.config.php',TYPE_STRING,'database/host',0);
		$this->config->addConfigByFileName ('site.config.php',TYPE_STRING,'database/user',0);
		$this->config->addConfigByFileName ('site.config.php',TYPE_STRING,'database/password',0);
		$this->config->addConfigByFileName ('site.config.php',TYPE_STRING,'database/name',0);
	} /* function database (&$config) */

	function error () {
		if ($this->config->getConfigByNameType ('general/errorreporting',TYPE_INT) == E_ALL) {
			return mysql_errno () . ': ' . mysql_error () . ' ';
		} else {
			return $GLOBALS['lang']->database->internal_error;
		}
	} /* function error () */

	function connect () {
		$error = new errorSDK ();
		$this->connection = mysql_connect (
			$this->config->getConfigByNameType ('database/host',TYPE_STRING),
			$this->config->getConfigByNameType ('database/user',TYPE_STRING),
			$this->config->getConfigByNameType ('database/password',TYPE_STRING));
		if ($this->connection == false) {
			$error->succeed = false;
			$error->error = $this->error ();
		} else {
			$error->succeed = mysql_select_db (
				$this->config->getConfigByNameType('database/name',TYPE_STRING),$this->connection );
			$error->error = $this->error ();
		}
		mysql_info ($this->connection);
		return $error;
	} /* function connect () */

	function close () {
		mysql_close ($this->connection);
	} /* function close () */

	function sql2mysql ($sql) {
		if (ereg ( '( serial )',$sql)) {
			$sql = ereg_replace ('( serial )',' int AUTO_INCREMENT ',$sql);
		}
		return $sql;
	} /* function sql2mysql ($sql) */

	function query ($sql,$fatal = true) {
		//echo "SQL: " . $sql . '<br />';
		$error = new errorSDK ();
		if (!isset ($this->connection)) {
			$error->succeed = false;
			$error->database = true;
			$error->fatal = true;
			$error->error = $GLOBALS['lang']->no_connection;
			return $error;
		} else {
			$sql = $this->sql2mysql ($sql);
			$result = mysql_query ($sql, $this->connection);
			if ($result == false) {
				$error->succeed = false;
				$error->error = $this->error () . $sql . '<br />';
				$error->fatal = $fatal;
				$error->database = true;
				return $error;
			} else {
				return $result;
			}
		}
	} /* function query ($sql,$fatal = true) */

	function fetch_array ($result) {
		if ($result == false) {
		// echo $lang->Fatal_error;
		} else {
			return mysql_fetch_array ($result,MYSQL_BOTH);
		}
	} /* function fetch_array ($result) */

	function  list_tables () {
		return mysql_list_tables ($this->config->getConfigByNameType('database/name',TYPE_STRING));
	} /* function list_tables () */

	function table_name ($result,$i) {
		return mysql_table_name ($result,$i);
	} /* function table_name ($result,$i) */

	function get_all_tables () {
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

	//DEPRECATED
	function countresults ($result) {
		return $this->num_rows ($result);
	} /* function countresults ($result) */

	function num_rows ($result) {
		return mysql_num_rows ($result);
	} /* function num_rows ($result) */
} /* class database */
?>
