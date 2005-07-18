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

class polls {
	function __construct ($database,&$config,$lang) {
		include ('kernel/polls.constants.php');
		$this->database = $database;
		$this->config = $config;
		$this->lang = $lang;
	} /* function __construct ($database,&$config,$lang) */

	function getIDCurrentPollByLanguage ($language) {
		try {
			$sql = 'SELECT ' . FIELD_POLL_ID . ' FROM ' . TBL_POLLS;
			$sql .= ' WHERE ' . FIELD_POLL_LANGUAGE . '=\'' . $language . '\'';
			$sql .= ' AND ' . FIELD_POLL_ACTIVE . '=\'' . YES . '\' LIMIT 1';
			$query = $this->database->query ($sql);
			$poll = $this->database->fetch_array ($query);
			return $poll[FIELD_POLL_ID];
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* function getIDCurrentPollByLanguage ($language) */

	function getPollByID ($id) {
		try {
			if ($id != NULL) {
				$sql = 'SELECT * FROM ' . TBL_POLLS;
				$sql .= ' WHERE ' . FIELD_POLL_ID . '=' . $id . ' LIMIT 1';
				$query = $this->database->query ($sql);
				$poll = $this->database->fetch_array ($query);
				return $poll;
			} else {
				return NULL;
			}
		} catch (exceptionlist $e) {
			throw $e;
		}
	} /* function getPollByID ($id) */

	function getAllPollsByLanguage ($language) {
		try {
			$sql = 'SELECT * FROM ' . TBL_POLLS;
			$sql .= ' WHERE ' . FIELD_POLL_LANGUAGE . '=\'' . $language . '\'';
			$query = $this->database->query ($sql);
			$polls = array ();
			while ($poll = $this->database->fetch_array ($query)) {
				$polls[] = $poll; // add poll at the end of the array
			}
			return $polls;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* function getAllPollsByLanguage ($language) */

	function vote ($info,$username) {
		try {
			if (isset ($info['voted_on'])) {
				if ($this->userhasvoted ($username) == false) {
					$id = $this->getidcurrentpollbylanguage (
						$this->config->getConfigByNameType('general/language',TYPE_STRING));
					$sql = 'SELECT ' . FIELD_POLL_RESULTS . ' FROM ' . TBL_POLLS . ' WHERE '; 
					$sql .= FIELD_POLL_ID  . '=\'' . $id . '\' LIMIT 1';
					$query = $this->database->query ($sql);
					$poll = $this->database->fetch_array ($query);
					$votes = explode (';',$poll[FIELD_POLL_RESULTS]);
					$votes[$info[POST_VOTED_ON]]++;
					$votes = implode (';',$votes);
					// and put it now back into the DB
					$sql = "UPDATE " . TBL_POLLS . " set " . FIELD_POLL_RESULTS . "='" . $votes . "' 
						WHERE " . FIELD_POLL_ID . "='" . $id . "'";// LIMIT 1"; 
					$query = $this->database->query ($sql);
					$expire = time () + 60*60*24*50; // 50 days from now on
					setcookie (COOKIE_POLL,$id,$expire,'/');
					// put the user in the db
					if ($username != NULL) {
						$sql = "SELECT " . FIELD_POLL_VOTED_USERS . " FROM " . TBL_POLLS . " WHERE " 
							. FIELD_POLL_ID . "='" . $id . "'";
						$query = $this->database->query ($sql);
						$poll = $this->database->fetch_array ($query);
						$votednames = $poll[FIELD_POLL_VOTED_USERS];
						$votednames .= ';' . $username; // add user to the voted list
						// and put it now in the db
						$sql = "UPDATE " . TBL_POLLS . " set " 
							. FIELD_POLL_VOTED_USERS . "='" . $votednames 
							. "' WHERE " . FIELD_POLL_ID . "='"
							. $id . "'";
						$query = $this->database->query ($sql);
					} else {
						// put the ip in that damn db
						// get current 
						$sql = 'UPDATE ' . TBL_POLLS . ' set ' .
							FIELD_POLL_VOTED_IPS . '=\'' . IP_USER . '\'';
						$sql .= ' WHERE ' . FIELD_POLL_ID . '=\'' . $id . '\'';
						$this->database->query ($sql);
					}
				} else {
					throw new exceptionlist ($this->lang->translate ('You have already voted'));
				}
			} else {
				throw new exceptionlist ($this->lang->translate ('You haven\'t selected an option'));
			}
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* function vote ($info,$username) */

	function userHasVoted ($username) {
		try {
			$cookie = false;
			$ip = false;
			$user = false;
			$idcurpoll = $this->getidcurrentpollbylanguage (
				$this->config->getConfigByNameType('general/language',TYPE_STRING)); 
			// check cookie
			if (isset ($_COOKIE[COOKIE_POLL])) {
				if ($_COOKIE[COOKIE_POLL] == $idcurpoll) {
					$cookie = true;
				}
			} 

			// check user
			if ($username != NULL) {
				$sql = "SELECT " . FIELD_POLL_VOTED_USERS . " FROM " . TBL_POLLS . " WHERE " 
					. FIELD_POLL_ID . "='" . $idcurpoll . "' LIMIT 1";
				$query = $this->database->query ($sql);
				$poll = $this->database->fetch_array ( $query );
				$curuser = $username;
				$users = explode (';',$poll[FIELD_POLL_VOTED_USERS]);
				foreach ($users as $username) {
					if ($username == $curuser) {
						$user = true;
						break; // if it is true it is useless to check the others
					}
				}
			}

			// check ip
			$sql = " SELECT " . FIELD_POLL_VOTED_IPS . " FROM " . TBL_POLLS . " WHERE "
				. FIELD_POLL_ID . "='" . $idcurpoll . "' LIMIT 1";
			$query = $this->database->query ( $sql );
				if ($username != NULL) {
					// FIXME
					//$getips = $user->getips ();
					$userips[] = IP_USER;
					//$error = $getips;
				} else {
					$userips[] = IP_USER;
				}
				
				$votedips = array ();
				while ($votedip = $this->database->fetch_array ($query)) {
					$votedips[] = $votedip[FIELD_POLL_VOTED_IPS];
				}
				// normally $voteips is bigger, so i think it is going less time
				//to check if a data out of big array is in a small array
				foreach ($votedips as $voteip) {
					if (in_array ($voteip,$userips)) {
						$ip = true;
						break;
					}
				}

			if (($cookie == true) or ($ip == true) or ($user == true)) {;
				return true;
			} else {
				return false;
			}
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* function userHasVoted ($username) */
} /* class polls */
?>
