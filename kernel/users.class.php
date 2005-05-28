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
if (!defined ('USE_YAPCASUSER')) {
	define ('USE_YAPCASUSER',true);
}

if (!defined ('EXCEPTION_CLASS')) {
	include ('kernel/exception.class.php');
}

class user { 
	public function __construct ($database) {
		include_once ('kernel/users.constants.php');
		$this->database = $database;

		try {
			$this->languageconfig = $this->getdbconfig (FIELD_USERS_LANGUAGE);
			$this->timezoneconfig =$this->getdbconfig (FIELD_USERS_TIMEZONE);
			$this->timeformatconfig = $this->getdbconfig (FIELD_USERS_TIMEFORMAT);
			$this->threadedconfig = $this->getdbconfig (FIELD_USERS_THREADED);
			$this->postsonpageconfig = $this->getdbconfig (FIELD_USERS_POSTSONPAGE);
			$this->headlinesconfig = $this->getdbconfig (FIELD_USERS_HEADLINES);
			$this->themeconfig = $this->getdbconfig (FIELD_USERS_THEME);
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function __construct ($database) */

	public function getconfig ($what) {
		switch ($what) {
			case 'language':
				return $this->languageconfig;
				break;
			case 'timezone':
				return $this->timezoneconfig;
				break;
			case 'timeformat':
				return $this->timeformatconfig;
				break;
			case 'threaded':
				return $this->threadedconfig;
				break;
			case 'postsonpage':
				return $this->postsonpageconfig;
				break;
			case 'headlines':
				return $this->headlinesconfig;
				break;
			case 'theme':
				return $this->themeconfig;
				break;
			case 'email':
				return $this->getEmail ();
				break;
			case 'name':
				return $this->getName ();
				break;
			default:
				echo $what;
				throw new exceptionlist ('Uknown userconfig',NULL,-1);
		}
	} /* public getconfig ($wat) */

	private function validlogin () {
		try {
			// this check of a hacker did not change the sessions on the server
			// to get another username or a type
			$sql = "SELECT " . FIELD_USERS_NAME . " FROM " . TBL_USERS;
			$sql .= " WHERE " . FIELD_USERS_NAME . "='" . $_SESSION[SESSION_NAME]; 
			$sql .= "' AND " . FIELD_USERS_PASSWORD . "='" .$_SESSION[SESSION_PASSWORD];
			$sql .= "' AND " . FIELD_USERS_TYPE . "='" . $_SESSION[SESSION_TYPE] . "'";
			$query = $this->database->query ($sql);
			if ($this->database->num_rows ($query) == 0) {
				session_unset ();
				return false;
			} else {
				return true;
			}
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* private function validlogin () */

	// FIXME rewrite this function
	public function getotherprofile ($username) {
		$sql = "SELECT * FROM " . TBL_USERS . " WHERE " 
			. FIELD_USERS_NAME . "='" . $username . "' AND " . FIELD_USERS_PUBLIC_USER . "='Y' LIMIT 1";
		$query = $GLOBALS['database']->query ($sql);
		if ( ! errorSDK::is_error ( $query ) ) {
			if ( $GLOBALS['database']->num_rows ( $query ) == 1 ) {
				$user = $GLOBALS['database']->fetch_array ( $query );
				if ( $user[FIELD_USERS_PUBLIC_PROFILE] == YES ) {
					$sql = "SELECT * FROM " . TBL_USERS_PROFILE . " WHERE " 
						. FIELD_USERS_PROFILE_NAME . "='" . $username . "' LIMIT 1";
					$query = $GLOBALS['database']->query ( $sql );
					if ( ! errorSDK::is_error ( $query ) ) {
						$profile = $GLOBALS['database']->fetch_array ( $query );
						$userprofile[FIELD_USERS_NAME] = $user[FIELD_USERS_NAME];
						$userprofile[FIELD_USERS_PROFILE_JOB] = $profile[FIELD_USERS_PROFILE_JOB];
						$userprofile[FIELD_USERS_PROFILE_INTRESTS] = $profile[FIELD_USERS_PROFILE_INTRESTS];
						$userprofile[FIELD_USERS_PROFILE_WEBSITE] = $profile[FIELD_USERS_PROFILE_WEBSITE];
						if ( $user[FIELD_USERS_PUBLIC_CONTACT_INFO] == YES ) {
							$userprofile[FIELD_USERS_PROFILE_AIM] = $profile[FIELD_USERS_PROFILE_AIM];
							$userprofile[FIELD_USERS_PROFILE_MSN] = $profile[FIELD_USERS_PROFILE_MSN];
							$userprofile[FIELD_USERS_PROFILE_YAHOO] = $profile[FIELD_USERS_PROFILE_YAHOO];
							$userprofile[FIELD_USERS_EMAIL] = $user[FIELD_USERS_EMAIL];
							$userprofile[FIELD_USERS_PROFILE_ICQ] = $profile[FIELD_USERS_PROFILE_ICQ];
							$userprofile[FIELD_USERS_PROFILE_ADRESS] = $profile[FIELD_USERS_PROFILE_ADRESS];
							$userprofile[FIELD_USERS_PROFILE_JABBER] = $profile[FIELD_USERS_PROFILE_JABBER];
						} else {
							$userprofile[FIELD_USERS_PROFILE_AIM] = $GLOBALS['lang']->users->not_public_contact_info;
							$userprofile[FIELD_USERS_PROFILE_MSN] = $GLOBALS['lang']->users->not_public_contact_info;
							$userprofile[FIELD_USERS_PROFILE_YAHOO] = $GLOBALS['lang']->users->not_public_contact_info;
							$userprofile[FIELD_USERS_PROFILE_EMAIL] = $GLOBALS['lang']->users->not_public_contact_info;
							$userprofile[FIELD_USERS_PROFILE_ICQ] = $GLOBALS['lang']->users->not_public_contact_info;
							$userprofile[FIELD_USERS_PROFILE_ADRESS] = $GLOBALS['lang']->users->not_public_contact_info;
							$userprofile[FIELD_USERS_PROFILE_JABBER] = $GLOBALS['lang']->users->not_public_contact_info;
						}
						return $userprofile;
					} else {
						return $query;
					}
				} else {
					$error = new errorSDK ();
					$error->succeed = false;
					$error->error = $GLOBALS['lang']->users->not_public_profile;
					return $error;
				}
			} else {
				$error = new errorSDK ();
				$error->succeed = false;
				$error->error = $GLOBALS['lang']->users->not_valid_user;
				return $error;
			}
		} else {
			return $query;
		}
	}

	public function getips () {
		try {
			$sql = "SELECT  " . FIELD_USERS_IP . " FROM " . TBL_USERS 
				. " WHERE " . FIELD_USERS_NAME . "='" . $this->getname () . "' LIMIT 1";
			$query = $this->database->query ($sql);
			$user = $this->database->fetch_array ($query);
			$ips = explode ( ',',$user[FIELD_USERS_IP] );
			return $ips;
		}
		catch (listexception $e) {
			throw $e;
		}
	} /* public function getips () */

	public function loggedin () {
		if (!empty ($_SESSION[SESSION_NAME])) {
			try {
				$validlogin = $this->validlogin (); 
				// this checks of the user has not hacked the session vars and make him a root or other user
				return $validlogin;
			}
			catch (listexception $e) {
				throw $e;
			}
		} else {
			return false;
		}
	} /* public function loggedin () */

	public function login ($name,$password) {
		try {
			$exception = NULL;
			$sql = 'SELECT * FROM ' . TBL_USERS;
			$sql .= ' WHERE ' . FIELD_USERS_NAME . '=\'' .$name. '\'';
			$query = $this->database->query ($sql);
			if ($this->database->num_rows ($query) != 1) {
				$exception = new exceptionlist ('User does not exists',NULL,4);
			}
			// since we have only __one__ result it must not be in a loop
			$user = $this->database->fetch_array ($query);
			if ($user[FIELD_USERS_PASSWORD] != md5($password)) {
				$e = new exceptionlist ('Your password is wrong',NULL,5);
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if ($exception != NULL) {
				throw $exception;
			}
			if ($user[FIELD_USERS_BLOCKED] == YES) {
				throw new exceptionlist ('Your username is blocked');
			}
			if ($user[FIELD_USERS_ACTIVATE] == NO) {
				throw new exceptionlist ('Your username is not (yet) activated,' .
				'check your mail to activate it');
			}
			$userip = $user[FIELD_USERS_IP];
			$userip = explode (',',$userip);
			if (!in_array (IP_USER,$userip)) {
				$userip[] = IP_USER;
			}
			foreach ($userip as $ip) {
				$sql = 'SELECT ' . FIELD_IPBLOCKS_IP  . ' FROM ' . TBL_IPBLOCKS;
				$sql .= ' WHERE ' . FIELD_IPBLOCKS_IP . '=\'' . $ip . '\'';
				$sql .= 'LIMIT 1';
				$query = $this->database->query ($sql,false);
				if ($this->database->num_rows ($query) != 0) {
					throw new exceptionlist ('Your IP is blocked');
				}
			}
			$sql = 'UPDATE ' . TBL_USERS;
			$sql .= ' SET ' . FIELD_USERS_IP . '=\'' . implode(',',$userip) . '\'';
			$sql .= 'WHERE ' . FIELD_USERS_NAME . '=\'' . $name . '\'';
			$this->database->query ($sql,false);
			$_SESSION[SESSION_NAME] = $user[FIELD_USERS_NAME];
			$_SESSION[SESSION_TYPE] = $user[FIELD_USERS_TYPE];
			$_SESSION[SESSION_PASSWORD] = $user[FIELD_USERS_PASSWORD];
			return true;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function login ($name,$password) */

	/*function login ( $inpuser ) {
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
									if ( errorSDK::is_error ( $query ) ) {
										$error = $query;
									} else {
										if ( $GLOBALS['database']->num_rows ( $query ) != 0 ) {
											$blocked = true;
										}
									}
								}

								if ( isset ( $error ) ) {
									$queryerror = $query;
								} else {
									$queryerror = NULL;
								}
								if ( $blocked == false ) {
									$_SESSION[SESSION_NAME] = $user[FIELD_USERS_NAME];
									$_SESSION[SESSION_TYPE] = $user[FIELD_USERS_TYPE];
									$_SESSION[SESSION_PASSWORD] = $user[FIELD_USERS_PASSWORD];
									$return = new errorSDK ();
									$return->succeed = true;
									$return->message = $GLOBALS['lang']->users->logged_in;
									return array ( $return,$queryerror );
								} else {
									$error = new errorSDK ();
									$error->succeed = false;
									$error->error = $GLOBALS['lang']->users->blocked;
									return array ( $error,$queryerror );
								}
							} else {
								$error = new errorSDK ();
								$error->succeed = false;
								$error->error = $GLOBALS['lang']->users->blocked;
								return array ( $error );
							}
						} else {
							$error = new errorSDK ();
							$error->succeed = false;
							$error->error = $GLOBALS['lang']->users->not_activated;
							return array ( $error );
						}
					} else {
						$error = new errorSDK ();
						$error->succeed = false;
						$error->error = $GLOBALS['lang']->users->wrong_password;
						return array ( $error );
					}
				} else {
					$error = new errorSDK ();
					$error->succeed = false;
					$error->error = $GLOBALS['lang']->users->wrong_username;
					return array ( $error );
				}
			} else {
				return array ( $query );
			}
		} else {
			$error = new errorSDK ();
			$error->succeed = false;
			$error->error = $GLOBALS['lang']->users->form_not_filled_in; 
			return array ( $error );
		}
	} // function login */

	public function logout () {
		try {
			if (!$this->loggedin ()) {
				throw new exceptionlist ("Not logged in",NULL,7);
			} else {
				unset ($_SESSION[SESSION_NAME]);
				unset ($_SESSION[SESSION_TYPE]);
				unset ($_SESSION[SESSION_PASSWORD]);
				session_unset();
				return true;
			}
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function logout () */

	public function register ($username,$password,$controlpass,$email,$activate,$language,
	$theme,$threaded,$postsonpage,$timeformat,$timezone,$headlines) {
			try {
				if ($password != $controlpass) {
					throw new exceptionlist ("Passwords are not the same");
				}
				// check username alreade exists
				$sql = "SELECT " . FIELD_USERS_NAME . " FROM " . TBL_USERS;
				$sql .= " WHERE " . FIELD_USERS_NAME . "='"  . $username . '\'';
				$sql .= " LIMIT 1";
				$query = $this->database->query ($sql);
				if ($GLOBALS['database']->num_rows ($query) != 0) {
					throw new exceptionlist ("User already exists, use another usrename");
				}
				// check email already exists
				$sql = "SELECT " . FIELD_USERS_EMAIL . " FROM " . TBL_USERS;
				$sql .= " WHERE " . FIELD_USERS_EMAIL . "='"  . $email . "'";
				$sql .= 'LIMIT 1';
				$query = $this->database->query ($sql);
				if ($this->database->num_rows ($query) != 0) {
					throw new exceptionlist ("Email is already registerd, use another email");
				}
				// i think everything is OK
				// user can be put into the db
				// get some standard config options
				$name = $username;
				$password = md5 ($password);
				$type = 'normal';
				$ip  = IP_USER;
				if ($activate == true ) {
					$activated = NO;
				} else {
					$activated = YES;
				}
				$sql = "INSERT INTO " .  TBL_USERS . " (" . FIELD_USERS_NAME .',' . FIELD_USERS_PASSWORD . ',' . FIELD_USERS_EMAIL .',' 
					. FIELD_USERS_TYPE .','.FIELD_USERS_LANGUAGE.','.FIELD_USERS_THEME.','.FIELD_USERS_THREADED.
					','.FIELD_USERS_POSTSONPAGE.','.FIELD_USERS_TIMEZONE.','.FIELD_USERS_TIMEFORMAT . ',' . FIELD_USERS_ACTIVATE
					.','.FIELD_USERS_HEADLINES.','.FIELD_USERS_IP.") VALUES ('$name', '$password', '$email', '$type',
					'$language', '$theme', '$threaded', '$postsonpage','$timezone','$timeformat','$activated','$headlines','$ip')";
				$query = $this->database->query ($sql);
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function register ($username,$password,$controlpass,$email,$activate,$language,
	$theme,$threaded,$postsonpage,$timeformat,$timezone,$headlines) */

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

		while ( strlen( $password ) <= PASSWORD_LENGTH) {
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
 
	private function getName () {
		if (!$this->loggedin ()) {
			return NULL;
		} else {
			return $_SESSION[SESSION_NAME];
		}
	} /* private function getName () */

	private function getEmail () {
		return 'email';
		if (!$this->loggedin ()) {
			return NULL;
		} else {
			// FIXME
			return 'email';
		}
	} /* private function getEmail () */

	function getdbconfig ($db_field) {
		if ($this->loggedin () == true) {
			$sql = "SELECT " . $db_field . " FROM " . TBL_USERS . " WHERE " . FIELD_USERS_NAME ."='" . $_SESSION[SESSION_NAME] . "' LIMIT 1";
			$query = $this->database->query ($sql);
			if (errorSDK::is_error ($query)) {
				return $query;
			} else {
				$user = $this->database->fetch_array ($query);
				return $user[$db_field];
			}
		} else {
			// FIXME
			return false;
		}
	}

	function setconfig ($db_what,$value) {
		if (!$this->loggedin ()) {
			$error = new erroSDK ();
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
