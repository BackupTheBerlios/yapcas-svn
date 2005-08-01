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
define ('ACTIVATE_ID_LENGTH',32);
class user { 
	public function __construct ($database,$mustactivate,$lang) {
		include_once ('kernel/users.constants.php');
		$this->database = $database;
		$this->mustactivate = $mustactivate;
		$this->lang = $lang;
		try {
			$this->updateConfig ();
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function __construct ($database,$mustactivate,$lang) */

	public function updateConfig () {
		$this->isloggedin = $this->isLoggedIn ();
		$this->languageconfig = $this->getdbconfig (FIELD_USERS_PROFILE_LANGUAGE);
		$this->timezoneconfig = $this->getdbconfig (FIELD_USERS_PROFILE_TIMEZONE);
		$this->timeformatconfig = $this->getdbconfig (FIELD_USERS_PROFILE_TIMEFORMAT);
		$this->threadedconfig = $this->getdbconfig (FIELD_USERS_PROFILE_THREADED);
		$this->postsonpageconfig = $this->getdbconfig (FIELD_USERS_PROFILE_POSTSONPAGE);
		$this->headlinesconfig = $this->getdbconfig (FIELD_USERS_PROFILE_HEADLINES);
		$this->themeconfig = $this->getdbconfig (FIELD_USERS_PROFILE_THEME);
		$this->email = $this->getEmail ();
		$this->name = $this->getName ();
	}

	public function getConfig ($what) {
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
				return $this->email;
				break;
			case 'name':
				return $this->name;
				break;
			case 'langcode':
				return 'en';
				break;
			default:
				throw new exceptionlist ($this->lang->translate ('Uknown userconfig'),NULL,-1);
		}
	} /* public getConfig ($what) */

	private function isValidLogin () {
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
	} /* private function isValidLogin () */

	public function getOtherProfile ($username) {
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
			if (($user[FIELD_USERS_PUBLIC_CONTACT_INFO] == YES) or ($this->getconfig ('name') ==  $username)) {
				$userprofile[FIELD_USERS_PROFILE_AIM] = $profile[FIELD_USERS_PROFILE_AIM];
				$userprofile[FIELD_USERS_PROFILE_MSN] = $profile[FIELD_USERS_PROFILE_MSN];
				$userprofile[FIELD_USERS_PROFILE_YAHOO] = $profile[FIELD_USERS_PROFILE_YAHOO];
				$userprofile[FIELD_USERS_EMAIL] = $user[FIELD_USERS_EMAIL];
				$userprofile[FIELD_USERS_PROFILE_ICQ] = $profile[FIELD_USERS_PROFILE_ICQ];
				$userprofile[FIELD_USERS_PROFILE_ADRESS] = $profile[FIELD_USERS_PROFILE_ADRESS];
				$userprofile[FIELD_USERS_PROFILE_JABBER] = $profile[FIELD_USERS_PROFILE_JABBER];
			} else {
				$userprofile[FIELD_USERS_PROFILE_AIM] = '';
				$userprofile[FIELD_USERS_PROFILE_MSN] = '';
				$userprofile[FIELD_USERS_PROFILE_YAHOO] = '';
				$userprofile[FIELD_USERS_PROFILE_EMAIL] = '';
				$userprofile[FIELD_USERS_PROFILE_ICQ] = '';
				$userprofile[FIELD_USERS_PROFILE_ADRESS] = '';
				$userprofile[FIELD_USERS_PROFILE_JABBER] = '';
			}
			return $userprofile;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	}

	public function getIPs () {
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
	} /* public function getIPs () */

	public function isLoggedIn () {
		if (! isset ($this->isloggedin)) {
			if (!empty ($_SESSION[SESSION_NAME])) {
				try {
					$validlogin = $this->isValidLogin ();
					// this checks of the user has not hacked the session vars and
					// make him a root or other user
					return $validlogin;
				}
				catch (exceptionlist $e) {
					throw $e;
				}
			} else {
				return false;
			}
		} else {
			return $this->isloggedin;
		}
	} /* public function isLoggedIn () */

	public function login ($name,$password) {
		try {
			$exception = NULL;
			$sql = 'SELECT * FROM ' . TBL_USERS;
			$sql .= ' WHERE ' . FIELD_USERS_NAME . '=\'' .$name. '\'';
			$query = $this->database->query ($sql);
			if ($this->database->num_rows ($query) != 1) {
				$exception = new exceptionlist ($this->lang->translate ('User does not exists'),NULL,4);
			}
			// since we have only __one__ result it must not be in a loop
			$user = $this->database->fetch_array ($query);
			if ($user[FIELD_USERS_PASSWORD] != md5($password)) {
				$e = new exceptionlist ($this->lang->translate ('Your password is wrong'),NULL,5);
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
				throw new exceptionlist ($this->lang->translate ('Your username is blocked'));
			}
			if ($user[FIELD_USERS_ACTIVATE] == NO) {
				throw new exceptionlist ($this->lang->translate ('Your username is not (yet) activated,' .
				'check your mail to activate it'));
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
					throw new exceptionlist ($this->lang->translate ('Your IP is blocked'));
				}
			}
			$sql = 'UPDATE ' . TBL_USERS;
			$sql .= ' SET ' . FIELD_USERS_IP . '=\'' . implode(',',$userip) . '\'';
			$sql .= 'WHERE ' . FIELD_USERS_NAME . '=\'' . $name . '\'';
			$this->database->query ($sql,false);
			$_SESSION[SESSION_NAME] = $user[FIELD_USERS_NAME];
			$_SESSION[SESSION_TYPE] = $user[FIELD_USERS_TYPE];
			$_SESSION[SESSION_PASSWORD] = $user[FIELD_USERS_PASSWORD];
			// we need this, otherwise it would not check for new db
			$this->isloggedin = true;
			$this->updateConfig ();
			return true;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function login ($name,$password) */

	public function logout () {
		try {
			if (! $this->isLoggedIn ()) {
				throw new exceptionlist ($this->lang->translate ('Not logged in'),NULL,7);
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

	public function register ($username,$password,$controlpass,$email,$cmail,$webmastermail) {
			try {
				if ($password != $controlpass) {
					throw new exceptionlist ($this->lang->translate ('Passwords are not the same'));
				}
				// check username alreade exists
				$sql = 'SELECT ' . FIELD_USERS_NAME . ' FROM ' . TBL_USERS;
				$sql .= ' WHERE ' . FIELD_USERS_NAME . '=\''  . $username . '\'';
				$sql .= ' LIMIT 1';
				$query = $this->database->query ($sql);
				if ($this->database->num_rows ($query) != 0) {
					throw new exceptionlist ($this->lang->translate ('User already exists, use another usrename'));
				}
				// check email already exists
				$sql = "SELECT " . FIELD_USERS_EMAIL . " FROM " . TBL_USERS;
				$sql .= " WHERE " . FIELD_USERS_EMAIL . "='"  . $email . "'";
				$sql .= 'LIMIT 1';
				$query = $this->database->query ($sql);
				if ($this->database->num_rows ($query) != 0) {
					throw new exceptionlist ($this->lang->translate ('Email is already registerd, use another email'));
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
					FIELD_USERS_EMAIL,FIELD_USERS_TYPE,FIELD_USERS_ACTIVATE,FIELD_USERS_IP,FIELD_USERS_BLOCKED);
				$strfields = implode (',',$fields);
				$sql .= ' (' . $strfields . ')';
				$content = array ('\''.$name.'\'','\''.$password.'\'',
					'\''.$email.'\'','\''.$type.'\'','\''.$activated.'\'',
					'\''.$ip.'\'','\''. NO .'\'');
				$strcontent = implode (',',$content);
				$sql .= ' VALUES ( ' . $strcontent . ')';
				$query = $this->database->query ($sql);
				// create the config column
				$sql = 'INSERT INTO ' . TBL_USERS_PROFILE;
				$sql .= ' ('. FIELD_USERS_PROFILE_NAME .')';
				$sql .= ' VALUES (\'' .$name. '\')';
				$this->database->query ($sql);
				if ($this->mustactivate == true ) {
					// put it in the queue
					$sql = 'INSERT INTO ' . TBL_ACTIVATE_QUEUE;
					$fields = array (FIELD_ACTIVATE_QUEUE_USER,
						FIELD_ACTIVATE_QUEUE_ID,FIELD_ACTIVATE_QUEUE_START);
					$strfields = implode (',',$fields);
					$sql .= ' ( ' . $strfields . ' )';
					$id = $this->getRandomPassword (ACTIVATE_ID_LENGTH);
					$content = array ('\''.$name.'\'','\''.$id.'\'','\''.time ().'\'');
					$strcontent = implode (',',$content);
					$sql .= ' VALUES ( ' . $strcontent . ' )';
					$this->database->query ($sql);
					// FIXME link
					$cmail['message'] = ereg_replace ('%d',
						'http://yapcas.localhost/users.php?action=activate&id='.$id,
						$cmail['message']);
				} else {
					// $cmail;
				}
				// FIXME sitenmae
				$this->sitename = 'YaPCaS';
				$cmail['message'] = ereg_replace ('%s',$this->sitename,$cmail['message']);
				$cmail['message'] = ereg_replace ('%n',$name,$cmail['message']);
				$headers = 'From: ' .$webmastermail;//.'\r\n';
				$headers = 'Reply-to: ' .$webmastermail;
				mail ($email,$cmail['subject'],$cmail['message'],$headers);
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function register ($username,$password,$controlpass,$email,$cmail,$webmastermail) */

	// TODO implement on check current password
	public function setNewPassword ($username,$password1,$password2,$curpassword = NULL) {
		try {
			$exception = NULL;
			if ($password1 != $password2) {
				throw new exceptionlist ($this->lang->translate ('Password not changed, 2 paswwords are not equal'));
			}
			$sql = 'UPDATE ' . TBL_USERS . ' SET ' . FIELD_USERS_PASSWORD;
			$sql .= '=\'' . md5($password1) . '\'';
			$sql .= 'WHERE ' . FIELD_USERS_NAME . '=\'' . $username . '\'';
			$query = $this->database->query ($sql);
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function setNewPassword ($username,$password1,$password2,$curpassword = NULL) */

	private function getRandomPassword ($length = PASSWORD_LENGTH) {
		mt_srand ((double) microtime () * 1000000);
		$password = NULL;

		while (strlen ($password) < $length) {
			$i = chr (mt_rand(0,255)); 
			if (eregi("^[a-z0-9A-Z]$",$i)) { // only add it if it is a-z,A-Z or 0-9
				$password .= $i; 
			}
		}
		return $password;
	} /* private function getRandomPassword ($length = PASSWORD_LENGTH) */

	public function lostpasw ($mail,$username,$cmail,$webmastermail) {
		try {
			$password = $this->randompassword ();
			if (($mail == NULL) and ($username == NULL)) {
				throw new exceptionlist ($this->lang->translate ('Fill e-mail or username in'));
			} else if (($mail != NULL) and ($username == NULL)) {
				// mail is given
				$sql = 'SELECT ' . FIELD_USERS_NAME .' FROM ' . TBL_USERS;
				$sql .= ' WHERE ' . FIELD_USERS_EMAIL . '=\'' . $mail . '\'';
				$query = $this->database->query ($sql);
				if ($this->database->num_rows ($query) == 0) {
					throw new exceptionlist ($this->lang->translate ('EMail not found'));
				}
				$user = $this->database->fetch_array ($query);
				$this->setnewpassword ($user[FIELD_USERS_NAME],$password,$password);
				$headers = 'From: ' . $webmastermail . '\r\n';
				$headers .= 'Reply-to: ' . $webmastermail;
				// FIXME
				$this->sitename = 'YaPCaS';
				$cmail['message'] = ereg_replace ('%n',$user[FIELD_USERS_NAME],$cmail['message']);
				$cmail['message'] = ereg_replace ('%p',$password,$cmail['message']);
				$cmail['message'] = ereg_replace ('%n',$this->sitename,$cmail['message']);
				mail ($mail,$cmail['subject'],$cmail['message'],$headers);
			} else if (($username != NULL) and ($mail == NULL)) {
				$sql = 'SELECT ' . FIELD_USERS_EMAIL . ' FROM ' . TBL_USERS;
				$sql .= ' WHERE ' . FIELD_USERS_NAME . '=\'' . $username . '\''; 
				$query = $this->database->query ($sql);
				if ($this->database->num_rows ($query) == 0) {
					throw new exceptionlist ($this->lang->translate ('Username not found'));
				}
				$user = $this->database->fetch_array ($query);
				$this->setnewpassword ($username,$password,$password);
				$headers = 'From: ' . $webmastermail . '\r\n';
				$headers .= 'Reply-to: ' . $webmastermail;
				// FIXME
				$this->sitename = 'YaPCaS';
				$cmail['message'] = ereg_replace ('%n',$username,$cmail['message']);
				$cmail['message'] = ereg_replace ('%p',$password,$cmail['message']);
				$cmail['message'] = ereg_replace ('%n',$this->sitename,$cmail['message']);
				mail ($user[FIELD_USERS_EMAIL],$cmail['subject'],$cmail['message'],$headers);
			} else {
				// BOTH given
				throw new exceptionlist ($this->lang->translate ('Both are given, fill or email or username in'));
			}
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function lostpasw ($mail,$username,$cmail,$webmastermail) */
 
	private function getName () {
		if (!$this->isLoggedIn ()) {
			return NULL;
		} else {
			return $_SESSION[SESSION_NAME];
		}
	} /* private function getName () */

	// why is this a seperate function?
	private function getEmail () {
		return $this->getdbconfig (FIELD_USERS_EMAIL,TBL_USERS);
	} /* private function getEmail () */

	private function getDBConfig ($db_field,$table = TBL_USERS_PROFILE) {
		try {
			if ($this->isLoggedIn () == true) {
				$sql = 'SELECT ' . $db_field . ' FROM ' . $table . ' WHERE ' 
					. FIELD_USERS_NAME . '=\'' . $_SESSION[SESSION_NAME] . '\' LIMIT 1';
				$query = $this->database->query ($sql);
				$user = $this->database->fetch_array ($query);
				if ($user[$db_field] == '') {
					return NULL;
				}
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
	} /* private function getDBConfig ($db_field) */

	public function setConfig ($db_what,$value,$tbl = TBL_USERS_PROFILE,$mail = NULL) {
		try {
			if (($this->mustactivate == true) and ($db_what == FIELD_USERS_EMAIL)){
				if (($value) != $this->getEmail ()) {
					if (! empty ($mail)) {
						$this->deActivate ($_SESSION[SESSION_NAME],$value,$mail['cmail'],$mail['webmaster']);
					} else {
						throw new exceptionlist ($this->lang->translate ('Internal error'));
					}
				}
			}
			$sql = 'UPDATE ' . $tbl . ' SET ' . $db_what . '=\'' . $value . '\'';
			$sql .= ' WHERE ' .  FIELD_USERS_NAME . '=\'' . $_SESSION[SESSION_NAME] . '\'';
			$query = $this->database->query ($sql);
			return true;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function setConfig ($db_what,$value,$mail = NULL) */

	public function hasSetConfig () {
		try {
			if (($this->languageconfig == NULL) or ($this->themeconfig == NULL) 
			or ($this->timezoneconfig == NULL) or ($this->timeformatconfig == NULL)
			or ($this->threadedconfig == NULL) or ($this->postsonpageconfig == NULL) 
			or ($this->headlinesconfig == NULL)) {
				return false;
			} else {
				return true;
			}
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* puclic function hasSetConfig () */

	public function activate ($id) {
		try {
			$sql = 'SELECT * FROM ' . TBL_ACTIVATE_QUEUE;
			$sql .= ' WHERE ' . FIELD_ACTIVATE_QUEUE_ID . '=\'' . $id . '\'';
			$query = $this->database->query ($sql);
			if ($this->database->num_rows ($query) == 0) {
				throw new exceptionlist ($this->lang->translate ('ID not found'));
			}
			$activate = $this->database->fetch_array ($query);
			$sql = 'UPDATE ' . TBL_USERS;
			$sql .= ' SET ' . FIELD_USERS_ACTIVATE . '=\'' . YES . '\'' ;
			$sql .= ' WHERE ' . FIELD_USERS_NAME . '=\'' .
				$activate[FIELD_ACTIVATE_QUEUE_USER] . '\'';
			$this->database->query ($sql);
			$sql = 'DELETE FROM ' . TBL_ACTIVATE_QUEUE;
			$sql .= ' WHERE ' . FIELD_ACTIVATE_QUEUE_ID . '=\'' . $id . '\'';
			$this->database->query ($sql);
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function activate ($id) */

	private function deActivate ($username,$mail,$cmail,$webmastermail) {
		$this->setconfig (FIELD_USERS_ACTIVATE,NO);
		// put it in the queue
		$sql = 'INSERT INTO ' . TBL_ACTIVATE_QUEUE;
		$fields = array (FIELD_ACTIVATE_QUEUE_USER,
			FIELD_ACTIVATE_QUEUE_ID,FIELD_ACTIVATE_QUEUE_START);
		$strfields = implode (',',$fields);
		$sql .= ' ( ' . $strfields . ' )';
		$id = $this->randompassword (ACTIVATE_ID_LENGTH);
		$content = array ('\''.$username.'\'','\''.$id.'\'','\''.time ().'\'');
		$strcontent = implode (',',$content);
		$sql .= ' VALUES ( ' . $strcontent . ' )';
		$this->database->query ($sql);
		// FIXME link
		$cmail['message'] = ereg_replace ('%d',
			'http://yapcas.localhost/users.php?action=activate&id='.$id,
			$cmail['message']);
		// FIXME sitenmae
		$this->sitename = 'YaPCaS';
		$cmail['message'] = ereg_replace ('%s',$this->sitename,$cmail['message']);
		$cmail['message'] = ereg_replace ('%n',$username,$cmail['message']);
		$headers = 'From: ' .$webmastermail;
		$headers = 'Reply-to: ' .$webmastermail;
		mail ($mail,$cmail['subject'],$cmail['message'],$headers);
	} /* private function deActive ($username,$mail,$cmail,$webmastermail) */
} // clas user
?>
