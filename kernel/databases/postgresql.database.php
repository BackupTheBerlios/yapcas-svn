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
		function database ( $config ) {
			$this->config = $config;
		} // function database
		
		function error () {
			if ( $this->config->safety->error_reporting == E_ALL ) {
				return pg_last_error () . '<br \>';
			} else {
				return $GLOBALS['lang']->database->internal_error . '<br \>';
			}
		} // function error
			
		function connect () {
			$error = new error ();
			$this->connection = pg_connect ( "user=" . $this->config->database->user . " password=" . $this->config->database->password . " dbname=" . $this->config->database->name);
			if ( $this->connection == false ) {
				$error->succeed = false;
				$error->error = $this->error ();
			} else {
				$error->succeed = true;
			}
			return $error;
		} // function connect
		
		function close () {
			pg_close ( $this->connection );
		} // function close
		
		function query ( $sql,$fatal = true ) {
			$error = new error ();
			if ( ! isset ( $this->connection ) ) {
				$error->succeed = false;
				$error->error = $GLOBALS['lang']->no_connection;
			} else {
				$result =  pg_query ( $this->connection, $sql );
				if  ( $result == false ) {
					$error->succeed = false;
					$error->error = $this->error ();
					$error->fatal = $fatal;
					$error->database = true;
				} else {
					$error->succeed = true;
					$error->value = $result;
				} // if $result == false
			} // if ! isset $connection
			return $error;
		} // function query
		
		function list_tables () {
			$sql = "SELECT relname FROM pg_stat_user_tables ORDER by relname";
			$result = $this->query ( $sql );
			return $result->value;
		} // function list_tables
		
		function get_all_tables () {
			$tables = array ();
			$tables_list = $this->list_tables ();
			while ( $row = $this->fetch_array ( $tables_list ) ) {
				array_push ( $tables,$row['relname'] );
			}
			return $tables;
		} // function get_all_tables
		
		function fetch_array ( $result ) {
			return pg_fetch_array ( $result );
		} // function fetch_array 
		
		//DEPRACTED
		function countresults ( $result ) {
			return $this->num_rows ( $result );
		} // function countresults
		
		function num_rows ( $result ) {
			return pg_num_rows ( $result );
		} // function num_rows
	} // class database
?>