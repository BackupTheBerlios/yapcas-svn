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
include ('kernel/skin.class.php');
$skin = new CSkin ();

if (! empty ($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$skin->redirect ('index.php');
}

function clean ($text) {
	preg_match_all ('/\[(.*)\](.*)\[\/(.*)\]/si',$text,$matches);
	foreach ($matches[0] as $key => $match) {
		if ($matches[1][$key] == $matches[3][$key]) {
			$text = str_replace ($match,$matches[2][$key],$text);
		}
	}
	return $text;
}

try {
	$errorrep = $config->getConfigByNameType ('general/errorreporting',TYPE_INT);
}
catch (exceptionlist $e) {
	// this is a big errror so $errorep = true
	$link = $skin->catchError ($e,'index.php?','error in subsystem',true);
	$database->close ();
	$theme->redirect ($link);
}
switch ($action) {
	case 'viewcomments': 
		try {
			$skin->loadSkinFile ('comments.html');
			$database->close ();
		}
		catch (exceptionlist $e) {
			$link = $skin->catchError ($e,'index.php?',$lang->translate ('Your action has no effect'),$errorrep);
			$database->close ();
			$skin>redirect ($link);
		}
		break;
	case 'postcommentform':
		try {
			$skin->loadSkinFile ('postcommentform.html',true);
			$database->close ();
		}
		catch (exceptionlist $e) {
			$link = $skin->catchError ($e,'index.php?',$lang->translate ('You can\'t open this page'),$errorrep);
			$database->close ();
			$skin->redirect ($link);
		}
		break;
	case 'postcomment':
		try {
			$exception = NULL;
			if (empty ($_POST[POST_SUBJECT])) {
				$e = new exceptionlist ($lang->translate ('You must fill in the subject field'));
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (empty ($_POST[POST_MESSAGE])) {
				$e = new exceptionlist ($lang->translate ('You must fill in the message field'));
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (empty ($_POST[POST_ID_NEWS])) {
				$e = new exceptionlist ($lang->translate ('Core error'));
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
			$contentLang = $config->getConfigByNameType ('general/contentlanguage',TYPE_STRING);
			$news->postComment ($_POST[POST_MESSAGE],$_POST[POST_SUBJECT],$contentLang,$date,
				$user->getconfig ('name'),$_POST[POST_ID_NEWS],$idcomment);
			$id = $_POST[POST_ID_NEWS];
			$database->close ();
			$skin->redirect ('news.php?action=viewcomments&id='.$id.
				'&note=' . $lang->translate ('Your comment is posted'));
		}
		catch (exceptionlist $e) {
			$link = $skin->catchError ($e,'index.php?',$lang->translate ('Your comment is not posted'),$errorrep);
			$database->close ();
			$skin->redirect ($link);
		}
		break;
	case 'postnews':
		try {
			$exception = NULL;
			if (empty ($_POST[POST_SUBJECT])) {
				$e = new exceptionlist ($lang->translate ('You must fill in the subject field'));
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (empty ($_POST[POST_MESSAGE])) {
				$e = new exceptionlist ($lang->translate ('You must fill in the message field'));
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (empty ($_POST[POST_CATEGORY])) {
				$e = new exceptionlist ($lang->translate ('You must fill in the category field'));
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (! empty ($exception)) {
				throw $exception;
			}
			$language = $config->getConfigByNameType ('general/contentlanguage',TYPE_STRING);
			$error = $news->postnews ($_POST[POST_MESSAGE],$_POST[POST_SUBJECT],
				$_POST[POST_CATEGORY],getUTCTime (),$language,$user->getconfig ('name'));
			$skin->redirect ('index.php?note=' . $lang->translate ('Your message is posted'));
			$database->close ();
		}
		catch (exceptionlist $e) {
			$link = $skin->catchError ($e,'index.php?',$lang->translate ('Your message is not posted'),$errorrep);
			$database->close ();
			$link = $skin->redirect ($link);
		}
		break;
	case 'postnewsform':
		try {
			$skin->loadSkinFile ('postnewsform.html',true);
			$database->close ();
		}
		catch (exceptionlist $e) {
			$link = $skin->catchError ($e,'index.php?',$lang->translate ('You can\'t open this page'),$errorrep);
			$database->close ();
			$skin->redirect ($link);
		}
		break;
	case 'editcomment':
		try {
			$exception = NULL;
			if (empty ($_POST[POST_MESSAGE])) {
				$e = new exceptionlist ($lang->translate ('You must fill in the message field'));
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			$date = getUTCtime ($config);
			if (empty ($_POST[POST_SUBJECT])) {
				$e = new exceptionlist ($lang->translate ('You must fill in the subject field'));
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (empty ($_GET[GET_ID])) {
				$e = new exceptionlist ($lang->translate ('Core error'));
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (! empty ($exception)) {
				throw $exception;
			}
			$news->editcomment ($_POST[POST_MESSAGE],$_POST[POST_SUBJECT],$_GET[GET_ID]);
			$database->close ();
			$skin->redirect ('index.php?note=' . $lang->translate ('Your message is edited'));
		}
		catch (exceptionlist $e) {
			$link = $skin->catchError ($e,'index.php?',$lang->translate ('Your message is not edited'),$errorrep);
			$database->close ();
			$skin->redirect ($link);
		}
		break;
	case 'editcommentform':
		try {
			$skin->loadSkinFile ('editcomment.html',true);
			$database->close ();
		}
		catch (exceptionlist $e) {
			$link = $skin->catchError ($e,'index.php?',$lang->translate ('You can\'t open this page'),$errorrep);
			$database->close ();
			$skin->redirect ($link);
		}
		break;
	case 'editnews':
		try {
			$exception = NULL;
			if (empty ($_POST[POST_MESSAGE])) {
				$e = new exceptionlist ($lang->translate ('You must fill in the message field'));
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			$date = getUTCtime ($config);
			if (empty ($_POST[POST_SUBJECT])) {
				$e = new exceptionlist ($lang->translate ('You must fill in the subject field'));
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (empty ($_GET[GET_ID])) {
				$e = new exceptionlist ($lang->translate ('Core error'));
				if ($exception == NULL) {
					$exception = $e;
				} else {
					$exception->setNext ($e);
				}
			}
			if (! empty ($exception)) {
				throw $exception;
			}
			$news->editNews ($_POST[POST_MESSAGE],$_POST[POST_SUBJECT],$_GET[GET_ID]);
			$database->close ();
			$skin->redirect ('index.php?note=' . $lang->translate ('Your newsitem is edited'));
		}
		catch (exceptionlist $e) {
			$link = $skin->catchError ($e,'index.php?',$lang->translate ('Your newsitel is not edited'),$errorrep);
			$database->close ();
			$skin->redirect ($link);
		}
		break;
	case 'editnewsform':
		try {
			$skin->loadSkinFile ('editnews.html',true);
			$database->close ();
		}
		catch (exceptionlist $e) {
			$link = $skin->catchError ($e,'index.php?',$lang->translate ('You can\'t open this page'),$errorrep);
			$database->close ();
			$skin->redirect ($link);
		}
		break;
	case 'viewfeed':
		try {
			$meta['title'] = $config->getConfigByNameType ('general/sitename',TYPE_STRING);
			$meta['description'] = $config->getConfigByNameType ('general/description',TYPE_STRING);
			$meta['link'] = 'http://'.$_SERVER["HTTP_HOST"];
			$meta['maxheadlines'] = $config->getConfigByNameType ('news/postsonpage',TYPE_NUMERIC);
			if (! empty ($_GET[GET_CATEGORY])) {
				$category = $_GET[GET_CATEGORY];
			} else {
				$category = NULL;
			}
			echo $news->showFeed ($meta,'clean',$category,'RSS2');
		}
		catch (exceptionlist $e) {
			$link = $skin->catchError ($e,'index.php?',$lang->translate ('error running feed'),$errorrep);
			$database->close ();
			$skin->redirect ($link);
		}
		break;
	case 'moresmilies':
		try {
			$skin->loadSkinFile ('moresmilies.html',false);
			$database->close ();
		}
		catch (exceptionlist $e) {
			$link = $skin->catchError ($e,'index.php?',$lang->translate ('You can\'t open this page'),$errorrep);
			$database->close ();
			$skin->redirect ($link);
		}
		break;
	default:
		$database->close ();
		$skin->redirect ('index.php');
}
?>
