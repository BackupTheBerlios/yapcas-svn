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
	class user { 
		function user ( $database,$config ) {
			include_once ( 'kernel/users.constants.php' );
			$this->database = $database;
			$this->config = $config;
			
			//FIXME can errors return
			$this->languageconfig = $this->getconfig ( $this->config->site->language,FIELD_USERS_LANGUAGE,'',get_language,cookie_language );
			$this->timezoneconfig =$this->getconfig ( $this->config->site->timezone,FIELD_USERS_TIMEZONE,'',get_timezone,cookie_timezone );
			$this->timeformatconfig = $this->getconfig ( $this->config->site->timeformat,FIELD_USERS_TIMEFORMAT,'',get_timeformat,cookie_timeformat );
			$this->threadedconfig = $this->getconfig ( $this->config->news->standard->view,FIELD_USERS_THREADED,'',get_threaded,cookie_threaded );
			$this->postsonpageconfig = $this->getconfig (  $this->config->news->postsonpage,FIELD_USERS_POSTSONPAGE,'',get_postsonpage,cookie_postsonpage );
			$this->headlinesconfig = $this->getconfig ( $this->config->news->headlines,FIELD_USERS_HEADLINES,'',get_headlines,cookie_headlines );
			$this->themeconfig = $this->getconfig ( $this->config->site->theme,FIELD_USERS_THEME,'',get_theme,cookie_theme );
		} // function user
		
		function gettimezone () {
			return $this->timezoneconfig;
		}
		
		function gettimeformat () {
			return $this->timeformatconfig;
		}
		
		function getthreaded () {
			return $this->threadedconfig;
		}
		
		function getlanguage () {
			return $this->languageconfig;
		}
		
		function getpostsonpage () {
			return $this->postsonpageconfig;
		}
		
		function getheadlines () {
			return $this->headlinesconfig;
		}
		
		function gettheme () {
			return $this->themeconfig;
		}
		
		function validlogin () {
			//FIXME return value loggedin
			$sql = "SELECT " . FIELD_USERS_NAME . " FROM " . TBL_USERS . " WHERE " . FIELD_USERS_NAME . "='" . $_SESSION[SESSION_NAME] . "' AND " . FIELD_USERS_PASSWORD . "='" .$_SESSION[SESSION_PASSWORD] . "' AND " . FIELD_USERS_TYPE . "='" . $_SESSION[SESSION_TYPE] . "'";
			$query = $this->database->query ( $sql );
			if ( errorSDK::is_error ( $query ) ) {
				session_unset ();
				return false;
			} else {
				if ( $this->database->countresults ( $query ) == 0 ) {
					session_unset ();
					return false;
				} else {
					return true;
				}
			}
		}
		
		function getips () {
			$sql = " SELECT  " . FIELD_USERS_IP . " FROM " . TBL_USERS 
				. " WHERE " . FIELD_USERS_NAME . "='" . $this->getname () . "' LIMIT 1";
			$query = $GLOBALS['database']->query ( $sql );
			if ( errorSDK::is_error ( $query ) ) {
				return $query;
			} else {
				$user = $GLOBALS['database']->fetch_array ( $query );
				$ips = explode ( ',',$user[FIELD_USERS_IP] );
				return $ips;
			}
		}
		
		function loggedin () {
			if ( ! empty ( $_SESSION[SESSION_NAME] ) ) {
				return $this->validlogin (); // this checks of the user has not hacked the session vars and make him a root or other user
			} else {
				return false;
			}
		} // function loggedin
		
		function login ( $inpuser ) {
			if ( ! ( ( empty ( $inpuser[POST_NAME] ) ) or ( empty ( $inpuser[POST_PASSWORD] ) ) ) ) {
				// get the user info out of the database
				$sql = 'SELECT * FROM ' .  TBL_USERS . ' WHERE ' . FIELD_USERS_NAME . '=\'' . $inpuser[POST_NAME] . '\' LIMIT 1';
				$query = $GLOBALS['database']->query ( $sql );
				if ( ! errorSDK::is_error ( $query ) ) {
					if ( ! $GLOBALS['database']->num_rows ( $query ) == 0 ) {
						$user = $GLOBALS['database']->fetch_array ( $query );
						if ( $user[FIELD_USERS_PASSWORD] == md5 ( $inpuser[POST_PASSWORD] ) ) {
							// see of user is blocked / not activated
							if ( $user[FIELD_USERS_ACTIVATE] == YES ) {
								if ( $user[FIELD_USERS_BLOCKED] == NO ) {
									$blocked = false; // beleave in the goodwill of people
									// put the current IP in the users ip
									$userip = explode ( ',',$user[FIELD_USERS_IP] );
									foreach ( $userip as $ip ) {
										$sql = "SELECT " . FIELD_IPBLOCKS_IP  . " FROM " . TBL_IPBLOCKS 
											. " WHERE " . FIELD_IPBLOCKS_IP . "='" . $ip
											. "' LIMIT 1";
										$query = $GLOBALS['database']->query ( $sql,false );	
										if ( ! errorSDK::is_error ( $query ) ) {
											$error = $query;
										} else {
											if ( $GLOBALS['database']->num_rows ( $query ) != 0 ) {
												$blocked = true;
											}
										}
									}
									
									if ( isset ( $error ) ) {
										$error = $query;
									}
									if ( $blocked == false ) {
										$_SESSION[SESSION_NAME] = $user[FIELD_USERS_NAME];
										$_SESSION[SESSION_TYPE] = $user[FIELD_USERS_TYPE];
										$_SESSION[SESSION_PASSWORD] = $user[FIELD_USERS_PASSWORD];
										return true;
									} else {
										$error = new errorSDK ();
										$error->succeed = false;
										$error->error = $GLOBALS['lang']->users->blocked;
										return $error;
									}
								} else {
									$error = new errorSDK ();
									$error->succeed = false;
									$error->error = $GLOBALS['lang']->users->blocked;
									return $error;
								}
							} else {
								$error = new errorSDK ();
								$error->succeed = false;
								$error->error = $GLOBALS['lang']->users->not_activated;
								return $error;
							}
						} else {
							$error = new errorSDK ();
							$error->succeed = false;
							$error->error = $GLOBALS['lang']->users->wrong_password;
							return $error;
						}
					} else {
						$error = new errorSDK ();
						$error->succeed = false;
						$error->error = $GLOBALS['lang']->users->wrong_username;
						return $error;
					}
				} else {
					return $query;
				}
			} else {
				$error = new errorSDK ();
				$error->succeed = false;
				$error->error = $GLOBALS['lang']->users->form_not_filled_in; 
				return $error;
			}
		} // function login
		
		function logout () {
			$error = new error;
			if ( ! $this->loggedin () ) {
				$error->succeed = false;
				$error->database = false;				
				$error->error = $GLOBALS['lang']->users->not_logged_in;
			} else {
				unset ( $_SESSION[SESSION_NAME] );
				unset ( $_SESSION[SESSION_TYPE] );
				unset ( $_SESSION[SESSION_PASSWORD] );
				session_unset();
				$error->succeed = true;
			}
			return $error;
		} // function logout
		
		function register ( $inputuser ) {
			if ( ! ( ( empty( $inputuser[POST_NAME] ) ) or ( empty( $inputuser[POST_EMAIL] ) )or 
			( empty ( $inputuser[POST_PASSWORD1] ) ) or (  empty ( $inputuser[POST_PASSWORD2])))){
				if ( $inputuser[POST_PASSWORD1] == $inputuser[POST_PASSWORD2]) {
					// check username alreade exists
					$sql = "SELECT " . FIELD_USERS_NAME . " FROM " . TBL_USERS . " WHERE " 
						. FIELD_USERS_NAME . "='"  . $inputuser[POST_NAME] . "' LIMIT 1";
					$query = $GLOBALS['database']->query ( $sql );
					if ( ! errorSDK::is_error ( $query ) ) {
						if ( $GLOBALS['database']->num_rows ( $query ) == 0 ) {
							// check email already exists
							$sql = "SELECT " . FIELD_USERS_EMAIL . " FROM " . TBL_USERS . " WHERE " 
								. FIELD_USERS_EMAIL . "='"  . $inputuser[POST_EMAIL] . "' LIMIT 1";
							$query = $GLOBALS['database']->query ( $sql );
							if ( ! errorSDK::is_error ( $query ) ) {
								if ( $GLOBALS['database']->num_rows ( $query ) == 0 ) {
									// i think everything is OK
									// user can be put into the db
									// mh check firts check of activating the user is needed
									if ( $GLOBALS['config']->users->activate == false ) {
										// get some standard config options
										$name = $inputuser[POST_NAME];
										$password = md5 ( $inputuser[POST_PASSWORD1] );
										$email = $inputuser[POST_EMAIL];
										$type = 'normal';
										$language = $this->getlanguage ();
										$theme = $this->gettheme ();
										$threaded = $this->getthreaded ();
										$postsonpage = $this->getpostsonpage ();
										$timezone = $this->gettimezone ();
										$timeformat = $this->gettimeformat ();
										$headlines = $this->getheadlines ();
										$ip  = IP_USER;
										$sql = "INSERT INTO " .  TBL_USERS . " (" . FIELD_USERS_NAME .',' . FIELD_USERS_PASSWORD . ',' . FIELD_USERS_EMAIL .',' 
											. FIELD_USERS_TYPE .','.FIELD_USERS_LANGUAGE.','.FIELD_USERS_THEME.','.FIELD_USERS_THREADED.
											','.FIELD_USERS_POSTSONPAGE.','.FIELD_USERS_TIMEZONE.','.FIELD_USERS_TIMEFORMAT
											.','.FIELD_USERS_HEADLINES.','.FIELD_USERS_IP.") VALUES ('$name', '$password', '$email', '$type', 
											'$language', '$theme', '$threaded', '$postsonpage','$timezone','$timeformat','$headlines','$ip')";
										echo 'SQL:: ' . $sql;
										$query = $GLOBALS['database']->query ( $sql );
										if ( ! errorSDK::is_error ( $query ) ) {
											return true;
										} else {
											return $query;
										} 
									} else {
										// FIXME
										// IMPLEMENT ME
										echo 'NYI';
									}
								} else {
									$error = new errorSDK ();
									$error->succeed = false;
									$error->error = 
										$GLOBALS['lang']->users->email_already_registered;
									return $error;
								}
							} else {
								return $query;
							}
						} else {
							$error = new errorSDK ();
							$error->succeed = false;
							$error->error = $error->error = 
								$GLOBALS['lang']->users->username_already_registered;
							return $error;
						}
					} else {
						return $query;
					}
				} else {
					$error = new errorSDK;
					$error->succeed = false;
					$error->error = $GLOBALS['lang']->users->pasw_not_equel;
					return $error;
				}
			} else {
				$error = new errorSDK ();
				$error->succeed = false;
				$error->error = $GLOBALS['lang']->users->form_not_filled_in;
				return $error;
			}
		} // function register
		
		
		function setnewpassword ( $password1,$password2 ) {
			$error = new error ();
			if ( $password1 != $password2 ) {
				$error->succeed = false;
			} else {
				$error->succeed = true;
				$sql = "UPDATE users SET " . db_password . "='" . md5( $password1 ) . "' WHERE " . db_name . "='" . $_SESSION[SESSION_NAME] . "'";
				$query = $this->database->query ( $sql );
			}
			return $error;
		}
		
		function randompassword () {
        		mt_srand ( ( double ) microtime () * 1000000 );
			$password = NULL;
	
			while ( strlen( $password ) <= PASSWORD_LENGTH)
			{
				$i = chr ( mt_rand ( 0,255 ) ); 
				if ( eregi("^[a-z0-9]$",$i) ) 
					$password .= $i; 
        		}
        		return md5 ( $password ); 
		} 
		
		function lostpasw () {
			$error = new error;
		 	if ( ( ! empty ( $_POST[POST_EMAIL] ) ) AND ( ! empty ( $_POST[POST_NAME] ) ) ) {
		 		//both are filled in -> error
		 		$error->succeed = false;
		 		$error->error = $GLOBALS['lang']->users->give_user_or_mail;
		 	} else {
		 		// none of them is filled in -> error
		 		if ( ( empty ( $_POST[POST_EMAIL] ) ) AND ( ( empty ( $_POST[POST_NAME] ) ) ) ) {
		 			$error->succeed = false;
		 			$error->error = $GLOBALS['lang']->users->give_user_or_mail;
		 		} else {
		 			if ( ! empty ( $_POST[POST_EMAIL] ) ) {
		 				// mail is given
		 				// search name and change password
		 				$sql = "SELECT " . db_name . "," . db_password . " FROM users WHERE " . db_email . "='" . $_POST[POST_email] . "'"; 
		 				$query_error = $this->database->query ( $sql );
		 				if ( $query_error->succeed != true ) {
		 					$error->succeed = false;
		 					$error->fatal = true;
		 					$error->database = true;
		 					$error->error = $query_error->error;
		 				} else {
			 				if ( $this->database->countresults ( $query_error->value ) == 0 ) {
			 					$error->succeed = false;
			 					$error->error = $GLOBALS['lang']->users->mail_not_found;
			 				} else {
			 					while ( $user = $this->database->fetch_array ( $query_error->value ) ) {
			 						//FIXME mail + change password
			 					}
			 					$error->succeed = true;
			 				}
		 				}
		 			} else {
		 				// name is given
		 				// replace password and mail it
		 				$sql = "SELECT " . db_email . " FROM users WHERE " . db_name . "='" .$_POST[POST_NAME] ."'"; 
		 				$query_error = $this->database->query ( $sql );
		 				if ( $query_error->succeed != true ) {
		 					$error->succeed = false;
		 					$error->fatal = true;
		 					$error->database = true;
		 					$error->error = $query_error->error;
		 				} else {
			 				if ( $this->database->countresults ( $query_error->value ) == 0 ) {
			 					$error->succeed = false;
			 					$error->error = $GLOBALS['lang']->users->user_not_found;
			 				} else {
			 					$password = $this->randompassword ();
			 					$sql = "UPDATE users SET " . db_password . "='$password' WHERE " . db_name . "='" . $_POST[POST_NAME] . "'";
			 					$query_error = $this->database->query ( $sql );
			 					if ( $query_error->succeed == false ) {
			 						$error->succeed = false;
			 						$error->database = true;
			 						$error->error = $GLOBALS['lang']->error_in_db;
			 					} else {
			 						// mail;
			 						$error->succeed = true;
			 					}
		 					}
		 				}
		 			}
		 		}
		 	}
		 	return $error;
		 } // function lostpasw
		 
		 function getname () {
			if ( ! $this->loggedin () ) {
				return NULL;
			} else {
				return $_SESSION[SESSION_NAME];
			}
		}
		
		function getemail () {
			return 'email';
			if ( ! $this->loggedin () ) {
				return NULL;
			} else {
				return 'email';
			}
		}


		function getconfig ( $what,$db_what,$SESSION_what,$get_what,$cookie_what ) {
			if ( ! empty ( $_GET[$get_what] ) ) {
				return $_GET[$get_what];
			} else {
				if ( $this->loggedin () == true ) {
					$sql = "SELECT " . $db_what . " FROM " . TBL_USERS . " WHERE " . FIELD_USERS_NAME ."='" . $_SESSION[SESSION_NAME] . "' LIMIT 1";
					$query = $this->database->query ( $sql );
					if ( errorSDK::is_error ( $query ) ) {
						return $query;
					} else {
						$user = $this->database->fetch_array ( $query );
						return $user[$db_what];
					}
				} else {
					if ( ! empty ( $_COOKIE[$cookie_what] ) ) {
						return $_COOKIE[$cookie_what];
					} else {
						return $what;
					}
				}
			}
		}
		
		function setconfig ( $db_what,$value ) {
			if ( ! $this->loggedin () ) {
				$error = new erroSDKr ();
				$error->succeed = false;
				$error->error = $GLOBALS['lang']->users->not_logged_in;
				return $error;
			} else {
				$sql = "UPDATE " . TBL_USERS . " SET " . $db_what . "='" . $value . "' WHERE " .  FIELD_USERS_NAME . "='" . $_SESSION[SESSION_NAME] . "'";
				$query = $this->database->query ( $sql );
				if ( errorSDK::is_error ( $query ) ) {
					return $error;
				} else {
					return true;
				}
			}
		}
		
		function convert_config2db ( $what,$value ) {
			switch ( $what ) {
				case CONFIG_THREADED:
					switch ( $value ) {
						case $GLOBALS['lang']->site->yes:
							$value = 'Y';
							break;
						case $GLOBALS['lang']->site->no:
							$value = 'N';
							break;
					}
					break;
			}
			return $value;
		}
		
		function convert_db2config ( $what,$value ) {
			switch ( $what ) {
				case CONFIG_THREADED:
					switch ( $value ) {
						case 'Y':
							$value = $GLOBALS['lang']->site->yes;
							break;
						case 'N':
							$value = $GLOBALS['lang']->site->no;
							break;
					}
					break;
			}
			return $value;
		}
		
	} // clas user
?>
