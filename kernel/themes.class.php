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
	class theme {
		var $config;
		var $news;
		var $database;
		var $user;
		
		function theme () {
			define ('TBL_PREFIX','');
			global $lang;
			error_reporting ( E_ALL );
			if ( isinstalled () ) {
				include ( 'config.class.php' );
				include ( 'kernel/error.class.php' );
				$this->config = new config ();
				loaddbclass ( $this->config->database->type );
				$this->database = new database ( $this->config );
				$this->database->connect ();
				if ( ! databasecheck ( $this->database ) ) {
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
					$this->news = new news ( $this->database,$this->user );
					$theme = $this;
					$this->themefile ( 'install.html',false,true );
					exit;
				} else {
					include ( 'kernel/users.class.php' );
					$this->user = new user ( $this->database,$this->config );
					include ( 'kernel/news.class.php' );
					include ( 'kernel/polls.class.php' );
					$GLOBALS['lang'] = loadlang ( $this->user->getlanguage () );
					$this->news = new news ( $this->database,$this->user );
					$this->loadtheme ( $this->user->gettheme () );
					$this->poll = new polls ();
					global $user,$database,$news,$theme,$config,$poll;
					$poll = $this->poll;
					$config = $this->config;
					$database = $this->database;
					$user = $this->user;
					$news = $this->news;
					$theme = $this;	
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
					$this->news = new news ( $this->database,$this->user );
					$theme = $this;
					$this->themefile ( 'install.html',false,true );
					exit;
				}
			}
		}
		
		function db_error ( $error,$fatal = false ) {
			if ( $fatal == true ) {
				echo '<div><h2>Database fout</h2><p>' . $error . '</p></div>';
			} else {
			}
		}
		
		function error ( $error ) {
			return $error->error;
		}

		function loadtheme ( $themedir ) {
			$this->themedir = $themedir;
			include_once ( 'themes/' . $this->themedir . '/theme.php' );
			if ( $this->version_cms != version ) {
				$this->themedir = $this->config->site->theme;
				include_once ( 'themes/' . $this->themedir . '/theme.php' );
			}
		}
		
		function title () {
			$sitename = $this->config->site->name;
			$pagename = $_SERVER['PHP_SELF'];
			$pagename = ereg_replace ( '/','',$pagename ); 
			// removes '/' in begin of pagename
			$language = $this->user->getlanguage ();
			$sql = "SELECT * FROM pages WHERE name='$pagename' AND language='$language' LIMIT 1";
			$query = $this->database->query ( $sql );
			if ( errorSDK::is_error ( $query ) ) {
				$this->error ( $query );
			} else {
				if ( $GLOBALS['database']->num_rows ( $query ) == 0 ) {
					$title = $GLOBALS['lang']->site->untitled;
				} else {
					$pagetitle = $GLOBALS['database']->fetch_array ( $query );
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
				$offset = $this->news->getlimit ('allnews');
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
			$language = $this->user->getlanguage ();
			$sql = "SELECT name,shown_name FROM pages WHERE language='$language' AND show_in_nav='Y' AND show_in_user_nav='N'";
			$query = $GLOBALS['database']->query ( $sql );
			$retlink = NULL;
			if ( errorSDK::is_error ( $query ) ) {
				$this->error ( $query );
			} else {
				while ( $link = $this->database->fetch_array ( $query ) ) {
					$temp = $links;
					$temp = ereg_replace ( '%link.name' ,$link['name'] ,$temp );
					$temp = ereg_replace ( '%link.shown_name' ,$link['shown_name'] ,$temp );
					$retlink .= $temp;
				}
			}

			return $retlink;
		}
		
		function loaduserlinks () {
			$userlinks = $this->userlink;
			$language = $this->user->getlanguage ();
			$sql = "SELECT name,shown_name FROM pages WHERE language='$language' AND show_in_nav='Y' AND show_in_user_nav='Y'";
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
			$language = $this->user->getlanguage ();
			$sql = "SELECT * FROM pages WHERE name='$pagename' AND language='$language'";
			$query = $this->database->query ( $sql );
			if ( errorSDK::is_error ( $query ) ) {
				$this->error ( $query );
			} else {
				if ( $this->database->countresults ( $query ) == 1 ) {
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
						return ereg_replace ( '%warning', $_GET[ $what ], $this->warningmessage );
						break;
					case 'db_warning':
						return ereg_replace ( '%warning', $_GET[ $what ], $this->warningmessage );
						break;
					case 'error':
						return ereg_replace ( '%error', $_GET[ $what ], $this->errormessage );
						break;
					case 'db_error':
						return ereg_replace ( '%db_error', $_GET[ $what ], $this->db_errormessage );
						break;
					case 'note':
						return ereg_replace ( '%note', $_GET[ $what ], $this->notemessage );
						break;
					case 'empty':
						return $_GET[ $what ];
				}
			} else {
				return NULL;
			}
		}
		
		function categories () {
			$sql = "SELECT * FROM " . TBL_CATEGORIES . " WHERE ". FIELD_CATEGORIES_LANGUAGE ."='" . $this->user->getlanguage () ."'";
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
			if ( empty ( $_GET['poll'] ) ) {
				$id = $GLOBALS['poll']->getidcurrentpollbylanguage ( $GLOBALS['user']->getlanguage () );
			} else {
				$id = $_GET['poll'];
			}
			
			$poll = $GLOBALS['poll']->getpollbyid ( $id );
			if ( errorSDK::is_error ( $poll ) ) {
				$this->error ( $poll );
				return NULL;
			} else {
				$output = $this->getfile ( 'themes/' . $this->themedir . '/viewpollresults.html' );
				$output = ereg_replace ( '%poll.question',$poll[FIELD_POLL_QUESTION],$output );
				$output = ereg_replace ( '%poll.result',$this->pollresults ( explode ( ';',$poll[FIELD_POLL_CHOICES] ),explode ( ';',$poll[FIELD_POLL_RESULTS] ) ),$output );
				return $output;
			}
		}
		
		function viewuserlist () {
			$sql = "SELECT " . FIELD_USERS_NAME . " FROM " . TBL_USERS . " WHERE " . FIELD_USERS_PUBLIC_USER . "='Y' ORDER by " . FIELD_USERS_NAME . " asc ";
			$query = $GLOBALS['database']->query ( $sql );
			if ( ! errorSDK::is_error ( $query ) ) {
				$output = NULL;
				while ( $user = $GLOBALS['database']->fetch_array ( $query ) ) {
					$output .= $this->touserinfolink;
					$link = 'users.php?action=viewuser&user=' . $user[FIELD_USERS_NAME];
					$output = ereg_replace ( '%user.name',$user[FIELD_USERS_NAME],$output );
					$output = ereg_replace ( '%user.url',$link,$output );
				}
				return $output;
			} else {
				return $this->error ( $query );
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
			$output = ereg_replace ( '%database.options' ,$this->options ( databasesinstalled() ,$this->database_option,$this->config->database->type ),$output );
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
			
			$timezone = $this->user->gettimezone ();
			$timeformat = $this->user->gettimeformat ();
			$headlines = $this->user->getheadlines ();
			$postsonpage = $this->user->getpostsonpage ();	
			$threaded = $this->user->getthreaded ();
			$theme = $this->user->gettheme ();
			$email = $this->user->getemail ();
			$name =  $this->user->getname ();
			$language = $this->user->getlanguage ();
			$pagename = $_SERVER['PHP_SELF'];
			$pagename = ereg_replace ( '/','',$pagename ); 
			// removes '/' in begin of pagename
			if ( ( $pagename == 'news.php' ) AND ( ! empty ( $_GET['action'] ) ) ) {
				if ( $_GET['action'] == 'viewcomments' ) {
					$output = ereg_replace ( '%commentnavigator.html',$this->commentnavigator (),$output );
				}
			}
			$output = ereg_replace ( '%timezone.timezone' ,$timezone,$output );
			$output = ereg_replace ( '%timeformat.timeformat' ,$timeformat,$output );
			$output = ereg_replace ( '%headlines.headlines' ,$headlines,$output );
			$output = ereg_replace ( '%postsonpage.postsonpage' ,$postsonpage,$output );
			$output = ereg_replace ( '%email.email' ,$email,$output );
			$output = ereg_replace ( '%username.user' ,$name,$output );
		
			$output = ereg_replace ( '%language.options' ,$this->options ( languagesinstalled(),$this->language_option,$language ) ,$output );
			$output = ereg_replace ( '%threaded.options' ,$this->options ( array ( 'N' => $GLOBALS['lang']->site->no, 'Y' => $GLOBALS['lang']->site->yes ),$this->threaded_option,$this->user->convert_db2config ( CONFIG_THREADED,$threaded ) ),$output );
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
			$output = ereg_replace ( '%showlinksallpolls',$this->showallpolls ( $GLOBALS['poll']->getallpollsbylanguage ( $GLOBALS['user']->getlanguage () ) ),$output );
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
			if ( $comment['author']  == $this->user->getname () ) {
				$output = ereg_replace ( '%edit.button',$this->newstheme->editbutton,$output );
				$output = ereg_replace ( '%link','news.php?action=editcommentform&amp;id=' . $comment['id'],$output );
			} else {
				$output = ereg_replace ( '%edit.button','',$output );
			}
			return $output;
		}
		
		function startthread ( $threadstart ) {
			$output = $this->replacecomment ( $threadstart );
			foreach ( $this->news->getthreadfollows ( $threadstart ) as $threadfollow ) {
				$output .= $this->openthread;
				$output .= $this->startthread ( $threadfollow );
				$output .= $this->closethread;
			}
			return $output;
		}
		
		function startflatthread () {
			$output = NULL;
			$allcomments = $this->news->getallcomments ();
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
			$offset = $this->news->getlimit ( 'comments' );
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
			if ( empty ( $_GET['id'] ) ) {
				return NULL;
			} else {
				$output = NULL;
				if ( $this->user->getthreaded () == YES ) {
					foreach ( $this->news->startthreads () as $threadstart ) {
						$output .= $this->startthread ( $threadstart );
					}
				} else {
					$output .= $this->startflatthread ();
				}
				return $output;
			}
		}
		
		function replaceubb ( $input ) {
			$output = preg_replace ( '#\\[quote\\](.+?)\\[/quote\\]#',ereg_replace ( '%text','\\1',$this->quote ),$input );
			$output = preg_replace ( '#\\[b\\](.+?)\\[/b\\]#',ereg_replace ( '%text','\\1',$this->b ),$output );
			$output = preg_replace ( '#\\[i\\](.+?)\\[/i\\]#',ereg_replace ( '%text','\\1',$this->i ),$output );
			$output = preg_replace ( '#\\[u\\](.+?)\\[/u\\]#',ereg_replace ( '%text','\\1',$this->u ),$output );
			return $output;
		}
		
		function replacerichtext ( $input,$useubb = true ) {
			$output = strip_tags ( $input );
			$output = nl2br ( $output );
			$output = $this->replacesmilies ( $output );
			if ( $useubb == true ) {
				$output = $this->replaceubb ( $output );
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
			$poll = $GLOBALS['poll']->getpollbyid ( $GLOBALS['poll']->getidcurrentpollbylanguage ( $this->user->getlanguage () ) );
			if ( ! errorSDK::is_error ( $poll ) ) {
				$output = $this->getfile ( 'themes/' . $this->themedir . '/shortviewpoll.html' );
				$output = ereg_replace ( '%poll.question',$poll['question'],$output);
				$output = preg_replace ( '#\n#','',$output );
				//remove all newlines ( to avoid problmes with next lines )
				$voted = $GLOBALS['poll']->userhasvoted ();
				if ( errorSDK::is_error ( $voted ) ) {
					$this->error ( $voted );
					$voted = false;
				}
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
				$output = $this->error ( $poll );
			}
			return $output;
		}
		
		function viewprofile () {
			if ( isset ( $_GET['user'] ) ) {
				$profile = $GLOBALS['user']->getotherprofile ( $_GET['user'] );
				if ( ! errorSDK::is_error ( $profile ) ) {
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
					return $this->error ( $profile );
				}
			} else {
				$error = new errorSDK ();
				$error->succeed = false;
				$error->error = $GLOBALS['lang']->users->form_not_filled_in;
				return $this->error ( $error );
			}
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
			}
			$output = $this->replace_all ( $output );
			echo $output;
		}
	} // layout
?>
