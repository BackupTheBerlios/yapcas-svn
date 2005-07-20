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
// The completely class needs to be rewritten
// Step-By-Step
// 1) Rewrite constructor :: DONE
// 2) better errorcatching
// 3) better translation
// 4) better desing possibilities
// 5) extensions

class theme {
	function __construct () {
		error_reporting (E_ALL);
		// as it crashes between this and load of the config
		// we need to have all debug info
		// FIXME
		if (! file_exists ('.install.php')) {
			include ('kernel/config.class.php');
			$config = new config ();
			$lang = new lang ();
			$config->addConfigByFileName ('site.config.php',TYPE_STRING,'database/tblprefix',0);
			define ('TBL_PREFIX',$config->getConfigByNameType ('database/tblprefix',TYPE_STRING));
			define ('TBL_PAGES',TBL_PREFIX . 'pages');
			include ('kernel/help.class.php');
			include ('kernel/error.class.php'); // This should not be in the release
			include ('kernel/users.class.php');
			include ('kernel/news.class.php');
			include ('kernel/polls.class.php');
			$config->addConfigByFileName ('site.config.php',TYPE_INT,'general/errorreporting',0);
			$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/webmastermail',0);
			error_reporting ($config->getConfigByNameType('general/errorreporting',TYPE_INT));
			$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/databasetype',0);
			loaddbclass ($config->getConfigByNameType ('general/databasetype',TYPE_STRING));
			$database = new database ($config,'site.config.php');
			$database->connect ();
			// TODO
			$tables = array ();
			if (checkDatabase ($database,$tables)) {
				// Database seems to be OK
				$config->addConfigByFileName ('site.config.php',TYPE_BOOL,'user/activatemail',0);
				$user = new user ($database,$config->getConfigByNameType ('user/activatemail',TYPE_BOOL),$lang);
				$news = new news ($database,$user,$config,$lang);
				$poll = new polls ($database,$config,$lang);
				$this->help = new help ($database,$config,$lang);
				$config->addConfigByFileName ('site.config.php',TYPE_FLOAT,'general/servertimezone');
				$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/httplink');
				$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/sitename');
				$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/description');
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('timezone',$user,'timezone','site.config.php'),
					'general/timezone',TYPE_FLOAT,1);
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('timeformat',$user,'timeformat','site.config.php'),
					'general/timeformat',TYPE_STRING,'');
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('language',$user,'language','site.config.php'),
					'general/language',TYPE_STRING,STANDARD_LANGUAGE);
				$lang->updatelang ($config->getConfigByNameType('general/language',TYPE_STRING));
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('theme',$user,'theme','site.config.php'),
					'general/theme',TYPE_STRING,'moderngray');
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('threaded',$user,'threaded','site.config.php'),
					'news/threaded',TYPE_BOOL,YES);
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('langcode',$user,'langcode','site.config.php'),
					'general/langcode',TYPE_STRING);
				$config->addConfigByList ('YAPCAS_USER',array($user),'user/email',TYPE_STRING);
				$config->addConfigByList ('YAPCAS_USER',array($user),'user/name',TYPE_STRING,'anonymous');
				$this->loadtheme ($config->getConfigByNameType('general/theme',TYPE_STRING));
				$this->config = $config;
				$this->news = $news;
				$this->config = $config;
				$this->poll = $poll;
				$this->lang = $lang;
				$this->user = $user;
				$this->database = $database;
				global $theme,$config,$news,$poll,$user,$lang,$database;
				$config = $this->config;
				$news = $this->news;
				$config = $this->config;
				$poll = $this->poll;
				$lang = $this->lang;
				$user = $this->user;
				$database = $this->database;
				$theme = $this;
			}
		} else {
			if (file_exists ('site.config.php')) {
				include ('kernel/config.class.php');
				$config = new config ();
				$config->addConfigByFileName ('site.config.php',TYPE_STRING,'database/tblprefix',0);
				define ('TBL_PREFIX',$config->getConfigByNameType ('database/tblprefix',TYPE_STRING));
				define ('TBL_PAGES',TBL_PREFIX . 'pages');
				$config->addConfigByFileName ('site.config.php',TYPE_INT,'general/errorreporting',0);
				//error_reporting ($config->getConfigByNameType('general/errorreporting',TYPE_INT));
				$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/databasetype',0);
				loaddbclass ($config->getConfigByNameType ('general/databasetype',TYPE_STRING));
				$database = new database ($config,'site.config.php');
				$database->connect ();
				// FIXME
				$tables = array ();
				if (checkDatabase ($database,$tables)) {
					// site is configured but the installscript exists???
					echo 'DELETE install.php and than reload this page';
					exit;
				} else {
					header ('Location: install.php');
				}
			} else {
				header ('Location: install.php');
			}
		}
	} /* function __construct () */

	function db_error ( $error,$fatal = false ) {
		if ( $fatal == true ) {
			echo '<div><h2>Database fout</h2><p>' . $error . '</p></div>';
		} else {
		}
	}

	function error ( $error ) {
		return $error->error;
	}

	function loadtheme ($themedir) {
		$this->themedir = $themedir;
		if (file_exists ('themes/' . $this->themedir . '/theme.php')) {
			include_once ('themes/' . $this->themedir . '/theme.php');
			if ($this->version_cms != version) {
				//$this->config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/theme',0);
				$this->themedir = 'moderngray';//$this->config->getConfigByNameType ('general/theme',TYPE_STRING);
				include_once ('themes/' . $this->themedir . '/theme.php');
			}
		} else {
			$this->themedir = 'moderngray';//$this->config->getConfigByNameType ('general/theme',TYPE_STRING);
			include_once ('themes/' . $this->themedir . '/theme.php');
		}
	}
	
	function title () {
		$sitename = $this->config->getConfigByNameType ('general/sitename',TYPE_STRING);
		$pagename = $_SERVER['PHP_SELF'];
		$pagename = preg_replace ('#(.+?)/(.+?)#','\\2',$pagename);
		// removes everything before and '/'
		$language = $this->config->getConfigByNameType ('general/language',TYPE_STRING);
		$sql = "SELECT * FROM " . TBL_PAGES ." WHERE name='$pagename' AND language='$language' LIMIT 1";
		$query = $this->database->query ( $sql );
		if ( errorSDK::is_error ( $query ) ) {
			$this->error ( $query );
		} else {
			if ( $this->database->num_rows ( $query ) == 0 ) {
				$title = $this->lang->translate ('Untitled');
			} else {
				$pagetitle = $this->database->fetch_array ( $query );
				$pagetitle = $pagetitle['shown_name'];
				$title = ereg_replace ( ' S ', $sitename, $this->titleformat );
				$title = ereg_replace ( ' P ', $pagetitle, $title );
			}
		}
		return $title;
	}
	
	function getfile ( $file ) {
		$handler = fopen ( $file,'r' );
		$output = fread ( $handler, filesize ( $file ) );
		fclose ( $handler );
		return $output;
	}
	
	function shownews () {
		if ( empty ( $_GET['category'] ) ) {
			$category = NULL;
		} else {
			$category = $_GET['category'];
		}
		if ( empty ( $_GET['offset'] ) ) {
			$offset = 0;
		} else {
			$offset = $_GET['offset'];
		}
		$newsmessage = $this->news->getAllNews ($offset,$category);
		if ( errorSDK::is_error ( $newsmessage ) ) {
			$this->error ( $newsmessage );
		} else {
			$output = NULL;
			foreach ( $newsmessage as $news ) {	
				$link = 'news.php?action=viewcomments&amp;id=' . $news['id'];
				$tempoutput = $this->getfile ( 'themes/' . $this->themedir . '/news.html' );		
				$tempoutput = ereg_replace ( '%news.subject',$this->replacerichtext ( $news['subject'],false ),$tempoutput );
				$tempoutput = ereg_replace ( '%news.author',$news['author'],$tempoutput );
				$tempoutput = ereg_replace ( '%news.message',$this->replacerichtext ( $news['message'] ),$tempoutput );
				$tempoutput = ereg_replace ( '%news.image',$news['image'],$tempoutput );
				$tempoutput = ereg_replace ( '%image.alt',$news['alternate'],$tempoutput );
				$tempoutput = ereg_replace ( '%news.date',setdate ( $news['date'] ),$tempoutput );
				$tempoutput = ereg_replace ( '%news.comments',$news['comments'] . '&nbsp;' . $this->lang->translate ('comments'),$tempoutput );
				$tempoutput = ereg_replace ( '%news.commentlink',$link,$tempoutput );
				$output .= $tempoutput;
			}
			$tempoutput = $this->getfile ( 'themes/' . $this->themedir . '/newsnavigator.html' );
			if (! isset ($_GET[GET_OFFSET])) {
				$offset = NULL;
			} else {
				$offset = $_GET[GET_OFFSET];
			}

			if (! isset ($_GET[GET_CATEGORY])) {
				$category = NULL;
			} else {
				$category = $_GET[GET_CATEGORY];
			}

			$offset = $this->news->getLimitNews ($offset,$category);
			$prevlink = 'index.php?category=' . $category . '&amp;offset=' . $offset['previous'];
			$nextlink = 'index.php?category=' . $category . '&amp;offset=' . $offset['next'];
			if ( $offset['previous'] < 0 ) {
				$tempoutput = preg_replace ( '#%ifprev0(.+?)%/ifprev0#', '\\1', $tempoutput );
				// change prev0 in somthing usefull
				$tempoutput = preg_replace ( '#%ifprev>0(.+?)%/ifprev>0#', '', $tempoutput );
				// make prev>0 dissappear
			} else {
				$tempoutput = preg_replace ( '#%ifprev>0(.+?)%/ifprev>0#', '\\1', $tempoutput );
				$tempoutput = preg_replace ( '#%prev.link#', $prevlink, $tempoutput );
				// change prev>0 in somthing usefull
				$tempoutput = preg_replace ( '#%ifprev0(.+?)%/ifprev0#', '', $tempoutput );
				// make prev0 dissappear
			}
			if ( $offset['next'] > $offset['total'] - 1 ) {
				$tempoutput = preg_replace ( '#%ifnext0(.+?)%/ifnext0#', '\\1', $tempoutput );
				$tempoutput = preg_replace ( '#%prev.link#', $prevlink, $tempoutput );
				$tempoutput = preg_replace ( '#%ifnext>0(.+?)%/ifnext>0#', '', $tempoutput );
			} else {
				$tempoutput = preg_replace ( '#%ifnext0(.+?)%/ifnext0#', '', $tempoutput );
				$tempoutput = preg_replace ( '#%next.link#', $nextlink, $tempoutput );
				$tempoutput = preg_replace ( '#%ifnext>0(.+?)%/ifnext>0#', '\\1', $tempoutput );
			}
			
			$output .= $tempoutput;	
			
			if ( $this->user->isLoggedIn () ) {
				$link = 'news.php?action=postnewsform';
				$tempoutput = $this->post_news_link;
				$tempoutput = preg_replace ( '#%postnews.link#', $link , $tempoutput );
				$tempoutput = preg_replace ( '#%postnews.lang#',$this->lang->translate ('Post a news'), $tempoutput );
			} else {
				$tempoutput = NULL;
			}
			
			$tempoutput = preg_replace  ( '#%newspostlink#',$tempoutput,$output );
			$output =  $tempoutput;
		}
		return $output;
	}
	
	function loadlinks () {
		$links = $this->getfile ( 'themes/' . $this->themedir . '/links.html' );
		$language = $this->config->getConfigByNameType ('general/language',TYPE_STRING);
		$sql = "SELECT name,shown_name FROM " . TBL_PAGES . " WHERE language='$language' AND show_in_nav='" . YES ."' AND show_in_user_nav='".NO."'";
		$query = $GLOBALS['database']->query ( $sql );
		$retlink = NULL;
			while ( $link = $this->database->fetch_array ( $query ) ) {
				$temp = $links;
				$temp = ereg_replace ( '%link.name' ,$link['name'] ,$temp );
				$temp = ereg_replace ( '%link.shown_name' ,$link['shown_name'] ,$temp );
				$retlink .= $temp;
			}
		return $retlink;
	}
	
	function loaduserlinks () {
		$userlinks = $this->userlink;
		$language = $this->config->getConfigByNameType('general/language',TYPE_STRING);
		$sql = "SELECT name,shown_name FROM " . TBL_PAGES ." WHERE language='$language' AND show_in_nav='" . YES ."' AND show_in_user_nav='".YES."'";
		$query = $GLOBALS['database']->query ( $sql );
		$retlink = NULL;
		if ( errorSDK::is_error ( $query ) ) {
			$this->error ( $query );
		} else {
			while ( $link = $this->database->fetch_array ( $query ) ) {
				$temp = $userlinks;
				$temp = ereg_replace ( '%link.url' ,$link['name'] ,$temp );
				$temp = ereg_replace ( '%link.shown_name' ,$link['shown_name'] ,$temp );
				$retlink .= $temp;
			}
		}

		return $retlink;
	}
	
	function loadnavigation () {
		$output = $this->getfile ( 'themes/' . $this->themedir . '/navigation.html' );
		return $output;
	}
	
	function contentpage () {
		$pagename = $_SERVER['PHP_SELF'];
		$pagename = ereg_replace ( '/','',$pagename ); 
		// removes '/' in begin of pagename
		$language = $this->config->getConfigByNameType ('general/language',TYPE_STRING);
		$sql = "SELECT * FROM " . TBL_PAGES . " WHERE " . FIELD_PAGES_NAME . "='$pagename' AND " . FIELD_PAGES_LANGUAGE . "='$language'";
		$query = $this->database->query ( $sql );
		if ( errorSDK::is_error ( $query ) ) {
			$this->error ( $query );
		} else {
			if ( $this->database->num_rows ( $query ) == 1 ) {
				while ( $page = $this->database->fetch_array ( $query ) ) {
					return $page['content'];
				}
			}
		}
	}
	
	function get ( $what, $loglevel ) {
		if ( ! empty ( $_GET[ $what ] ) ) {
			switch ( $loglevel ) {
				case 'warning':
					$output =  ereg_replace ( '%warning', $_GET[ $what ], $this->warningmessage );
					break;
				case 'db_warning':
					$output = ereg_replace ( '%warning', $_GET[ $what ], $this->warningmessage );
					break;
				case 'error':
					$output = ereg_replace ( '%error', $_GET[ $what ], $this->errormessage );
					break;
				case 'db_error':
					$output = ereg_replace ( '%db_error', $_GET[ $what ], $this->db_errormessage );
					break;
				case 'note':
					$output = ereg_replace ( '%note', $_GET[ $what ], $this->notemessage );
					break;
				case 'empty':
					return $_GET[ $what ];
			}
			if (!empty($_GET[ERROR_NUMBER])) {
				$output = ereg_replace ('%link',ERROR_LINK . $_GET[ERROR_NUMBER],$output);
			} else {
				$output = ereg_replace ('%link',NULL,$output);
			}
			return $output;
		} else {
			return NULL;
		}
	}
	
	function categories () {
		$sql = "SELECT * FROM " . TBL_CATEGORIES;
		$sql.= " WHERE " . FIELD_CATEGORIES_LANGUAGE ."='" . $this->config->getConfigByNameType('general/language',TYPE_STRING) ."'";
		$query = $this->database->query ( $sql );
		$categories = NULL;
			if ( ! errorSDK::is_error ( $query ) ) {
				while ( $category = $this->database->fetch_array ( $query) ) {
					$categories .= '<option>';
						$categories .= $category[FIELD_CATEGORIES_NAME];
					$categories .= '</option>';
				}
			} else {
				$this->error ( $query );
			}
		return $categories;
	}
	
	function on_comment () {
		if ( empty ( $_GET['id_comment'] ) ) {
			return 'NULL';
		} else {
			return $_GET['id_comment'];
		}
	}
	
	function options ( $options,$who,$currentvalue ) {
		$output = $who['open'];
		foreach ( $options as $option ) {
			if ( $option == $currentvalue ) {
				$output .= $who['syntax_curval'];
			} else {
				$output .= $who['syntax'];
			}
			$output = ereg_replace ( '%option',$option,$output );
		}
		$output .= $who['close'];
		return $output;
	} // function options
	
	function showallpolls ( $polls ) {
		$output = NULL;
		foreach ( $polls as $poll ) {
			$output .= $this->polllink;
			$output = ereg_replace ( '%poll.question',$poll[FIELD_POLL_QUESTION],$output);
			$output = ereg_replace ( '%poll.link', 'polls.php?action=allpolls&poll=' . $poll[FIELD_POLL_ID],$output);
		}
		return $output;
	}
	
	function showpoll () {
		try {
			if ( empty ( $_GET['poll'] ) ) {
				$id = $GLOBALS['poll']->getidcurrentpollbylanguage ($this->config->getConfigByNameType('general/language',TYPE_STRING));
			} else {
				$id = $_GET['poll'];
			}
			
			$poll = $GLOBALS['poll']->getpollbyid ( $id );
			$output = $this->getfile ( 'themes/' . $this->themedir . '/viewpollresults.html' );
			$output = ereg_replace ( '%poll.question',$poll[FIELD_POLL_QUESTION],$output );
			$output = ereg_replace ( '%poll.result',$this->pollresults ( explode ( ';',$poll[FIELD_POLL_CHOICES] ),explode ( ';',$poll[FIELD_POLL_RESULTS] ) ),$output );
			return $output;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	}
	
	function viewuserlist () {
		try {
			$sql = "SELECT " . FIELD_USERS_NAME . " FROM " . TBL_USERS . " WHERE " . FIELD_USERS_PUBLIC_USER . "='" . YES . "' ORDER by " . FIELD_USERS_NAME . " asc ";
			$query = $GLOBALS['database']->query ( $sql );
			$output = NULL;
			while ( $user = $GLOBALS['database']->fetch_array ( $query ) ) {
				$output .= $this->touserinfolink;
				$link = 'users.php?action=viewuser&user=' . $user[FIELD_USERS_NAME];
				$output = ereg_replace ( '%user.name',$user[FIELD_USERS_NAME],$output );
				$output = ereg_replace ( '%user.url',$link,$output );
			}
			return $output;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	}
	
	function replaceprofile () {
		
	}
	
	function replacesmilies ( $output ) {
		foreach ( $this->smilies as $smiley ) {
			$output = ereg_replace ( $smiley['text'],$smiley['output'],$output );
		}
		$output = $this->replaceimages ( $output );
		return $output;
	}
	
	function replaceimages ( $output )  {
		$output = preg_replace ( '#%image.(.+?)#','themes/' . $this->themedir . '/images/\\1',$output );
		return $output;
	}
	
	function replace_all ( $page ) {
		//global $GLOBALS['lang'];
		$output = $page;
		$output = ereg_replace ( '%css' ,'themes/' . $this->themedir . '/standard.css',$output );
		$output = ereg_replace ( '%databasetype.lang' ,$this->lang->translate ('type'),$output );
		$output = ereg_replace ( '%databasehost.lang' ,$this->lang->translate ('host'),$output );
		$output = ereg_replace ( '%databasename.lang' ,$this->lang->translate ('name'),$output );
		$output = ereg_replace ( '%databasepassword.lang' ,$this->lang->translate ('password'),$output );
		$output = ereg_replace ( '%installation.lang' ,$this->lang->translate ('Install'),$output );
		$output = ereg_replace ( '%install.lang' ,$this->lang->translate ('Install!!'),$output );
		$output = ereg_replace ( '%installscript.method' ,'post',$output );
		$output = ereg_replace ( '%installscript.action' ,'install.php',$output );
		$output = ereg_replace ( '%database.options' ,$this->options ( databasesinstalled() ,$this->database_option,$this->config->getConfigByNameType('general/databasetype',TYPE_STRING)),$output );
		$output = ereg_replace ( '%copyright.site.text' ,COPYRIGHT,$output );
		$output = ereg_replace ( '%copyright.site.link' ,'about.php',$output );
		$output = ereg_replace ( '%copyright.theme.text' ,$this->copyright,$output );
		$output = ereg_replace ( '%copyright.theme.link' ,$this->themelink,$output );
		$output = ereg_replace ( '%navigation.lang' ,$this->lang->translate ('Navigation'),$output );
		$output = ereg_replace ( '%register.lang' ,$this->lang->translate ('Register'),$output );
		$output = ereg_replace ( '%register.error' ,$this->get ( get_registererror, 'error' ),$output );
		$output = ereg_replace ( '%email.formname' ,POST_EMAIL,$output );
		$output = ereg_replace ( '%password1.formname' ,POST_PASSWORD1,$output );
		$output = ereg_replace ( '%password2.formname' ,POST_PASSWORD2,$output );
		$output = ereg_replace ( '%user.formname' ,POST_NAME,$output );
		$output = ereg_replace ( '%databaseuser.lang',$this->lang->translate ('usernmae'),$output );
		$output = ereg_replace ( '%sendpassword.lang' ,$this->lang->translate ('Forgot your password?'),$output );
		$output = ereg_replace ( '%sendpassword.error' ,$this->get ( get_sendpassworderror, 'error' ),$output );
		$output = ereg_replace ( '%or.lang' ,$this->lang->translate ('or'),$output );
		$output = ereg_replace ( '%sendpassword.action' ,sendpassword_action,$output );
		$output = ereg_replace ( '%sendpassword.method' ,form_method,$output );
		$output = ereg_replace ( '%username.lang' ,$this->lang->translate ('username'),$output );
		$output = ereg_replace ( '%password1.lang' ,$this->lang->translate ('password'),$output );
		$output = ereg_replace ( '%password2.lang' ,$this->lang->translate ('repeat your password'),$output );
		$output = ereg_replace ( '%email.lang' ,$this->lang->translate ('e-mail'),$output );
		$output = ereg_replace ( '%registerform.action' ,registerform_action,$output );
		$output = ereg_replace ( '%registerform.method' ,form_method,$output );
		$output = ereg_replace ( '%postnews.method' ,form_method,$output );
		$output = ereg_replace ( '%changeoptions.method' ,form_method,$output );
		$output = ereg_replace ( '%postnews.action' ,'news.php?action=postnews',$output );
		$output = ereg_replace ( '%postcomment.method' ,form_method,$output );
		$output = ereg_replace ( '%postcomment.action' ,'news.php?action=postcomment',$output );
		$output = ereg_replace ( '%on_news.value' ,$this->get ( 'id_news','empty' ),$output );
		$output = ereg_replace ( '%on_comment.value' ,$this->on_comment (),$output );
		$output = ereg_replace ( '%postnews.lang' ,$this->lang->translate ('Post a news'),$output );
		$output = ereg_replace ( '%subject.lang' ,$this->lang->translate ('Subject'),$output );
		$output = ereg_replace ( '%newsmessage.lang' ,$this->lang->translate ('Message'),$output );
		$output = ereg_replace ( '%category.lang' ,$this->lang->translate ('Category'),$output );
		$output = ereg_replace ( '%post.lang' ,$this->lang->translate ('Post!!'),$output );
		$output = ereg_replace ( '%logout.lang' ,$this->lang->translate ('Logout'),$output );
		$output = ereg_replace ( '%logout.link' ,'users.php?action=logout',$output );
		$output = ereg_replace ( '%timezone.lang' ,$this->lang->translate ('timezone'),$output );
		$output = ereg_replace ( '%timeformat.lang' ,$this->lang->translate ('timeformat'),$output );
		$output = ereg_replace ( '%valueheadlines.lang' ,$this->lang->translate ('Number of headlines visible'),$output );
		$output = ereg_replace ( '%headlines.lang' ,$this->lang->translate ('Headlines'),$output );
		$output = ereg_replace ( '%comments_on_news.lang' ,$this->lang->translate ('Comments on newsmessage'),$output );
		$output = ereg_replace ( '%postsonpage.lang' ,$this->lang->translate ('Number of posts visible'),$output );
		$output = ereg_replace ( '%threaded.lang' ,$this->lang->translate ('View comments threaded'),$output );
		$output = ereg_replace ( '%theme.lang' ,$this->lang->translate ('theme'),$output );
		$output = ereg_replace ( '%language.lang' ,$this->lang->translate ('language'),$output );
		$output = ereg_replace ( '%postcomment.lang' ,$this->lang->translate ('Post a comment'),$output );
		$output = ereg_replace ( '%new_password1.lang' ,$this->lang->translate ('Type a new password'),$output );
		$output = ereg_replace ( '%new_password2.lang' ,$this->lang->translate ('Retype your new password'),$output );
		$output = ereg_replace ( '%poll.action' ,POLLS_VOTE_ACTION,$output );
		$output = ereg_replace ( '%poll.method' ,'post',$output );
		$output = ereg_replace ( '%viewallpols.lang' ,$this->lang->translate ('View all polls'),$output );
		$output = ereg_replace ( '%profile.lang' ,$this->lang->translate ('Profile'),$output );
		$output = ereg_replace ( '%job.lang' ,$this->lang->translate ('Job'),$output );
		$output = ereg_replace ( '%intrests.lang' ,$this->lang->translate ('Intrests'),$output );
		$output = ereg_replace ( '%icq.lang' ,$this->lang->translate ('ICQ'),$output );
		$output = ereg_replace ( '%aim.lang' ,$this->lang->translate ('AIM'),$output );
		$output = ereg_replace ( '%yahoo.lang' ,$this->lang->translate ('Yahoo'),$output );
		$output = ereg_replace ( '%website.lang' ,$this->lang->translate ('Site'),$output );
		$output = ereg_replace ( '%adress.lang' ,$this->lang->translate ('Adress'),$output );
		$output = ereg_replace ( '%msn.lang' ,$this->lang->translate ('MSN'),$output );
		$output = ereg_replace ( '%jabber.lang' ,$this->lang->translate ('Jabber'),$output );

		$timezone = $this->config->getConfigByNameType('general/timezone',TYPE_INT);
		$timeformat = $this->config->getConfigByNameType('general/timeformat',TYPE_STRING);
		$headlines = $this->config->getConfigByNameType('news/headlines',TYPE_INT);
		$postsonpage = $this->config->getConfigByNameType('news/postsonpage',TYPE_INT);
		$threaded =$this->config->getConfigByNameType('news/threaded',TYPE_BOOL);
		$theme = $this->config->getConfigByNameType('general/theme',TYPE_STRING);
		$email =$this->config->getConfigByNameType('user/email',TYPE_STRING);
		$name =  $this->config->getConfigByNameType('user/name',TYPE_STRING);
		$language = $this->config->getConfigByNameType('general/language',TYPE_STRING);
		$userprofile = $this->user->getotherprofile ($name);
		$pagename = $_SERVER['PHP_SELF'];
		//$pagename = ereg_replace ( '/','',$pagename ); 
		// removes '/' in begin of pagename
		if ( ( ereg ('news.php',$pagename) ) AND ( ! empty ( $_GET['action'] ) ) ) {
			if ( $_GET['action'] == 'viewcomments' ) {
				if ($this->config->getConfigByNameType ('news/threaded',TYPE_BOOL) == false) {
					$output = ereg_replace ( '%commentnavigator.html',$this->commentnavigator (),$output );
				} else {
					$output = ereg_replace ( '%commentnavigator.html','',$output );
				}
			}
		}
		$output = ereg_replace ( '%timezone.timezone' ,strval($timezone),$output );
		$output = ereg_replace ( '%timeformat.timeformat' ,$timeformat,$output );
		$output = ereg_replace ( '%headlines.headlines' ,strval($headlines),$output );
		$output = ereg_replace ( '%postsonpage.postsonpage' ,strval($postsonpage),$output );
		$output = ereg_replace ( '%email.email' ,$email,$output );
		$output = ereg_replace ( '%username.user' ,$name,$output );
		$output = ereg_replace ( '%aim.aim' ,$userprofile[FIELD_USERS_PROFILE_AIM],$output );
		$output = ereg_replace ( '%aim.name' ,'newaim',$output );
		$output = ereg_replace ( '%icq.icq' ,$userprofile[FIELD_USERS_PROFILE_ICQ],$output );
		$output = ereg_replace ( '%icq.name' ,'newicq',$output );
		$output = ereg_replace ( '%msn.msn' ,$userprofile[FIELD_USERS_PROFILE_MSN],$output );
		$output = ereg_replace ( '%msn.name' ,'newmsn',$output );
		$output = ereg_replace ( '%jabber.jabber' ,$userprofile[FIELD_USERS_PROFILE_JABBER],$output );
		$output = ereg_replace ( '%jabber.name' ,'newjabber',$output );
		$output = ereg_replace ( '%adress.adress' ,$userprofile[FIELD_USERS_PROFILE_ADRESS],$output );
		$output = ereg_replace ( '%adress.name' ,'newadress',$output );
		$output = ereg_replace ( '%website.website' ,$userprofile[FIELD_USERS_PROFILE_WEBSITE],$output );
		$output = ereg_replace ( '%website.name' ,'newwebsite',$output );
		$output = ereg_replace ( '%yahoo.yahoo' ,$userprofile[FIELD_USERS_PROFILE_YAHOO],$output );
		$output = ereg_replace ( '%yahoo.name' ,'newyahoo',$output );
		$output = ereg_replace ( '%job.job' ,$userprofile[FIELD_USERS_PROFILE_JOB],$output );
		$output = ereg_replace ( '%job.name' ,'newjob',$output );
		$output = ereg_replace ( '%intrests.intrests' ,$userprofile[FIELD_USERS_PROFILE_INTRESTS],$output );
		$output = ereg_replace ( '%intrests.name' ,'newintrests',$output );
	
		$output = ereg_replace ( '%language.options' ,$this->options ( languagesinstalled(),$this->language_option,$language ) ,$output );
		$boolthreaded = $this->config->getConfigByNameType ('news/threaded',TYPE_BOOL);
		convertToDatabase ($boolthreaded);
		$output = ereg_replace ( '%threaded.options' ,$this->options ( array (NO,YES),$this->threaded_option,$boolthreaded),$output );
		$output = ereg_replace ( '%theme.options' ,$this->options ( themesinstalled(),$this->theme_option,$theme ) ,$output );
		
		$output = ereg_replace ( '%databasename.name' ,'name',$output );
		$output = ereg_replace ( '%databasepassword.name' ,'password',$output );
		$output = ereg_replace ( '%databaseuser.name' ,'user',$output );
		$output = ereg_replace ( '%databasehost.name' ,'host',$output );
		$output = ereg_replace ( '%database.name' ,'type',$output );
		$output = ereg_replace ( '%timezone.name' ,POST_TIMEZONE,$output );
		$output = ereg_replace ( '%timeformat.name' ,POST_TIMEFORMAT,$output );
		$output = ereg_replace ( '%headlines.name' ,POST_HEADLINES,$output );
		$output = ereg_replace ( '%postsonpage.name' ,POST_POSTSONPAGE,$output );
		$output = ereg_replace ( '%threaded.name' ,POST_THREADED,$output );
		$output = ereg_replace ( '%theme.name' ,POST_THEME,$output );
		$output = ereg_replace ( '%email.name' ,POST_EMAIL,$output );
		$output = ereg_replace ( '%language.name' ,POST_LANGUAGE,$output );
		$output = ereg_replace ( '%new_password1.name' ,POST_NEW_PASSWORD1,$output );
		$output = ereg_replace ( '%new_password2.name' ,POST_NEW_PASSWORD2,$output );
		$output = ereg_replace ( '%changeoptionsform.lang',$this->lang->translate ('edit your options'),$output );
		$output = ereg_replace ( '%changeoptionsform.submit',$this->lang->translate ('save your options'),$output );
		$output = ereg_replace ( '%changeoptionsform.action',changeoptions_action,$output );
		$output = ereg_replace ( '%changeoptionsform.action',changeoptions_action,$output );
		//$output = ereg_replace ( '%showlinksallpolls',$this->showallpolls ($GLOBALS['poll']->getallpollsbylanguage ($this->config->getConfigByNameType('general/language',TYPE_STRING)),$output);
		$output = ereg_replace ( '%showselectedpoll',$this->showpoll (),$output );
		$output = ereg_replace ( '%user.links',$this->loaduserlinks (),$output );
		$output = ereg_replace ( '%viewusers.list',$this->viewuserlist (),$output );
		$output = ereg_replace ('%description%',
			$this->config->getConfigByNameType ('general/description',TYPE_STRING),
			$output);
		$output = ereg_replace ('%editcommentform.lang',$this->lang->translate ('Edit this comment'),$output );
		$output = ereg_replace ('%editcomment.lang',$this->lang->translate ('Edit!!'),$output );
		$output = ereg_replace ('%poll.language',$this->lang->translate ('Poll'),$output );
		$output = ereg_replace ('%vote.lang',$this->lang->translate ('Vote!!'),$output );
		$output = $this->replaceimages ( $output );
		return $output;
	}
	
	function loadloginform () {
		//echo $this->user->loggedin ();
		$output = $this->getfile ( 'themes/' . $this->themedir . '/loginform.html' );
		if ( ! $this->user->isLoggedIn () ) {
			$output = preg_replace ( '#\n#','',$output );
			//remove all newlines ( to avoid problmes with next line )
			$output = preg_replace ( '#\{loggedin\}(.+?)\{/loggedin}#','\\1',$output );
			$output = preg_replace ( '#\{notloggedin\}(.+?)\{/notloggedin\}#','',$output );
			$output = ereg_replace ( '%login.error' ,$this->get ( get_loginerror, 'error' ),$output );
			$output = ereg_replace ( '%login.action', loginform_action, $output );
			$output = ereg_replace ( '%login.method', form_method, $output );
			$output = ereg_replace ( '%username.lang',$this->lang->translate ('username'), $output );
			$output = ereg_replace ( '%username.formname', POST_NAME, $output );
			$output = ereg_replace ( '%password.lang',$this->lang->translate ('password'), $output );
			$output = ereg_replace ( '%password.formname', POST_PASSWORD, $output );
			$output = ereg_replace ( '%login.lang',$this->lang->translate ('login'), $output );
			$output = ereg_replace ( '%toregisterform_action', toregisterform_action, $output );
			$output = ereg_replace ( '%notyetregisterd.lang',$this->lang->translate ('Not yet registerd?'), $output );
			if ( ! empty ( $_GET[get_error] ) ) {
				$output = ereg_replace ( '%getusernameorpassword.lang',$this->lang->translate ('Forgotten your password/username?'), $output );
			} else {
				$output = ereg_replace ( '%getusernameorpassword.lang', NULL, $output );
			}
			$output = ereg_replace ( '%getusernameorpassword.link', sendpasswordform_action, $output );
		} else {
			$output = preg_replace ( '#\n#','',$output );
			//remove all newlines ( to avoid problmes with next line )
			$output = preg_replace ( '#\{loggedin\}(.+?)\{/loggedin}#','',$output );
			$output = preg_replace ( '#\{notloggedin\}(.+?)\{/notloggedin\}#','\\1',$output );
			$output = ereg_replace ( '%user.links',$this->loaduserlinks (),$output );
			$output = ereg_replace ( '%users.lang',$this->lang->translate ('User'),$output );
			$output = ereg_replace ( '%logout.lang',$this->lang->translate ('logout'),$output );
			$output = ereg_replace ( '%changeoptionsform.lang',$this->lang->translate ('Edit your options'),$output );
			$output = ereg_replace ( '%changeoptionsform.link',tochangeoptionsform,$output );
			$output = ereg_replace ( '%logout.link',logout,$output );
		}
		return $output;
	}
	
	function replacecomment ( $comment ) {
		$output = $this->getfile ( 'themes/' . $this->themedir . '/newscomment.html' );
		$output = ereg_replace ( '%comment.subject',$this->replacerichtext ( $comment['subject'],false ),$output );
		$output = ereg_replace ( '%comment.author',$comment['author'],$output );
		$output = ereg_replace ( '%comment.message',$this->replacerichtext ( $comment['message'] ),$output );
		$output = ereg_replace ( '%comment.date',setdate ( $comment['date'] ),$output );
		$link = 'news.php?action=postcommentform&amp;id_comment=' . $comment['id'] . '&amp;id_news=' . $comment['id_news'];
		$output = ereg_replace ( '%comment.newcommentlink',$link,$output );
		$output = ereg_replace ( '%comment.newcomment',$this->lang->translate ('Post a comment'),$output );
		if ( $comment['author']  == $this->config->getConfigByNameType ('user/name',TYPE_STRING)) {
			$output = ereg_replace ( '%edit.button',$this->newstheme->editbutton,$output );
			$output = ereg_replace ( '%link','news.php?action=editcommentform&amp;id=' . $comment['id'],$output );
		} else {
			$output = ereg_replace ( '%edit.button','',$output );
		}
		return $output;
	}
	
	function startthread ( $threadstart ) {
		$output = $this->replacecomment ( $threadstart );
		foreach ( $this->news->getthreadfollows ($_GET[GET_NEWSID], $threadstart ) as $threadfollow ) {
			$output .= $this->openthread;
			$output .= $this->startthread ( $threadfollow );
			$output .= $this->closethread;
		}
		return $output;
	}
	
	function startflatthread () {
		$output = NULL;
		if (empty ($_GET['offset'])) {
			$offset = 0;
		} else {
			$offset = $_GET['offset'];
		}
		$allcomments = $this->news->getallcomments ($_GET[GET_NEWSID],$offset);
		if ( ! errorSDK::is_error ( $allcomments ) ) {
			foreach ( $allcomments  as $comment ) {
				$output .= $this->replacecomment ( $comment );
			}
			return $output;
		} else {
			$this->error ( $allcomments );
		}
	}
	
	function commentnavigator () {
		if (! isset ($_GET[GET_OFFSET])) {
			$offset = NULL;
		} else {
			$offset = $_GET[GET_OFFSET];
		}

		if (! isset ($_GET[GET_NEWSID])) {
			throw new exceptionlist ('News id is not set');
		}
		$offset = $this->news->getLimitComments ($_GET[GET_NEWSID],$offset);
		$output = $this->getfile ( 'themes/' .  $this->themedir . '/commentnavigator.html' );
		if ( $offset['previous'] < 0 ) {
			$output = preg_replace ( '#%ifprev0(.+?)%/ifprev0#', '\\1', $output );
			// change prev0 in somthing usefull
			$output = preg_replace ( '#%ifprev>0(.+?)%/ifprev>0#', '', $output );
			// make prev>0 dissappear
		} else {
			$link = 'news.php?action=viewcomments&id=' . $_GET['id'] . '&offset=' . $offset['previous'];
			$output = preg_replace ( '#%ifprev>0(.+?)%/ifprev>0#', '\\1', $output );
			$output = preg_replace ( '#%prev.link#', $link, $output );
			// change prev>0 in somthing usefull
			$output = preg_replace ( '#%ifprev0(.+?)%/ifprev0#', '', $output );
			// make prev0 dissappear
		}
		if ( $offset['next'] > $offset['total'] - 1 ) {
			$output = preg_replace ( '#%ifnext0(.+?)%/ifnext0#', '\\1', $output );
			$output = preg_replace ( '#%ifnext>0(.+?)%/ifnext>0#', '', $output );
		} else {
			$link = 'news.php?action=viewcomments&id=' . $_GET['id'] . '&offset='. $offset['next'];
			$output = preg_replace ( '#%ifnext0(.+?)%/ifnext0#', '', $output );
			$output = preg_replace ( '#%next.link#', $link, $output );
			$output = preg_replace ( '#%ifnext>0(.+?)%/ifnext>0#', '\\1', $output );
		}
		return $output;
	}
	
	function showcomments () {
		if ( empty ( $_GET[GET_NEWSID] ) ) {
			return NULL;
		} else {
			$output = NULL;
			if ($this->config->getConfigByNameType('news/threaded',TYPE_BOOL) == YES) {
				foreach ( $this->news->startthreads ($_GET[GET_NEWSID]) as $threadstart ) {
					$output .= $this->startthread ( $threadstart );
				}
			} else {
				$output .= $this->startflatthread ();
			}
			return $output;
		}
	}
	
	function replaceubb ( $text,$ubbtag,$htmlopen,$htmlclose ) {
		$text = preg_replace ( '#\[' . $ubbtag . '\]#' , $htmlopen,$text );
		$text = preg_replace ( '#\[/' . $ubbtag . '\]#',$htmlclose,$text );
		return $text;
	}
	
	function replacerichtext ( $input,$useubb = true ) {
		$output = strip_tags ( $input );
		$output = nl2br ( $output );
		$output = $this->replacesmilies ( $output );
		if ( $useubb == true ) {
			$output = $this->replaceubb ( $output,'quote',$this->quote['open'],$this->quote['close'] );
			$output = $this->replaceubb ( $output,'b',$this->b['open'],$this->b['close'] );
			$output = $this->replaceubb ( $output,'u',$this->u['open'],$this->u['close'] );
			$output = $this->replaceubb ( $output,'i',$this->i['open'],$this->i['close'] );
		}
		return $output;
	}
	
	function shownewsmessage () {
		if ( empty ( $_GET['id'] ) ) {
			return NULL;
		} else {
			$id = $_GET['id'];
			$news = $this->news->getnews ( $id );
			$output = NULL;
			if ( errorSDK::is_error ( $news ) ) {
				return $news;
			} else {
				$link = 'comments.php?id=' . $news['id'];
				$output = $this->getfile ( 'themes/' . $this->themedir . '/newsmessage.html' );		
				$output = ereg_replace ( '%news.subject',$this->replacerichtext ( $news['subject'],false ),$output );
				$output = ereg_replace ( '%news.author',$news['author'],$output );
				$output = ereg_replace ( '%news.message',$this->replacerichtext ( $news['message'] ),$output );
				//$output = ereg_replace ( '%news.image',$news['image'],$output );
				$output = ereg_replace ( '%news.date',setdate ( $news['date'] ),$output );
				$output = ereg_replace ( '%news.comments',$news['comments'] . '&nbsp;' . $this->lang->translate ('Comments'),$output );
				$output = ereg_replace ( '%news.commentlink',$link,$output );
				$output = ereg_replace ( '%news.newcomment',$this->lang->translate ('Post a comment'),$output );
				$link2 = 'news.php?action=postcommentform&amp;id_news=' . $_GET['id'];
				$output = ereg_replace ( '%news.link.newcomment',$link2,$output );
				return $output;
			}
		}
	}
	
	function redirect ( $link ) {
		error_reporting ( E_NONE );
		header ( 'Location: ' . $link );
		$output = $this->getfile ( 'themes/' . $this->themedir . '/redirect.html' );
		$output = ereg_replace ( '%redirect.content' ,$link,$output );
		echo $output;
		exit ();
	}
	
	function showheadlines () {
		$fileoutput = $this->getfile ( 'themes/' . $this->themedir . '/headlines.html' );
		$output = NULL;
		$category = NULL; // TODO
		$headlines = $this->news->getHeadlines ($category);
		
		if ( errorSDK::is_error ( $headlines ) ) {
			$this->error ( $headlines );
		} else {
			$output = NULL;
			foreach ( $headlines as $headline ) {
				$tempoutput = ereg_replace ( '%headline.title',$headline['subject'],$fileoutput );
				$tempoutput = ereg_replace ( '%headline.message', substr ( $headline['message'],0,60) . '...',$tempoutput );
				$tempoutput = ereg_replace ( '%headline.user',$headline['author'],$tempoutput );
				$tempoutput = ereg_replace ( '%headline.date',setdate ( $headline['date'] ),$tempoutput );
				$tempoutput = ereg_replace ( '%headline.link','news.php?action=viewcomments&amp;id=' . $headline['id'],$tempoutput );
				$output .= $tempoutput;
			}
		}
		return $output;
	}
	
	function editcommentform () {
		$output = NULL;
		if ( isset ( $_GET['id'] ) ) {
			$fileoutput = $this->getfile ( 'themes/' . $this->themedir . '/editcommentform.html' );
			$comment = $this->news->getcomment ( $_GET['id'] );
			$output = ereg_replace ( '%message.current',$comment['message'],$fileoutput );
			$output = ereg_replace ( '%subject.current',$comment['subject'],$output );
			$output = ereg_replace ( '%editcomment.action','news.php?action=editcomment&id=' . $comment['id'],$output );
			$output = ereg_replace ( '%editcomment.method','post',$output );
		}
		return $output;
	} // function editcommentform
	
	function pollanswerstovote ( $answers ) {
		$output = NULL;
		$id = 0;
		foreach ( $answers as $answer ) {
			$output .= $this->pollanswertovote;
			$output = ereg_replace ( '%answer.text',$answer,$output );
			$output = ereg_replace ( '%answer.id',"$id",$output );
			$output = ereg_replace ( '%choices.name',POST_VOTED_ON,$output );
			$id++;
		}
		return $output;
	}
	
	function pollresults ( $answers,$results ) {
		$output = NULL;
		$total = array_sum ( $results );
		if ( $total == 0 ) {
			$total = 1;
		}
		$i = 0;
		foreach ( $answers as $answer ) {
			$output .= $this->pollresults;
			$output = ereg_replace ( '%answer',$answer,$output );
			$percent = round ( $results[$i]/$total*100 );
			$output = ereg_replace ( '%votes.percent',"$percent",$output ); 
				// use "$percent" otherwise i does not work ( conversion to ASCII )
			$i++;
		}
		return $output;
	}
	
	function shortviewcurrentpoll () {
		try {
			$poll = $GLOBALS['poll']->getpollbyid ( $GLOBALS['poll']->getidcurrentpollbylanguage ($this->config->getConfigByNameType ('general/language',TYPE_STRING)) );
			if ($poll != NULL) {
				$output = $this->getfile ( 'themes/' . $this->themedir . '/shortviewpoll.html' );
				$output = ereg_replace ( '%poll.question',$poll['question'],$output);
				$output = preg_replace ( '#\n#','',$output );
				//remove all newlines ( to avoid problmes with next lines )
				$voted = $GLOBALS['poll']->userhasvoted ($this->user->getconfig ('name'));
				if ( $voted == true ) {
					$output = preg_replace ( '#\{ifvoted\}(.+?)\{/ifvoted}#','\\1',$output );
					$output = preg_replace ( '#\{ifnotvoted\}(.+?)\{/ifnotvoted\}#','',$output );
					$output = ereg_replace ( '%poll.result',$this->pollresults ( explode ( ';',$poll[FIELD_POLL_CHOICES] ),explode ( ';',$poll[FIELD_POLL_RESULTS] ) ),$output );
				} else {
					$output = preg_replace ( '#\{ifvoted\}(.+?)\{/ifvoted}#','',$output );
					$output = preg_replace ( '#\{ifnotvoted\}(.+?)\{/ifnotvoted\}#','\\1',$output );
					$output = ereg_replace ( '%poll.choice',$this->pollanswerstovote ( explode ( ';',$poll[FIELD_POLL_CHOICES] ) ),$output );
				}
				$output = ereg_replace ('%viewallpolls.link','polls.php?action=allpolls',$output);
			} else {
				$output = NULL;
			}
			return $output;
		} catch (exceptionlist $e) {
			throw $e;
		}
	}
	
	function viewprofile () {
		try {
			if ( isset ( $_GET['user'] ) ) {
				$profile = $this->user->getotherprofile ( $_GET['user'] );
				$output = $this->getfile ( 'themes/' . $this->themedir . '/profile.html' );
				$output = ereg_replace ( '%user.name',$profile[FIELD_USERS_NAME],$output );
				$output = ereg_replace ( '%user.job',$profile[FIELD_USERS_PROFILE_JOB],$output );
				$output = ereg_replace ( '%user.icq',$profile[FIELD_USERS_PROFILE_ICQ],$output );
				$output = ereg_replace ( '%user.aim',$profile[FIELD_USERS_PROFILE_AIM],$output );
				$output = ereg_replace ( '%user.msn',$profile[FIELD_USERS_PROFILE_MSN],$output );
				$output = ereg_replace ( '%user.yahoo',$profile[FIELD_USERS_PROFILE_YAHOO],$output );
				$output = ereg_replace ( '%user.intrests',$profile[FIELD_USERS_PROFILE_INTRESTS],$output );
				$output = ereg_replace ( '%user.website',$profile[FIELD_USERS_PROFILE_WEBSITE],$output );
				$output = ereg_replace ( '%user.adress',$profile[FIELD_USERS_PROFILE_ADRESS],$output );
				$output = ereg_replace ( '%user.email',$profile[FIELD_USERS_EMAIL],$output );
				$output = ereg_replace ( '%user.jabber',$profile[FIELD_USERS_PROFILE_JABBER],$output );
				return $output;
			} else {
				// throw
			}
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	}

	function helpcategories ($parent) {
		try {
			$cat = array ();
			foreach ($this->help->getAllCategoriesIDByParent ($parent) as $subcat) {
				$cat[$subcat] = $this->helpcategories ($subcat);
			}
			return $cat;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	}

	function replace_helpindex ($index,$nr = 0) {
		$output = NULL;
		$nr++;
		if (array_key_exists ($nr,$this->helpindex)) {
			$replace[$nr] = $this->helpindex[$nr];
		} else {
			$replace[$nr]['start'] = '<ol>';
			$replace[$nr]['item'] = '<li><a href="#%itemid%">%item%</a></li>';
			$replace[$nr]['end'] = '</ol><ul>%questions%</ul>';
			$replace[1]['end'] = '</ol>';
		}
		$output .= $replace[$nr]['start'];
		foreach ($index as $parentkey => $i) {
			$cat = $this->help->getCatByIDAndLang ($parentkey,$this->config->getConfigByNameType ('general/langcode',TYPE_STRING));
			$output .= $replace[$nr]['item'];
			$output = ereg_replace ('%item%',$cat['name'],$output);
			$output = ereg_replace ('%itemid%','category' . $parentkey,$output);
			$output .= $this->replace_helpindex ($i,$nr);
			// add the questions
			$q = $this->help->getAllQByCategoryIDAndLang ($parentkey,
				$this->config->getConfigByNameType ('general/langcode',TYPE_STRING));
			$qo = NULL;
			foreach ($q as $question) {
				$qotmp = $this->helpindexquestion;
				$qo .= ereg_replace ('%question%',$question['question'],$qotmp);
				$qo = ereg_replace ('%itemid%','question' . $question['id'],$qo);
			}
			$output = ereg_replace ('%questions%',$qo,$output);
		}
		$output .= $replace[$nr]['end'];
		return $output;
	}

	function replace_helpcontent ($index,$nr = 0) {
		$output = NULL;
		$nr++;
		if (array_key_exists ($nr,$this->helpcontent)) {
			$replace[$nr] = $this->helpcontent[$nr];
		} else {
			$replace[$nr]['start'] = '<ol>';
			$replace[$nr]['item'] = '<li id="%itemid%">%item%</li>';
			$replace[$nr]['end'] = '</ol><ul>%questions%</ul>';
			$replace[1]['end'] = '</ol>';
		}
		$output .= $replace[$nr]['start'];
		foreach ($index as $parentkey => $i) {
			$cat = $this->help->getCatByIDAndLang ($parentkey,$this->config->getConfigByNameType ('general/langcode',TYPE_STRING));
			$output .= $replace[$nr]['item'];
			$output = ereg_replace ('%item%',$cat['name'],$output);
			$output = ereg_replace ('%itemid%','category' . $parentkey,$output);
			$output .= $this->replace_helpcontent ($i,$nr);
			// add the questions
			$q = $this->help->getAllQByCategoryIDAndLang ($parentkey,
				$this->config->getConfigByNameType ('general/langcode',TYPE_STRING));
			$qo = NULL;
			foreach ($q as $question) {
				$qotmp = $this->helpcontentquestion;
				$qo .= ereg_replace ('%question%',$question['question'],$qotmp);
				$qo = ereg_replace ('%answer%',$question['answer'],$qo);
				$qo = ereg_replace ('%itemid%','question' . $question['id'],$qo);
			}
			$output = ereg_replace ('%questions%',$qo,$output);
		}
		$output .= $replace[$nr]['end'];
		return $output;
	}

	function helpindex () {
		try {
			$output = NULL;
			$index = $this->helpcategories (0);
			$output .= $this->replace_helpindex ($index);
			return $output;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	}

	function helpcontent () {
		try {
			$output = NULL;
			$index = $this->helpcategories (0);
			$output .= $this->replace_helpcontent ($index);
			return $output;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	}
	
	function help () {
		$output = $this->getfile ('themes/' . $this->themedir . '/helpcontent.html');
		$output = ereg_replace ('%helpindex.main',$this->helpindex (),$output);
		$output = ereg_replace ('%helpcontent.main',$this->helpcontent (),$output);
		return $output;
	}
	
	function themefile ( $file,$mustlogin = false,$basic = false) {
		if ( ( $mustlogin == true ) AND ( $this->user->isLoggedIn () != true ) ) {
			$this->redirect ( 'index.php?warning=' . $this->lang->translate ('Login is required') );
		}
		$output = $this->getfile ( 'themes/' . $this->themedir . '/' . $file );
		
		$output = ereg_replace ( '%note',$this->get ( 'note','note' ) ,$output );
		$output = ereg_replace ( '%error' ,$this->get ( 'error','error' ),$output );
		$output = ereg_replace ( '%db_error' ,$this->get ( 'db_error','db_error' ),$output );
		$output = ereg_replace ( '%db_warning' ,$this->get ( 'db_warning','db_warning' ),$output );
		$output = ereg_replace ( '%warning' ,$this->get ( 'warning','warning' ),$output );
		if ( ! $basic ) {			
			$output = ereg_replace ( '%categories',$this->categories (),$output );
			$output = ereg_replace ( '%loginform.html',$this->loadloginform (),$output );
			$output = ereg_replace ( '%navigation.html',$this->loadnavigation (),$output );			
			$output = ereg_replace ( '%headlines.html',$this->showheadlines (),$output );			
			$output = ereg_replace ( '%title',$this->title (),$output );	
			$output = ereg_replace ( '%links.html',$this->loadlinks (),$output );
			$output = ereg_replace ( '%contentpage',$this->contentpage (),$output );
			$output = ereg_replace ( '%news.html',$this->shownews (),$output );
			$output = ereg_replace ( '%newsmessage.html',$this->shownewsmessage (),$output );
			$output = ereg_replace ( '%comment.html',$this->showcomments (),$output );
			$output = ereg_replace ( '%editcommentform.html',$this->editcommentform (),$output );
			$output = ereg_replace ( '%shortviewcurrentpoll.html',$this->shortviewcurrentpoll (),$output );
			$output = ereg_replace ( '%profile.html',$this->viewprofile (), $output);
			$output = ereg_replace ( '%helpcontent.html',$this->help (), $output);
		}
		$output = $this->replace_all ( $output );
		echo $output;
	}

/*----------------------------------NEW CODE----------------------------------*/

	private function convertFile ($file) {
		return 'themes/' . $this->themedir . '/' . $file;
	} /* private function convertFile ($file) */

	private function loadGroup ($string) {
		return explode (';',$string);
	}

	private function includes ($parser) {
		// Get all Elements with the name include
		while ($parser->getElement ('include') != NULL) {
			// set the includetag and parent
			$include = $parser->getElement ('include');
			$parent = $include->getParent ();
			// creates a new doc from the file to be included and parse for includes
			$incFile = new MiniXMLDoc ();
			$incFile->fromFile ($this->convertFile ($include->xattributes['href']));
			$this->includes ($incFile);
			// set the root of the new file in the index of include
			$parent->insertChild ($incFile->getRoot (),$include->getIndex ());
			// be sure to not create an endless loop
			$parent->removeChild ($include);
		}
	} /* private function includes ($parser) */

	private function loadSideBar ($parser,$skinFile) {
		$childsOfSideBar = $this->loadGroup ($this->childsOfSideBar);
		$page = $this->loadGroup ($this->pages[$skinFile]);
		// search the same parts in the 2 arrays
		$toShow = array_intersect ($childsOfSideBar,$page);
		foreach ($toShow as $test) {
			echo 'JA=' . $test;
		}
	}

	public function loadSkinFile ($skinFile,$loginReq = true) {
		include ('kernel/minixml/minixml.inc.php');
		if (file_exists ($this->convertFile ($skinFile))) {
			$parser = new MiniXMLDoc ();
			$parser->fromFile ($this->convertFile ($skinFile));
			$this->includes ($parser,$skinFile);
			$this->loadSideBar ($parser,$skinFile);
			echo $parser->toString ();
		} else {
			echo 'ERROR: ' . $this->convertFile ($skinFile);
			throw new exceptionlist ("Failed to open file",ERROR_THEME);
		}
	} /* public function loadSkinFile ($skinFile,$loginReq = true) */
} // layout
?>
