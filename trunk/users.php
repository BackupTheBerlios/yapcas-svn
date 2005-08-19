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
init ();

if (!empty ($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$skin->redirect ('index.php');
}
try {
	$errorrep = $config->getConfigByNameType ('general/errorreporting',TYPE_INT);
}
catch (exceptionlist $e) {
	// this is a big errror so $errorep = true
	$link = catch_error ($e,'index.php?',$lang->translate ('You can\'t view this page'),true);
	$database->close ();
	$skin->redirect ($link);
}
switch ($action) {
	case 'login':
		try {
			$exception = NULL;
			if (empty ($_POST[POST_NAME])) {
				$e = new exceptionlist ($lang->translate ('You must fill in the username field'));
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (empty ($_POST[POST_PASSWORD])) {
				$e = new exceptionlist ($lang->translate ('You must fill in the password field'));
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (! empty ($exception)) {
				throw $exception;
			}
			$user->login ($_POST[POST_NAME],$_POST[POST_PASSWORD]);
			$database->close ();
			if ($user->hasSetConfig () == true) {
				// this is not the first time the user logged in
				$skin->redirect ('index.php?note=' . $lang->translate ('You are logged in'));
			} else {
				$skin->redirect ('users.php?action=changeoptionsform' . 
					'&note=' . $lang->translate ('This is the first time you log in, configure your account'));
			}
		}
		catch (exceptionlist $e) {
			$database->close ();
			// FIXME
			// save some loginthings like username in a cookie and retrieve them later??
			$link = catch_error ($e,'index.php?',$lang->translate ('You are not logged in'),$errorrep);
			$skin->redirect ($link);
		}
		break;
	case 'logout':
		try {
			$user->logout ();
			$database->close ();
			$skin->redirect ('index.php?note=' . $lang->translate ('You are logged out'));
		}
		catch (exceptionlist $e) {
			$database->close ();
			$link = catch_error ($e,'index.php?',$lang->translate ('You are not logged out'),$errorrep);
			$skin->redirect ($link);
		}
		break;
	case 'registerform':
		try {
			$skin->loadSkinFile ('registerform.html');
			$database->close ();
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'index.php?',$lang->translate ('You can\'t open this page'),$errorrep);
			$skin->redirect ($link);
		}
		break;
	case 'register':
		try {
			$activatemail = $config->getConfigByNameType ('user/activatemail',
				TYPE_BOOL);
			if ($activatemail == true) {
				$mail['subject'] = $lang->translate ('You must activate your email');
				$mail['message'] = $lang->translate ('Hello %n \n. Thanks for registering on %s \n. Click on the following link to activate your mail %d \n.');
			} else {
				$mail['subject'] = $lang->translate ('Thanks for registering');
				$mail['message'] = $lang->translate ('Hello %n \n.Thanks for registering on %s \n. We hope to see you soon on our website');
			}
			$webmastermail = $config->getConfigByNameType ('general/webmastermail',TYPE_STRING);
			$user->register ($_POST[POST_NAME],$_POST[POST_PASSWORD1],
				$_POST[POST_PASSWORD2],$_POST[POST_EMAIL],$mail,$webmastermail);
			$database->close ();
			if ($activatemail == true) {
				$link = 'index.php?note=' . $lang->translate ('You are now registerd, check your mail to activate your account');
			} else {
				$link = 'index.php?note=' . $lang->translate ('You are now registerd, you can login now');
			}
			$skin->redirect ($link); 
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'users.php?action=registerform&',
				$lang->translate ('You are not registerd'),$errorrep);
			$skin->redirect ($link); 
		}
		break;
	case 'sendpassword':
		try {
			if (empty ($_POST[POST_EMAIL])) {
				$mail = NULL;
			} else {
				$mail = $_POST[POST_EMAIL];
			}
			if (empty ($_POST[POST_NAME])) {
				$username = NULL;
			} else {
				
				$username = $_POST[POST_NAME];
			}
			$cmail['subject'] = $lang->translate ('New password');
			$cmail['message'] = $lang->translate ('Hello %n \n.Your password is changed on %s \n.New password is: %p\n. Username is: %n');
			$webmastermail = $config->getConfigByNameType ('general/webmastermail',TYPE_STRING);
			$user->lostpasw ($mail,$username,$cmail,$webmastermail); 
			$database->close ();
			$link = 'index.php?note=' . $lang->translate ('Your password is send to your emailadress');
			$skin->redirect ($link);
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'index.php?',
				$lang->translate ('Your password isn\'t send'),$errorrep);
			$skin->redirect ($link); 
		}
		break;
	case 'sendpasswordform':
		$skin->loadSkinFile ('sendpasswordform.html');
		$database->close ();
		break;
	case 'changeoptionsform':
		$skin->loadSkinFile ('changeoptionsform.html',true);
		$database->close ();
		break;
	case 'changeoptions':
		try {
			$mailchanged = ($_POST[POST_EMAIL] != $user->getConfig ('email')) ? true : false;
			$threaded = (! empty ($_POST[POST_THREADED])) ? YES : NO;
			$user->setconfig (FIELD_USERS_PROFILE_THEME,$_POST[POST_THEME]);
			$user->setconfig (FIELD_USERS_PROFILE_THREADED,$threaded);
			$UILang = lang2code ($_POST[POST_UILANGUAGE]);
			$user->setconfig (FIELD_USERS_PROFILE_UILANGUAGE,$UILang);
			$contentLang = lang2code ($_POST[POST_CONTENTLANGUAGE]);
			$user->setconfig (FIELD_USERS_PROFILE_CONTENTLANGUAGE,$contentLang);
			$mail['webmaster'] = $config->getConfigByNameType ('general/webmastermail',TYPE_STRING);
			$mail['cmail']['subject'] = $lang->translate ('You must activate your mail');
			$mail['cmail']['message'] = $lang->translate ('Hello %n \n. You have changed your
				email-adress %s \n. Click on the following link to activate
				your mail %d \n.');
			$user->setconfig (FIELD_USERS_PROFILE_EMAIL,$_POST[POST_EMAIL],TBL_USERS,$mail);
			$user->setconfig (FIELD_USERS_PROFILE_TIMEZONE,$_POST[POST_TIMEZONE]);
			$user->setconfig (FIELD_USERS_PROFILE_TIMEFORMAT,$_POST[POST_TIMEFORMAT]);
			$user->setconfig (FIELD_USERS_PROFILE_POSTSONPAGE,$_POST[POST_POSTSONPAGE]);
			$user->setconfig (FIELD_USERS_PROFILE_HEADLINES,$_POST[POST_HEADLINES]);
			$user->setconfig (FIELD_USERS_PROFILE_AIM,$_POST[POST_NEW_AIM]);
			$user->setconfig (FIELD_USERS_PROFILE_MSN,$_POST[POST_NEW_MSN]);
			$user->setconfig (FIELD_USERS_PROFILE_ICQ,$_POST[POST_NEW_ICQ]);
			$user->setconfig (FIELD_USERS_PROFILE_JABBER,$_POST[POST_NEW_JABBER]);
			$user->setconfig (FIELD_USERS_PROFILE_YAHOO,$_POST[POST_NEW_YAHOO]);
			$user->setconfig (FIELD_USERS_PROFILE_JOB,$_POST[POST_NEW_JOB]);
			$user->setconfig (FIELD_USERS_PROFILE_ADRESS,$_POST[POST_NEW_ADRESS]);
			$user->setconfig (FIELD_USERS_PROFILE_INTRESTS,$_POST[POST_NEW_INTRESTS]);
			$user->setconfig (FIELD_USERS_PROFILE_WEBSITE,$_POST[POST_NEW_WEBSITE]);
			if ((!empty ($_POST[POST_NEW_PASSWORD1])) AND (!empty ($_POST[POST_NEW_PASSWORD2]))) {
				$user->setnewpassword ($user->getconfig ('name'),$_POST[POST_NEW_PASSWORD1],$_POST[POST_NEW_PASSWORD2]);
				// Do not log out -> password is wrong so he thinks you are'nt logged in
				// FIXME
				// $user->logout ();
				$database->close ();
				$link = 'index.php?note=' . $lang->translate ('Your options are saved: Login again with your new password');
				$skin->redirect ($link);
			} else {
				if ($mailchanged) {
					$user->logout ();
					$link = 'index.php?note=' . $lang->translate ('You need to reactivate your account, check your mail');
					$skin->redirect ($link);
				} else {
					$database->close ();
					$link = 'users.php?action=changeoptionsform&note=' . $lang->translate ('Your options are saved');
					$skin->redirect ($link);
				}
			}
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'users.php?action=changeoptionsform&',
				$lang->translate ('Options are not saved or saved partionelly'),$errorrep);
			$skin->redirect ($link);
		}
		break;
	case 'viewuserlist':
		$skin->loadSkinFile ('viewuserlist.html',true);
		$database->close ();
		break;
	case 'viewuser':
		$skin->loadSkinFile ('viewuserprofile.html',true);
		$database->close ();
		break;
	case 'activate':
		try {
			if (empty ($_GET['id'])) {
				throw new exceptionlist ($lang->translate ('ID must be set'));
			}
			$user->activate ($_GET['id']);
			$link = 'index.php?note=' . $lang->translate ('Your account is activated, you can login now');
			$skin->redirect ($link);
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'index.php&',
				$lang->translate ('Your account is not activated'),$errorrep);
			$skin->redirect ($link);
		}
	default:
		$theme->redirect ('index.php');
} // switch ($action)
?>
