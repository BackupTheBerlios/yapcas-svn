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
include ('kernel/error.constants.php');
	class errorSDK {
		function is_error ( $var ) {
			return ( is_object ( $var ) );
		} /* function is_error  ( $var ) */
		
		function errorSDK ($number = -1) {
			$this->database = false;
			$this->error = NULL;
			$this->succeed = true; 
			$this->value = NULL;
			$this->fatal = true;
			$this->number = $number; // -1 is the standard, it means 'not set or not an error (succeed = true and fatal = true)'
		} 
		
		function getHelpIndex ($database) {
			$return = array ();
			$sql = "SELECT " .  FIELD_HELPINDEX_TITLE . " FROM " . TBL_HELPINDEX;
			$sql .= " WHERE " . FIELD_HELPINDEX_LANG . "='dutch'";
			$query = $database->query ($sql);
			if (! errorSDK::is_error($query)) {
				while ($index = $database->fetch_array ($query)) {
					$return[] = $index[FIELD_HELPINDEX_TITLE];
				}
			} else {
				return $query;
			}
			return $return; 
		}
		
		function getQuestionsByIndex ($database,$index) {
			/*$return = array ();
			$sql = "SELECT " . FIELD_HELPQUESTION_INDEX . " FROM " . TBL_HELPQUESTION . " WHERE " . FIELD_HELPQUESTION_INDEX . "='tes' LIMIT 1";
			//$sql .= 
			$query = $database->query ($sql);
			if (! errorSDK::is_error($query)) {
				while ($question = $database->fetch_array ($query)) {
					echo $question['question'];
					$return[] = $question;
				}
			} else {
				return $query;
			}
			return $return; 
			*/
		}
		
	} /* class error */

?>
