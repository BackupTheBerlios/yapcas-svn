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
			$config->addConfigByFileName ('site.config.php',TYPE_STRING,'database/tblprefix',0);
			define ('TBL_PREFIX',$config->getConfigByNameType ('database/tblprefix',TYPE_STRING));
			define ('TBL_PAGES',TBL_PREFIX . 'pages');
			include ('kernel/error.class.php');
			include ('kernel/users.class.php');
			include ('kernel/news.class.php');
			include ('kernel/polls.class.php');
			$config->addConfigByFileName ('site.config.php',TYPE_INT,'general/errorreporting',0);
			$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/webmastermail',0);
			error_reporting ($config->getConfigByNameType('general/errorreporting',TYPE_INT));
			$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/databasetype',0);
			$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/description',0);
			loaddbclass ($config->getConfigByNameType ('general/databasetype',TYPE_STRING));
			$database = new database ($config,'site.config.php');
			$database->connect ();
			// TODO
			$tables = array ();
			if (checkDatabase ($database,$tables)) {
				// Database seems to be OK
				$config->addConfigByFileName ('site.config.php',TYPE_BOOL,'user/activatemail',0);
				$user = new user ($database,$config->getConfigByNameType ('user/activatemail',TYPE_BOOL));
				$news = new news ($database,$user,$config);
				$poll = new polls ($database,$config);
				$config->addConfigByFileName ('site.config.php',TYPE_FLOAT,'general/servertimezone');
				$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/httplink');
				$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/sitename');
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('timezone',$user,'timezone','site.config.php'),
					'general/timezone',TYPE_FLOAT);
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('timeformat',$user,'timeformat','site.config.php'),
					'general/timeformat',TYPE_STRING);
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('language',$user,'language','site.config.php'),
					'general/language',TYPE_STRING);
				$lang = loadlang  ($config->getConfigByNameType('general/language',TYPE_STRING));
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('theme',$user,'theme','site.config.php'),
					'general/theme',TYPE_STRING);
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('threaded',$user,'threaded','site.config.php'),
					'news/threaded',TYPE_BOOL);
				$config->addConfigByList ('YAPCAS_USER',array($user),'user/email',TYPE_STRING);
				$config->addConfigByList ('YAPCAS_USER',array($user),'user/name',TYPE_STRING);
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

	/*function __construct () {
		error_reporting (E_ALL);
		define ('TBL_PREFIX','yapcas');
		define ('ERROR_LINK','./help.php#error');
		global $lang;
		if (isinstalled ()) {
			include ('config.class.php');
			include ('kernel/error.class.php');
			$this->database = new config ();
			$this->config->addConfigByFileName ('site.config.php',TYPE_INT,'general/errorreporting',0);
		//	error_reporting ($this->config->getConfigByNameType('general/errorreporting',TYPE_INT));
			$this->config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/sitename',0);
			$this->config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/description',0);
			$this->config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/databasetype',0);
			$this->config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/webmastermail',0);
			loaddbclass ($this->config->getConfigByNameType ('general/databasetype',TYPE_STRING));
			$this->database = new database ($this->config,'site.config.php');
			$this->database->connect ();
			if (!databasecheck($this->database)) {
				include ( 'kernel/users.constants.php' );
				global $theme;
				if ( empty ( $_GET[get_language] ) ) {
					$language = STANDARD_LANGUAGE;
				} else {
					$language = $_GET[get_language];
				}
				$GLOBALS['lang'] = loadlang ( $language );
				if ( empty ( $_GET[get_theme] ) ) {
					$theme = STANDARD_THEME;
				} else {
					$theme = $_GET[get_theme];
				}
				
				// Define some $this->user->get  varrs to avoid problems
				include ( 'kernel/basicuser.class.php' );
				$this->user = new basicuser ();
				$this->loadtheme ( $theme );
				include ( 'kernel/news.class.php' );
				$news = new news ( $this->database,$this->user,$this->config );
				$theme = $this;
				$this->themefile ( 'install.html',false,true );
				exit;
			} else {
				include ( 'kernel/users.class.php' );
				$this->config->addConfigByFileName ('site.config.php',TYPE_BOOL,'user/activatemail');
				$this->user = new user ( $this->database,
					$this->config->getConfigByNameType ('user/activatemail',TYPE_BOOL) );
				include ( 'kernel/news.class.php' );
				include ( 'kernel/polls.class.php' );
				$this->config->addConfigByFileName ('site.config.php',TYPE_FLOAT,'general/servertimezone');
				$this->config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/httplink');
				$this->config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('timezone',$this->user,'timezone','site.config.php'),
					'general/timezone',TYPE_FLOAT);
				$this->config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('timeformat',$this->user,'timeformat','site.config.php'),
					'general/timeformat',TYPE_STRING);
				$this->config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('language',$this->user,'language','site.config.php'),
					'general/language',TYPE_STRING);
				$GLOBALS['lang'] = loadlang 
					($this->config->getConfigByNameType('general/language',TYPE_STRING));
				$news = new news ($this->database,$this->user,$this->config);
				$this->config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('theme',$this->user,'theme','site.config.php'),
					'general/theme',TYPE_STRING);
				$this->config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('threaded',$this->user,'threaded','site.config.php'),
					'news/threaded',TYPE_BOOL);
				$this->config->addConfigByList ('YAPCAS_USER',array($this->user),'user/email',TYPE_STRING);
				$this->config->addConfigByList ('YAPCAS_USER',array($this->user),'user/name',TYPE_STRING);
				$this->loadtheme ($this->config->getConfigByNameType('general/theme',TYPE_STRING));
				$this->poll = new polls ($this->config);
				global $user,$this->database,$news,$theme,$this->config,$poll;
				$poll = $this->poll;
				$this->config = $this->config;
				$this->database = $this->database;
				$user = $this->user;
				$news = $news;
				$theme = $this;
				define ('TBL_PAGES', TBL_PREFIX . 'pages');
			}
		} else {
			if ( file_exists ( 'site.config.php' ) ) {
				echo 'COPY a theme, lang or database to the installation dir of YaPCaS';
			} else {
				include ( 'kernel/basicconfig.class.php' );
				$this->config = new basicconfig ();
				loaddbclass ( $this->config->database->type );
				$this->database = new database ( $this->config );
				$this->database->connect ();
				include ( 'kernel/users.constants.php' );
				global $theme;
				if ( empty ( $_GET[get_language] ) ) {
					$language = STANDARD_LANGUAGE;
				} else {
					$language = $_GET[get_language];
				}
				$GLOBALS['lang'] = loadlang ( $language );
				if ( empty ( $_GET[get_theme] ) ) {
					$theme = STANDARD_THEME;
				} else {
					$theme = $_GET[get_theme];
				}
				// Define some $this->user->get  varrs to avoid problems
				include ( 'kernel/basicuser.class.php' );
				$this->user = new basicuser ();
				$this->loadtheme ( $theme );
				include ( 'kernel/news.class.php' );
				$news = new news ( $this->database,$this->user );
				$theme = $this;
				$this->themefile ( 'install.html',false,true );
				exit;
			}
		}
	}*/
	
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
		include_once ('themes/' . $this->themedir . '/theme.php');
		if ($this->version_cms != version) {
			//$this->config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/theme',0);
			$this->themedir = 'moderngray';//$this->config->getConfigByNameType ('general/theme',TYPE_STRING);
			include_once ('themes/' . $this->themedir . '/theme.php');
		}
	}
	
	function title () {
		$sitename = $this->config->getConfigByNameType ('general/sitename',TYPE_STRING);
		$pagename = $_SERVER['PHP_SELF'];
		$pagename = ereg_replace ( '/','',$pagename ); 
		// removes '/' in begin of pagename
		$language = $this->config->getConfigByNameType ('general/language',TYPE_STRING);
		$sql = "SELECT * FROM " . TBL_PAGES ." WHERE name='$pagename' AND language='$language' LIMIT 1";
		$query = $this->database->query ( $sql );
		if ( errorSDK::is_error ( $query ) ) {
			$this->error ( $query );
		} else {
			if ( $this->database->num_rows ( $query ) == 0 ) {
				$title = $GLOBALS['lang']->site->untitled;
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
		$newsmessage = $this->news->showallnews ();
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
				$tempoutput = ereg_replace ( '%news.comments',$news['comments'] . '&nbsp;' . $GLOBALS['lang']->news->plural_comment,$tempoutput );
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
			if ( $offset['previous'] < 0 ) {
				$tempoutput = preg_replace ( '#%ifprev0(.+?)%/ifprev0#', '\\1', $tempoutput );
				// change prev0 in somthing usefull
				$tempoutput = preg_replace ( '#%ifprev>0(.+?)%/ifprev>0#', '', $tempoutput );
				// make prev>0 dissappear
			} else {
				$link = 'index.php';
				$tempoutput = preg_replace ( '#%ifprev>0(.+?)%/ifprev>0#', '\\1', $tempoutput );
				$tempoutput = preg_replace ( '#%prev.link#', $link, $tempoutput );
				// change prev>0 in somthing usefull
				$tempoutput = preg_replace ( '#%ifprev0(.+?)%/ifprev0#', '', $tempoutput );
				// make prev0 dissappear
			}
			if ( $offset['next'] > $offset['total'] - 1 ) {
				$tempoutput = preg_replace ( '#%ifnext0(.+?)%/ifnext0#', '\\1', $tempoutput );
				$tempoutput = preg_replace ( '#%ifnext>0(.+?)%/ifnext>0#', '', $tempoutput );
			} else {
				$link = 'index.php?category=' . $category . '&amp;offset=' . $offset['next'];
				$tempoutput = preg_replace ( '#%ifnext0(.+?)%/ifnext0#', '', $tempoutput );
				$tempoutput = preg_replace ( '#%next.link#', $link, $tempoutput );
				$tempoutput = preg_replace ( '#%ifnext>0(.+?)%/ifnext>0#', '\\1', $tempoutput );
			}
			
			$output .= $tempoutput;	
			
			if ( $this->user->loggedin () ) {
				$link = 'news.php?action=postnewsform';
				$tempoutput = $this->post_news_link;
				$tempoutput = preg_replace ( '#%postnews.link#', $link , $tempoutput );
				$tempoutput = preg_replace ( '#%postnews.lang#', $GLOBALS['lang']->news->post_a_news , $tempoutput );
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
		$sql = "SELECT * FROM pages WHERE name='$pagename' AND language='$language'";
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
		$output = ereg_replace ( '%databasetype.lang' ,$GLOBALS['lang']->database->database_type,$output );
		$output = ereg_replace ( '%databasehost.lang' ,$GLOBALS['lang']->database->database_host,$output );
		$output = ereg_replace ( '%databasename.lang' ,$GLOBALS['lang']->database->database_name,$output );
		$output = ereg_replace ( '%databasepassword.lang' ,$GLOBALS['lang']->database->database_password,$output );
		$output = ereg_replace ( '%installation.lang' ,$GLOBALS['lang']->site->installation,$output );
		$output = ereg_replace ( '%install.lang' ,$GLOBALS['lang']->site->install,$output );
		$output = ereg_replace ( '%installscript.method' ,'post',$output );
		$output = ereg_replace ( '%installscript.action' ,'install.php',$output );
		$output = ereg_replace ( '%database.options' ,$this->options ( databasesinstalled() ,$this->database_option,$this->config->getConfigByNameType('general/databasetype',TYPE_STRING)),$output );
		$output = ereg_replace ( '%copyright.site.text' ,COPYRIGHT,$output );
		$output = ereg_replace ( '%copyright.site.link' ,'about.php',$output );
		$output = ereg_replace ( '%copyright.theme.text' ,$this->copyright,$output );
		$output = ereg_replace ( '%copyright.theme.link' ,$this->themelink,$output );
		$output = ereg_replace ( '%navigation.lang' ,$GLOBALS['lang']->site->navigation,$output );
		$output = ereg_replace ( '%register.lang' ,$GLOBALS['lang']->users->register,$output );
		$output = ereg_replace ( '%register.error' ,$this->get ( get_registererror, 'error' ),$output );
		$output = ereg_replace ( '%email.formname' ,POST_EMAIL,$output );
		$output = ereg_replace ( '%password1.formname' ,POST_PASSWORD1,$output );
		$output = ereg_replace ( '%password2.formname' ,POST_PASSWORD2,$output );
		$output = ereg_replace ( '%user.formname' ,POST_NAME,$output );
		$output = ereg_replace ( '%databaseuser.lang',$GLOBALS['lang']->database->database_user,$output );
		$output = ereg_replace ( '%sendpassword.lang' ,$GLOBALS['lang']->users->lost_password_question,$output );
		$output = ereg_replace ( '%sendpassword.error' ,$this->get ( get_sendpassworderror, 'error' ),$output );
		$output = ereg_replace ( '%or.lang' ,$GLOBALS['lang']->users->or,$output );
		$output = ereg_replace ( '%sendpassword.action' ,sendpassword_action,$output );
		$output = ereg_replace ( '%sendpassword.method' ,form_method,$output );
		$output = ereg_replace ( '%username.lang' ,$GLOBALS['lang']->users->username,$output );
		$output = ereg_replace ( '%password1.lang' ,$GLOBALS['lang']->users->password,$output );
		$output = ereg_replace ( '%password2.lang' ,$GLOBALS['lang']->users->password_repeat,$output );
		$output = ereg_replace ( '%email.lang' ,$GLOBALS['lang']->users->email,$output );
		$output = ereg_replace ( '%registerform.action' ,registerform_action,$output );
		$output = ereg_replace ( '%registerform.method' ,form_method,$output );
		$output = ereg_replace ( '%postnews.method' ,form_method,$output );
		$output = ereg_replace ( '%changeoptions.method' ,form_method,$output );
		$output = ereg_replace ( '%postnews.action' ,'news.php?action=postnews',$output );
		$output = ereg_replace ( '%postcomment.method' ,form_method,$output );
		$output = ereg_replace ( '%postcomment.action' ,'news.php?action=postcomment',$output );
		$output = ereg_replace ( '%on_news.value' ,$this->get ( 'id_news','empty' ),$output );
		$output = ereg_replace ( '%on_comment.value' ,$this->on_comment (),$output );
		$output = ereg_replace ( '%postnews.lang' ,$GLOBALS['lang']->news->post_a_news,$output );
		$output = ereg_replace ( '%subject.lang' ,$GLOBALS['lang']->news->subject,$output );
		$output = ereg_replace ( '%newsmessage.lang' ,$GLOBALS['lang']->news->message,$output );
		$output = ereg_replace ( '%category.lang' ,$GLOBALS['lang']->news->category,$output );
		$output = ereg_replace ( '%post.lang' ,$GLOBALS['lang']->news->post,$output );
		$output = ereg_replace ( '%logout.lang' ,$GLOBALS['lang']->users->logout,$output );
		$output = ereg_replace ( '%logout.link' ,'users.php?action=logout',$output );
		$output = ereg_replace ( '%timezone.lang' ,$GLOBALS['lang']->users->timezone,$output );
		$output = ereg_replace ( '%timeformat.lang' ,$GLOBALS['lang']->users->timeformat,$output );
		$output = ereg_replace ( '%valueheadlines.lang' ,$GLOBALS['lang']->users->valueheadlines,$output );
		$output = ereg_replace ( '%headlines.lang' ,$GLOBALS['lang']->news->headlines,$output );
		$output = ereg_replace ( '%comments_on_news.lang' ,$GLOBALS['lang']->news->comments_on_news,$output );
		$output = ereg_replace ( '%postsonpage.lang' ,$GLOBALS['lang']->users->postsonpage,$output );
		$output = ereg_replace ( '%threaded.lang' ,$GLOBALS['lang']->users->threaded,$output );
		$output = ereg_replace ( '%theme.lang' ,$GLOBALS['lang']->users->theme,$output );
		$output = ereg_replace ( '%language.lang' ,$GLOBALS['lang']->users->language,$output );
		$output = ereg_replace ( '%postcomment.lang' ,$GLOBALS['lang']->news->post_a_comment,$output );
		$output = ereg_replace ( '%new_password1.lang' ,$GLOBALS['lang']->users->new_password,$output );
		$output = ereg_replace ( '%new_password2.lang' ,$GLOBALS['lang']->users->new_password_repeat,$output );
		$output = ereg_replace ( '%poll.action' ,POLLS_VOTE_ACTION,$output );
		$output = ereg_replace ( '%poll.method' ,'post',$output );
		
		$timezone = $this->config->getConfigByNameType('general/timezone',TYPE_INT);
		$timeformat = $this->config->getConfigByNameType('general/timeformat',TYPE_STRING);
		$headlines = $this->config->getConfigByNameType('news/headlines',TYPE_INT);
		$postsonpage = $this->config->getConfigByNameType('news/postsonpage',TYPE_INT);
		$threaded =$this->config->getConfigByNameType('news/threaded',TYPE_BOOL);
		$theme = $this->config->getConfigByNameType('general/theme',TYPE_STRING);
		$email =$this->config->getConfigByNameType('user/email',TYPE_STRING);
		$name =  $this->config->getConfigByNameType('user/name',TYPE_STRING);
		$language = $this->config->getConfigByNameType('general/language',TYPE_STRING);
		$pagename = $_SERVER['PHP_SELF'];
		$pagename = ereg_replace ( '/','',$pagename ); 
		// removes '/' in begin of pagename
		if ( ( $pagename == 'news.php' ) AND ( ! empty ( $_GET['action'] ) ) ) {
			if ( $_GET['action'] == 'viewcomments' ) {
				if ($this->config->getConfigByNameType ('news/threaded',TYPE_BOOL) == false) {
					$output = ereg_replace ( '%commentnavigator.html',$this->commentnavigator (),$output );
				} else {
					$output = ereg_replace ( '%commentnavigator.html','',$output );
				}
			}
		}
		$output = ereg_replace ( '%timezone.timezone' ,$timezone,$output );
		$output = ereg_replace ( '%timeformat.timeformat' ,$timeformat,$output );
		$output = ereg_replace ( '%headlines.headlines' ,$headlines,$output );
		$output = ereg_replace ( '%postsonpage.postsonpage' ,$postsonpage,$output );
		$output = ereg_replace ( '%email.email' ,$email,$output );
		$output = ereg_replace ( '%username.user' ,$name,$output );
	
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
		$output = ereg_replace ( '%changeoptionsform.lang',$GLOBALS['lang']->users->change_options,$output );
		$output = ereg_replace ( '%changeoptionsform.submit',$GLOBALS['lang']->users->change_options_submit,$output );
		$output = ereg_replace ( '%changeoptionsform.action',changeoptions_action,$output );
		$output = ereg_replace ( '%changeoptionsform.action',changeoptions_action,$output );
		//$output = ereg_replace ( '%showlinksallpolls',$this->showallpolls ($GLOBALS['poll']->getallpollsbylanguage ($this->config->getConfigByNameType('general/language',TYPE_STRING)),$output);
		$output = ereg_replace ( '%showselectedpoll',$this->showpoll (),$output );
		$output = ereg_replace ( '%user.links',$this->loaduserlinks (),$output );
		$output = ereg_replace ( '%viewusers.list',$this->viewuserlist (),$output );
		$output = $this->replaceimages ( $output );
		return $output;
	}
	
	function loadloginform () {
		//echo $this->user->loggedin ();
		$output = $this->getfile ( 'themes/' . $this->themedir . '/loginform.html' );
		if ( ! $this->user->loggedin () ) {
			$output = preg_replace ( '#\n#','',$output );
			//remove all newlines ( to avoid problmes with next line )
			$output = preg_replace ( '#\{loggedin\}(.+?)\{/loggedin}#','\\1',$output );
			$output = preg_replace ( '#\{notloggedin\}(.+?)\{/notloggedin\}#','',$output );
			$output = ereg_replace ( '%login.error' ,$this->get ( get_loginerror, 'error' ),$output );
			$output = ereg_replace ( '%login.action', loginform_action, $output );
			$output = ereg_replace ( '%login.method', form_method, $output );
			$output = ereg_replace ( '%username.lang', $GLOBALS['lang']->users->username, $output );
			$output = ereg_replace ( '%username.formname', POST_NAME, $output );
			$output = ereg_replace ( '%password.lang', $GLOBALS['lang']->users->password, $output );
			$output = ereg_replace ( '%password.formname', POST_PASSWORD, $output );
			$output = ereg_replace ( '%login.lang', $GLOBALS['lang']->users->login, $output );
			$output = ereg_replace ( '%toregisterform_action', toregisterform_action, $output );
			$output = ereg_replace ( '%yetregisterd.lang', $GLOBALS['lang']->users->yet_registered_question, $output );
			if ( ! empty ( $_GET[get_error] ) ) {
				$output = ereg_replace ( '%getusernameorpassword.lang', $GLOBALS['lang']->users->forget_password_or_username, $output );
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
			$output = ereg_replace ( '%users.lang',$GLOBALS['lang']->users->user,$output );
			$output = ereg_replace ( '%logout.lang',$GLOBALS['lang']->users->logout,$output );
			$output = ereg_replace ( '%changeoptionsform.lang',$GLOBALS['lang']->users->changeoptions,$output );
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
		$output = ereg_replace ( '%comment.newcomment',$GLOBALS['lang']->news->give_a_comment,$output );
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
		// FIXME: $offset
		$allcomments = $this->news->getallcomments ($_GET[GET_NEWSID],0);
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
				$output = ereg_replace ( '%news.comments',$news['comments'] . '&nbsp;' . $GLOBALS['lang']->news->plural_comment,$output );
				$output = ereg_replace ( '%news.commentlink',$link,$output );
				$output = ereg_replace ( '%news.newcomment',$GLOBALS['lang']->news->give_a_comment,$output );
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
		$headlines = $this->news->headlines ( 'show' );
		
		if ( errorSDK::is_error ( $headlines ) ) {
			$this->error ( $headlines );
		} else {
			$output = NULL;
			foreach ( $headlines as $headline ) {
				$tempoutput = ereg_replace ( '%headline.title',$headline['subject'],$fileoutput );
				$tempoutput = ereg_replace ( '%headline.message', substr ( $headline['message'],0,60) . '...',$tempoutput );
				$tempoutput = ereg_replace ( '%headline.user',$headline['author'],$tempoutput );
				$tempoutput = ereg_replace ( '%headline.date',setdate ( $headline['date'] ),$tempoutput );
				$tempoutput = ereg_replace ( '%headline.link','news.php?action=viewcomments&id=' . $headline['id'],$tempoutput );
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
	
	function helpindex () {
		$output = NULL;
		foreach (errorSDK::getHelpIndex ($this->database) as $title) {
			$output .= $title;
		}
		return $output;
	}
	
	function helpcontent () {
		$output = NULL;
		foreach (errorSDK::getHelpIndex ($this->database) as $index) {
			$question = errorSDK::getQuestionsByIndex ($this->database,$index);
			if (! errorSDK::is_error ($question)) {
				$tmpoutput = $this->helpqa;
				$tmpoutput = ereg_replace ('%question',$question[FIELD_HELPQUESTION_QUESTION],$tmpoutput);
				$tmpoutput = ereg_replace ('%answer',$question[FIELD_HELPQUESTION_ANSWER],$tmpoutput);
				$output .= $tmpoutput;  
			} else {
				$output .= $this->error ($question);
			}
		}
		return $output;
	}
	
	function help () {
		$output = $this->getfile ('themes/' . $this->themedir . '/helpcontent.html');
		$output = ereg_replace ('%helpindex.main',$this->helpindex (),$output);
		$output = ereg_replace ('%helpcontent.main',$this->helpcontent (),$output);
		return $output;
	}
	
	function themefile ( $file,$mustlogin = false,$basic = false) {
		if ( ( $mustlogin == true ) AND ( $this->user->loggedin () != true ) ) {
			$this->redirect ( 'index.php?warning=' . $GLOBALS['lang']->users->must_login );
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
} // layout
?>
