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
if (!defined ('EXCEPTION_CLASS')) {
	include ('kernel/exception.class.php');
}
include ('kernel/functions.php');
loadall ();

if (!empty ($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$theme->redirect ('index.php');
}
try {
	$errorrep = $config->getConfigByNameType ('general/errorreporting',TYPE_INT);
}
catch (exceptionlist $e) {
	// this is a big errror so $errorep = true
	$link = catch_error ($e,'index.php?','you are not logged in',true);
	$database->close ();
	$theme->redirect ($link);
}
switch ($action) {
	case 'login':
		try {
			$user->login ($_POST[POST_NAME],$_POST[POST_PASSWORD]);
			$database->close ();
			if ($user->hasSetConfig () == true) {
				// this is not the first time the user logged in
				$theme->redirect ('index.php?note=you are logged in');
			} else {
				$theme->redirect ('users.php?action=changeoptionsform' . 
					'&note=this is the first time you log in, configure your account');
			}
		}
		catch (exceptionlist $e) {
			$database->close ();
			// FIXME
			// save some loginthings like username in a cookie and retrieve them later??
			$link = catch_error ($e,'index.php?','you are not logged in',$errorrep);
			$theme->redirect ($link);
		}
		break;
	case 'logout':
		try {
			$user->logout ();
			$database->close ();
			$theme->redirect ('index.php?note=you are logged out');
		}
		catch (exceptionlist $e) {
			$database->close ();
			$link = catch_error ($e,'index.php?','you are not logged out',$errorrep);
			$theme->redirect ($link);
		}
		break;
	case 'registerform':
		try {
			$theme->themefile ('registerform.html');
			$database->close ();
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'index.php?','you can\'t open this page',$errorrep);
			$theme->redirect ($link);
		}
		break;
	case 'register':
		try {
			$activatemail = $config->getConfigByNameType ('user/activatemail',
				TYPE_BOOL);
			if ($activatemail = true) {
				$mail['subject'] = 'You must activate your email';
				$mail['message'] = 'Hello %n \n. Thanks for registering on %s \n. Click on the following link to activate your mail %d \n.';
			} else {
				$mail['subject'] = 'Thanks for registering';
				$mail['message'] = 'Hello %n \n.Thanks for registering on %s \n. We hope to see you soon on our website';
			}
			$webmastermail = $config->getConfigByNameType ('general/webmastermail',TYPE_STRING);
			$user->register ($_POST[POST_NAME],$_POST[POST_PASSWORD1],
				$_POST[POST_PASSWORD2],$_POST[POST_EMAIL],$mail,$webmastermail);
			$database->close ();
			if ($activatemail == true) {
				$link = 'index.php?note=You are now registerd, check your mail';
			} else {
				$link = 'index.php?note=You are now registerd';
			}
			$theme->redirect ($link); 
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'users.php?action=registerform&',
				'You are not registerd',$errorrep);
			$theme->redirect ($link); 
		}
		break;
	case 'sendpassword':
		try {
			if (empty ($_POST['email'])) {
				$mail = NULL;
			} else {
				$mail = $_POST['email'];
			}
			if (empty ($_POST['name'])) {
				$username = NULL;
			} else {
				
				$username = $_POST['name'];
			}
			$cmail['subject'] = 'New password';
			$cmail['message'] = 'Hello %n \n.Your password is changed on %s \n.New password is: %p\n. Username is: %n';
			$webmastermail = $config->getConfigByNameType ('general/webmastermail',TYPE_STRING);
			$user->lostpasw ($mail,$username,$cmail,$webmastermail); 
			$database->close ();
			$link = 'index.php?note=Your password is send to your emailadress';
			$theme->redirect ($link);
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'index.php?',
				'Your password isn\'t send',$errorrep);
			$theme->redirect ($link); 
		}
		break;
	case 'sendpasswordform':
		$theme->themefile ('sendpasswordform.html');
		$database->close ();
		break;
	case 'changeoptionsform':
		$theme->themefile ('changeoptionsform.html',true);
		$database->close ();
		break;
	case 'changeoptions':
		try {
			$user->setconfig (FIELD_USERS_PROFILE_THEME,$_POST[POST_THEME]);
			$user->setconfig (FIELD_USERS_PROFILE_THREADED,$_POST[POST_THREADED]);
			$user->setconfig (FIELD_USERS_PROFILE_LANGUAGE,$_POST[POST_LANGUAGE]);
			$mail['webmaster'] = $config->getConfigByNameType ('general/webmastermail',TYPE_STRING);
			$mail['cmail']['subject'] = 'You must activate your mail';
			$mail['cmail']['message'] = 'Hello %n \n. You have changed your ' .
				'email-adress %s \n. Click on the following link to activate' .
				'your mail %d \n.';
			$user->setconfig (FIELD_USERS_PROFILE_EMAIL,$_POST[POST_EMAIL],TBL_USERS,$mail);
			$user->setconfig (FIELD_USERS_PROFILE_TIMEZONE,$_POST[POST_TIMEZONE]);
			$user->setconfig (FIELD_USERS_PROFILE_TIMEFORMAT,$_POST[POST_TIMEFORMAT]);
			$user->setconfig (FIELD_USERS_PROFILE_POSTSONPAGE,$_POST[POST_POSTSONPAGE]);
			$user->setconfig (FIELD_USERS_PROFILE_HEADLINES,$_POST[POST_HEADLINES]);
			if ((!empty ($_POST[POST_NEW_PASSWORD1])) AND (!empty ($_POST[POST_NEW_PASSWORD2]))) {
				$user->setnewpassword ($user->getconfig ('name'),$_POST[POST_NEW_PASSWORD1],$_POST[POST_NEW_PASSWORD2]);
				// Do not log out -> password is wrong so he thinks you are'nt logged in
				// FIXME
				// $user->logout ();
				$database->close ();
				$link = 'index.php?note=Your options are saved: Login again with your new password';
				$theme->redirect ($link);
			} else {
				$database->close ();
				$link = 'users.php?action=changeoptionsform&note=Your options are saved';
				$theme->redirect ($link);
			}
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'users.php?action=changeoptionsform&',
				'Options are not saved or saved partionelly',$errorrep);
			$theme->redirect ($link);
		}
		break;
	case 'viewuserlist':
		$theme->themefile ('viewuserlist.html',true);
		$database->close ();
		break;
	case 'viewuser':
		$theme->themefile ('viewuserprofile.html',true);
		$database->close ();
		break;
	case 'activate':
		try {
			if (empty ($_GET['id'])) {
				throw new exceptionlist ('ID must be set');
			}
			$user->activate ($_GET['id']);
			$link = 'index.php?note=Your account is activated, you can login now';
			$theme->redirect ($link);
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'index.php&',
				'Your account is not activated',$errorrep);
			$theme->redirect ($link);
		}
	default:
		$theme->redirect ('index.php');
} // switch ($action)
?>
