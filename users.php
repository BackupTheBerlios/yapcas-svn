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
	$theme->redirect ($link);
}
switch ($action) {
	case 'login':
		try {
			$user->login ($_POST[POST_NAME],$_POST[POST_PASSWORD]);
			$database->close ();
			$theme->redirect ('index.php?message=loggedin');
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
		$error = $user->logout ();
		$database->close ();
		$link =error_handling ($error,'index.php?','index.php?',
			$lang->users->logged_out,$lang->users->not_logged_out);
		$theme->redirect ($redirect);
		break;
	case 'registerform':
		$theme->themefile ('registerform.html');
		$database->close ();
		break;
	case 'register':
		$errors = $user->register ($_POST);
		$database->close ();
		$link = error_handling ($errors,'index.php?','users.php?action=registerform');
		$theme->redirect ($link); 
		break;
	case 'sendpassword':
		$error = $user->lostpasw (); 
		$database->close ();
		$link =  error_handling ($error,'index.php?','users.php?action=sendpasswordform',
			$lang->users->password_is_send,$lang->users->password_is_not_send);
		$theme->redirect ($link);
		break;
	case 'sendpasswordform':
		$theme->themefile ('sendpasswordform.html');
		$database->close ();
		break;
	case 'changeoptionsform':
		$theme->themefile ('changeoptionsform.html',true);
		$database->close ();
		break;
	/*case 'changeoptions':
		$user->setconfig (FIELD_USERS_THEME,$_POST[POST_THEME] ;
		$user->setconfig (FIELD_USERS_THREADED,$user->convert_config2db (CONFIG_THREADED,$_POST[POST_THREADED]));
		$user->setconfig (FIELD_USERS_LANGUAGE,$_POST[POST_LANGUAGE]);
		$user->setconfig (FIELD_USERS_EMAIL,$_POST[POST_EMAIL]);
		$user->setconfig (FIELD_USERS_TIMEZONE,$_POST[POST_TIMEZONE]);
		$user->setconfig (FIELD_USERS_TIMEFORMAT,$_POST[POST_TIMEFORMAT]);
		$user->setconfig (FIELD_USERS_POSTSONPAGE,$_POST[POST_POSTSONPAGE]);
		$user->setconfig (FIELD_USERS_HEADLINES,$_POST[POST_HEADLINES]);
		if ((!empty ($_POST[POST_NEW_PASSWORD1])) AND (!empty ($_POST[POST_NEW_PASSWORD2]))) {
			$user->setnewpassword ($_POST[POST_NEW_PASSWORD1],$_POST[POST_NEW_PASSWORD2]);
			$user->logout ();
			$database->close ();
			$link =  'index.php?note=' . $lang->users->options_are_saved . ': ' . $lang->users->logged_out_new_password;
			$theme->redirect ();
		} else {
			$database->close ();
			$link =  'users.php?action=changeoptionsform&note=' . $lang->users->options_are_saved;
			$theme->redirect ($link);
		}
		break;*/
	case 'viewuserlist':
		$theme->themefile ('viewuserlist.html',true);
		$database->close ();
		break;
	case 'viewuser':
		$theme->themefile ('viewuserprofile.html',true);
		$database->close ();
		break;
	default:
		$theme->redirect ('index.php');
} // switch ($action)
?>
