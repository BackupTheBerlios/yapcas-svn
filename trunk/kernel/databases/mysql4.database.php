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
* File that take care of the database (MySQL) SubSystem
*
* @package database
* @author Nathan Samson
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
if (function_exists ('mysql_connect')) {
	$supported['MySQL 3.x'] = 'Database_mysql';
	$supported['MySQL 4.x'] = 'Database_mysql';
}
if (function_exists ('mysqli_connect')) {
	//$supported['MySQLi 4.1'] = 'Database_mysqli';
	//$supported['MySQLi 5.x'] = 'Database_mysqli';
}

if (array_search ('Database_mysql',$supported,true)) {
/**
* class that take care of the database (MySQL) SubSystem
*
* @author Nathan Samson
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class Database_mysql implements IDatabase {
	public function __construct () {
		include_once ('kernel/exception.class.php');
	}

	private function error () {
		//if ($this->config->getConfigByNameType ('general/errorreporting',TYPE_INT) == E_ALL) {
			return mysql_errno () . ': ' . mysql_error () . ' ';
		//}
	} /* function error () */

	public function connect ($host,$user,$password,$database) {
		$this->connection = mysql_connect ($host,$user,$password,$database);
		if ($this->connection == false) {
			throw new exceptionlist ("No database connection established", 
				': '.$this->error ().': '.__FILE__.': '.__FUNCTION__.': '.
				__LINE__,1,true,true);
		} else {
			$succeed = mysql_select_db ($database,$this->connection);
			if ($succeed == true) {
				return true;
			} else {
				throw new exceptionlist ("No database could be selected",
					': '.$this->error ().': '.__FILE__.': '.__FUNCTION__.': '.
					__LINE__,2,true,true);
			}
		}
	}

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
} /* class Database_mysql */
}
?>
