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
		function polls ($database,&$config) {
			include ('kernel/polls.constants.php');

			$this->config = $config;
		} /* function polss */
	
		function getidcurrentpollbylanguage ( $language ) {
			$sql =
				'SELECT ' . FIELD_POLL_ID . ' FROM ' . TBL_POLLS . ' WHERE ' . 
				FIELD_POLL_LANGUAGE . "='" . $language . "' AND " . FIELD_POLL_ACTIVE . "='" . 
				YES . "' LIMIT 1";
			$query = $GLOBALS['database']->query ( $sql );
			if ( ! errorSDK::is_error ( $query ) ) {
				$poll = $GLOBALS['database']->fetch_array ( $query );
				return $poll[ FIELD_POLL_ID ];
			} else {
				echo 'FOUT';
				return $query;
			}
		} /* function getidcurrentpollbylanguage ( $language )*/
		
		function getpollbyid ( $id ) {
			if ( errorSDK::is_error ( $id ) ) {
				return $id;
			} else {
				if ( $id != NULL ) {
					$sql = 'SELECT * FROM ' . TBL_POLLS . ' WHERE ' . FIELD_POLL_ID . '=' . $id . ' LIMIT 1';
					$query = $GLOBALS['database']->query ( $sql );
					if ( errorSDK::is_error ( $query ) ) {
						return $query;
					} else {
						$poll = $GLOBALS['database']->fetch_array ( $query );
						return $poll;
					}
				} else {
					$error = new errorSDK ();
					$error->succeed = false;
					$error->error = 'geen oll';
					return $error;
				}
			}
		} /* function getpollbyid ( $id ) */
		
		function getallpollsbylanguage ( $language ) {
			$sql = "SELECT * FROM " . TBL_POLLS . " WHERE " . FIELD_POLL_LANGUAGE . "='" . $language . "'";
			$query = $GLOBALS['database']->query ( $sql );
			if ( errorSDK::is_error ( $query ) ) {
				return $query;
			} else {
				$polls = array ();
				while ( $poll = $GLOBALS['database']->fetch_array ( $query ) ) {
					$polls[] = $poll; // add poll at the end of the array
				}
				return $polls;
			}
		} /* function getallpollsbylanguage */
		
		function vote ( $info ) {
			if ( ( isset ( $info['voted_on'] ) ) ) {
				// get current voted info
				if ( $this->userhasvoted () == false ) {
					$id = $this->getidcurrentpollbylanguage ($this->config->getConfigByNameType('general/language',TYPE_STRING));
					if ( ! errorSDK::is_error ( $id ) ) {
						$sql = "SELECT " . FIELD_POLL_RESULTS . " FROM " . TBL_POLLS . " WHERE " 
							. FIELD_POLL_ID  . "='" . $id . "' LIMIT 1";
						$query = $GLOBALS['database']->query ( $sql );
						if ( ! errorSDK::is_error ( $query ) ) {
							$poll = $GLOBALS['database']->fetch_array ( $query );
							$votes = explode ( ';',$poll[FIELD_POLL_RESULTS] );
							$votes[ $info[POST_VOTED_ON] ]++;
							$votes = implode ( ';',$votes );
							// and put it now back into the DB
							$sql = "UPDATE " . TBL_POLLS . " set " . FIELD_POLL_RESULTS . "='" . $votes . "' 
								WHERE " . FIELD_POLL_ID . "='" . $id . "' LIMIT 1"; 
							$query = $GLOBALS['database']->query ( $sql );
							if ( ! errorSDK::is_error ( $query ) ) {
								$expire = time () + 60*60*24*50; // 50 days from now on
								setcookie ( COOKIE_POLL,$id,$expire,'/' );
								// put the user in the db
								if ( $GLOBALS['user']->loggedin () == true ) {
									$sql = "SELECT " . FIELD_POLL_VOTED_USERS . " FROM " . TBL_POLLS . " WHERE " 
										. FIELD_POLL_ID . "='" . $id . "' LIMIT 1";
									$query = $GLOBALS['database']->query ( $sql );
									if ( ! errorSDK::is_error ( $query ) ) {
										$poll = $GLOBALS['database']->fetch_array ( $query );
										$curuser = $GLOBALS['user']->getname ();
										$votednames = $poll[FIELD_POLL_VOTED_USERS];
										$votednames .= ';' . $curuser; // add user to the voted list
										// and put it now in the db
										$sql = "UPDATE " . TBL_POLLS . " set " 
											. FIELD_POLL_VOTED_USERS . "='" . $votednames 
											. "' WHERE " . FIELD_POLL_ID . "='"
											. $id . "' LIMIT 1";
										$query = $GLOBALS['database']->query ( $sql );
										if ( ! errorSDK::is_error ( $query ) ) {
											$error = $query;
										}
									}
								} else {
									// put the ip in that damn db
									// get current 
									
									if ( isset ( $error ) ) {
										return $error;
									} else {
										return true;
									}
								}
							} else {
								return $query;
							}
						} else {
							return $query;
						}
					} else {
						return $id;
					}
				} else {
					$error = new errorSDK ();
					$error->succeed = false;
					$error->error = $GLOBALS['lang']->polls->has_yet_voted;
					return $error;
				}
			} else {
				$error = new errorSDK ();
				$error->succeed = false;
				$error->error = $GLOBALS['lang']->users->form_not_filled_in;
				return $error;
			}
		} /* function vote ( $info ) */
		
		function userhasvoted () {
			$cookie = false;
			$ip = false;
			$user = false;
			$idcurpoll = $this->getidcurrentpollbylanguage ($this->config->getConfigByNameType('general/language',TYPE_STRING)); 
			// check cookie
			if ( isset ( $_COOKIE[COOKIE_POLL] ) ) {
				if ( $_COOKIE[COOKIE_POLL] == $idcurpoll ) {
					$cookie = true;
				}
			} 
			
			// check user
			if ( $GLOBALS['user']->loggedin () == true ) {
				$sql = "SELECT " . FIELD_POLL_VOTED_USERS . " FROM " . TBL_POLLS . " WHERE " 
					. FIELD_POLL_ID . "='" . $idcurpoll . "' LIMIT 1";
				$query = $GLOBALS['database']->query ( $sql );
				if ( ! errorSDK::is_error ( $query ) ) {
					$poll = $GLOBALS['database']->fetch_array ( $query );
					$curuser = $GLOBALS['user']->getname ();
					$users = explode ( ';', $poll[FIELD_POLL_VOTED_USERS]);
					foreach ( $users as $username ) {
						if ( $username == $curuser ) {
							$user = true;
							break; // if it is true it is useless to check the others
						}
					}
				} else {
					$error = $query;
				}
			}
			
			// check ip
			$sql = " SELECT " . FIELD_POLL_VOTED_IPS . " FROM " . TBL_POLLS . " WHERE "
				. FIELD_POLL_ID . "='" . $idcurpoll . "' LIMIT 1";
			$query = $GLOBALS['database']->query ( $sql );
			if ( ! errorSDK::is_error ( $query ) ) {
				if ( $GLOBALS['user']->loggedin () ) {
					$getips = $GLOBALS['user']->getips ();
					if ( errorSDK::is_error ( $getips ) ) {
						$userips[] = IP_USER;
						$error = $getips;
					} else {
						$userips = $getips;
					}
				} else {
					$userips[] = IP_USER;
				}
				
				$votedips = array ();
				while ( $votedip = $GLOBALS['database']->fetch_array ( $query ) ) {
					$votedips[] = $votedip[FIELD_POLL_VOTED_IPS];
				}
				/* normally $voteips is bigger, so i think it is going less time
				to check if a data out of big array is in a small array */
				foreach ( $votedips as $voteip ) {
					if ( in_array ( $voteip,$userips ) ) {
						$ip = true;
						break;
					}
				}
			} else {
				$error = $query;
			}
			
			
			if ( ( $cookie == true ) or ( $ip == true ) or ( $user == true ) ) {;
				return true;
			} else {
				if ( isset ( $error ) ) {
					return $error;
				} else {
					return false;
				}
			}
		}
	} /* class polls */
?>
