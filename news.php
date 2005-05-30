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
include ('kernel/functions.php');
loadall ();

if (! empty ($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$theme->redirect ('index.php');
}
try {
	$errorrep = $config->getConfigByNameType ('general/errorreporting',TYPE_INT);
}
catch (exceptionlist $e) {
	// this is a big errror so $errorep = true
	$link = catch_error ($e,'index.php?','error in subsystem',true);
	$database->close ();
	$theme->redirect ($link);
}
switch ($action) {
	case 'viewcomments': 
		try {
			$theme->themefile ('comments.html');
			$database->close ();
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'index.php?','you can\'t open this page',$errorrep);
			$database->close ();
			$theme->redirect ($link);
		}
		break;
	case 'postcommentform':
		try {
			$theme->themefile ('postcommentform.html',true);
			$database->close ();
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'index.php?','you can\'t open this page',$errorrep);
			$database->close ();
			$theme->redirect ($link);
		}
		break;
	case 'postcomment':
		try {
			$exception = NULL;
			if (empty ($_POST[POST_SUBJECT])) {
				$e = new exceptionlist ('You must fill in the subject field');
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (empty ($_POST[POST_MESSAGE])) {
				$e = new exceptionlist ('You must fill in the message field');
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (! empty ($exception)) {
				throw $exception;
			}
			$date = getUTCtime ($config);
			if (! empty ($_POST[POST_ID_COMMENT])) {
				$idcomment = $_POST[POST_ID_COMMENT];
			} else {
				$idcomment = 0;
			}
			$news->postcomment ($_POST[POST_MESSAGE],$_POST[POST_SUBJECT],$date,
				$user->getconfig ('name'),$_POST[POST_ID_NEWS],$idcomment);
			$id = $_POST['on_news'];
			$database->close ();
			$theme->redirect ('news.php?action=viewcomments&id='.$id.
				'&note=Your comment is posted');
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'index.php?','Your comment is not posted',$errorrep);
			$database->close ();
			$theme->redirect ($link);
		}
		break;
	case 'postnews':
		try {
			$exception = NULL;
			if (empty ($_POST[POST_SUBJECT])) {
				$e = new exceptionlist ('You must fill in the subject field');
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			$date = getUTCtime ($config);
			if (empty ($_POST[POST_MESSAGE])) {
				$e = new exceptionlist ('You must fill in the message field');
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (empty ($_POST[POST_CATEGORY])) {
				$e = new exceptionlist ('You must fill in the category field');
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (! empty ($exceptionlist)) {
				throw $exception;
			}
			$language = $config->getConfigByNameType ('general/language',TYPE_STRING);
			$error = $news->postnews ($_POST[POST_MESSAGE],$_POST[POST_SUBJECT],
				$_POST[POST_CATEGORY],$date,$language,$user->getconfig ('name'));
			$theme->redirect ('index.php?note=Your message is posted');
			$database->close ();
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'index.php?','Your message is not posted',$errorrep);
			$database->close ();
			$link = $theme->redirect ($link);
		}
		break;
	case 'postnewsform':
		try {
			$theme->themefile ('postnewsform.html',true);
			$database->close ();
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'index.php?','you can\'t open this page',$errorrep);
			$database->close ();
			$theme->redirect ($link);
		}
		break;
	case 'editcomment':
		try {
			$exception = NULL;
			if (empty ($_POST[POST_MESSAGE])) {
				$e = new exceptionlist ('You must fill in the message field');
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			$date = getUTCtime ($config);
			if (empty ($_POST[POST_SUBJECT])) {
				$e = new exceptionlist ('You must fill in the subject field');
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (! empty ($exceptionlist)) {
				throw $exception;
			}
			$news->editcomment ($_POST[POST_MESSAGE],$_POST[POST_SUBJECT],$_GET[GET_ID]);
			$database->close ();
			$theme->redirect ('index.php?note=Your message is edited');
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'index.php?','Your message is not edited',$errorrep);
			$database->close ();
			$theme->redirect ($link);
		}
		break;
	case 'editcommentform':
		try {
			$theme->themefile ('editcomment.html',true);
			$database->close ();
		}
		catch (exceptionlist $e) {
			$link = catch_error ($e,'index.php?','you can\'t open this page',$errorrep);
			$database->close ();
			$theme->redirect ($link);
		}
		break;
	default: 
		$database->close ();
		$theme->redirect ('index.php');
}
?>
