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
	public function __construct ($database,$mustactivate) {
		include_once ('kernel/users.constants.php');
		$this->database = $database;
		$this->mustactivate = $mustactivate;
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
	} /* public function __construct ($database,$mustactivate) */

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
				throw new exceptionlist ('Uknown userconfig',NULL,-1);
		}
	} /* public getconfig ($what) */

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

	public function getotherprofile ($username) {
		try {
			$sql = "SELECT * FROM " . TBL_USERS . " WHERE " 
				. FIELD_USERS_NAME . "='" . $username . "' AND " . FIELD_USERS_PUBLIC_USER . "='" .YES. "' LIMIT 1";
			$query = $this->database->query ($sql);
			if ($this->database->num_rows ($query) != 1) {
				// throw
			}
			$user = $this->database->fetch_array ($query);
			if ($user[FIELD_USERS_PUBLIC_PROFILE] == NO) {
				// throw
			}
			$sql = "SELECT * FROM " . TBL_USERS_PROFILE . " WHERE " 
				. FIELD_USERS_PROFILE_NAME . "='" . $username . "' LIMIT 1";
			$query = $this->database->query ($sql);
			$profile = $this->database->fetch_array ($query);
			$userprofile[FIELD_USERS_NAME] = $user[FIELD_USERS_NAME];
			$userprofile[FIELD_USERS_PROFILE_JOB] = $profile[FIELD_USERS_PROFILE_JOB];
			$userprofile[FIELD_USERS_PROFILE_INTRESTS] = $profile[FIELD_USERS_PROFILE_INTRESTS];
			$userprofile[FIELD_USERS_PROFILE_WEBSITE] = $profile[FIELD_USERS_PROFILE_WEBSITE];
			if ($user[FIELD_USERS_PUBLIC_CONTACT_INFO] == YES) {
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
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	}

	public function getips () {
		try {
			$sql = 'SELECT ' . FIELD_USERS_IP . ' FROM ' . TBL_USERS;
			$sql .= ' WHERE ' . FIELD_USERS_NAME . '=\'' . $this->getname () . '\'';
			$sql .= ' LIMIT 1';
			$query = $this->database->query ($sql);
			$user = $this->database->fetch_array ($query);
			$ips = explode (',',$user[FIELD_USERS_IP]);
			return $ips;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function getips () */

	public function loggedin () {
		if (!empty ($_SESSION[SESSION_NAME])) {
			try {
				$validlogin = $this->validlogin ();
				// this checks of the user has not hacked the session vars and
				// make him a root or other user
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
			$this->languageconfig = $this->getdbconfig (FIELD_USERS_LANGUAGE);
			$this->timezoneconfig =$this->getdbconfig (FIELD_USERS_TIMEZONE);
			$this->timeformatconfig = $this->getdbconfig (FIELD_USERS_TIMEFORMAT);
			$this->threadedconfig = $this->getdbconfig (FIELD_USERS_THREADED);
			$this->postsonpageconfig = $this->getdbconfig (FIELD_USERS_POSTSONPAGE);
			$this->headlinesconfig = $this->getdbconfig (FIELD_USERS_HEADLINES);
			$this->themeconfig = $this->getdbconfig (FIELD_USERS_THEME);
			return true;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function login ($name,$password) */

	public function logout () {
		try {
			if (! $this->loggedin ()) {
				throw new exceptionlist ("Not logged in",NULL,7);
			} else {
				unset ($_SESSION[SESSION_NAME]);
				unset ($_SESSION[SESSION_TYPE]);
				unset ($_SESSION[SESSION_PASSWORD]);
				session_unset ();
				return true;
			}
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function logout () */

	public function register ($username,$password,$controlpass,$email) {
			try {
				if ($password != $controlpass) {
					throw new exceptionlist ('Passwords are not the same');
				}
				// check username alreade exists
				$sql = 'SELECT ' . FIELD_USERS_NAME . ' FROM ' . TBL_USERS;
				$sql .= ' WHERE ' . FIELD_USERS_NAME . '=\''  . $username . '\'';
				$sql .= ' LIMIT 1';
				$query = $this->database->query ($sql);
				if ($this->database->num_rows ($query) != 0) {
					throw new exceptionlist ('User already exists, use another usrename');
				}
				// check email already exists
				$sql = "SELECT " . FIELD_USERS_EMAIL . " FROM " . TBL_USERS;
				$sql .= " WHERE " . FIELD_USERS_EMAIL . "='"  . $email . "'";
				$sql .= 'LIMIT 1';
				$query = $this->database->query ($sql);
				if ($this->database->num_rows ($query) != 0) {
					throw new exceptionlist ('Email is already registerd, use another email');
				}
				// i think everything is OK
				// user can be put into the db
				// get some standard config options
				$name = $username;
				$password = md5 ($password);
				$type = 'normal';
				$ip = IP_USER;
				if ($this->mustactivate == true ) {
					$activated = NO;
				} else {
					$activated = YES;
				}
				$sql = 'INSERT INTO ' . TBL_USERS;
				$fields = array (FIELD_USERS_NAME,FIELD_USERS_PASSWORD,
					FIELD_USERS_EMAIL,FIELD_USERS_TYPE,FIELD_USERS_ACTIVATE,FIELD_USERS_IP);
				$strfields = implode (',',$fields);
				$sql .= ' (' . $strfields . ')';
				$content = array ('\''.$name.'\'','\''.$password.'\'',
					'\''.$email.'\'','\''.$type.'\'','\''.$activated.'\'',
					'\''.$ip.'\'');
				$strcontent = implode (',',$content);
				$sql .= ' VALUES ( ' . $strcontent . ')';
				$query = $this->database->query ($sql);
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function register ($username,$password,$controlpass,$email) */

	// TODO implement on check current password
	public function setnewpassword ($password1,$password2,$curpassword = NULL) {
		try {
			$exception = NULL;
			if ($password1 != $password2) {
				throw new exceptionlist ('Password not changed, 2 paswwords are not equal');
			}
			$sql = 'UPDATE ' . TBL_USERS . ' SET ' . FIELD_USERS_PASSWORD;
			$sql .= '=\'' . md5($password1) . '\'';
			$sql .= 'WHERE ' . FIELD_USERS_NAME . '=\'' . $_SESSION[SESSION_NAME] . '\'';
			$query = $this->database->query ($sql);
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function setnewpassword ($password1,$password2,$curpassword = NULL) */

	private function randompassword () {
		mt_srand ((double) microtime () * 1000000);
		$password = NULL;

		while (strlen ($password) <= PASSWORD_LENGTH) {
			$i = chr (mt_rand(0,255)); 
			if (eregi("^[a-z0-9A-Z]$",$i)) { // only add it if it is a-z,A-Z or 0-9
				$password .= $i; 
			}
			return md5 ($password); 
		}
	} /* private function randompassword () */

	public function lostpasw ($mail,$username) {
		try {
			if (($mail == NULL) and ($username == NULL)) {
				throw new exceptionlist ('Both are given, give only username or email');
			} else if ($mail != NULL) {
				// mail is given
				$sql = 'SELECT ' . FIELD_USERS_NAME . ',' . FIELD_USERS_PASSWORD
					. ' FROM ' . TBL_USERS;
				$sql .= ' WHERE ' . FIELD_USERS_EMAIL . '=\'' . $mail . '\''; 
				$query = $this->database->query ($sql);
				if ($this->database->num_rows ($query) == 0) {
					throw new exceptionlist ('EMail not found');
				}
				$user = $this->database->fetch_array ($query);
				$password = $this->randompassword ();
				$this->setnewpassword ($password,$password);
				//FIXME mail
			} else if ($username != NULL) {
				$sql = "SELECT " . db_email . " FROM users WHERE " . db_name . "='" .$_POST[POST_NAME] ."'"; 
				$query = $this->database->query ($sql);
				if ($this->database->num_rows ($query) == 0) {
					throw new exceptionlist ('Username not found');
				}
				$password = $this->randompassword ();
				$this->setnewpassword ($password,$password);
				// FIXME mail;
			} else {
				// NONE given
				throw new exceptionlist ('Fill e-mail or username in');
			}
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function lostpasw ($mail,$username) */
 
	private function getName () {
		if (!$this->loggedin ()) {
			return NULL;
		} else {
			return $_SESSION[SESSION_NAME];
		}
	} /* private function getName () */

	// why is this a seperate function?
	private function getEmail () {
		return $this->getdbconfig (FIELD_USERS_EMAIL);
	} /* private function getEmail () */

	private function getdbconfig ($db_field) {
		try {
			if ($this->loggedin () == true) {
				$sql = 'SELECT ' . $db_field . ' FROM ' . TBL_USERS . ' WHERE ' 
					. FIELD_USERS_NAME . '=\'' . $_SESSION[SESSION_NAME] . '\' LIMIT 1';
				$query = $this->database->query ($sql);
				$user = $this->database->fetch_array ($query);
				return $user[$db_field];
			} else {
				// do no throw because it is used every time so return false
				// throw new exceptionlist ('Access denied');
				return false;
			}
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* private function getdbconfig ($db_field) */

	public function setconfig ($db_what,$value) {
		try {
			$sql = 'UPDATE ' . TBL_USERS . ' SET ' . $db_what . '=\'' . $value . '\'';
			$sql .= ' WHERE ' .  FIELD_USERS_NAME . '=\'' . $_SESSION[SESSION_NAME] . '\'';
			$query = $this->database->query ($sql);
			if (($this->mustactivate == true) and ($db_what == FIELD_USERS_EMAIL)){
				$this->setconfig (FIELD_USERS_ACTIVATE,NO);
			}
			return true;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function setconfig ($db_what,$value) */

	public function hasSetConfig () {
		try {
			if (($this->languageconfig == '') or ($this->themeconfig == '') 
			or ($this->timezoneconfig == '') or ($this->timeformatconfig == '')
			or ($this->threadedconfig == '') or ($this->postsonpageconfig == '') 
			or ($this->headlinesconfig == '')) {
				return false;
			} else {
				return true;
			}
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* puclic function hasSetConfig () */
} // clas user
?>
