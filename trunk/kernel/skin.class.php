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

class CSkin {
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
	}

	private function loadTheme ($themedir) {
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

	public function file ($fileName) {
			if (is_readable ($fileName)) {
				return file_get_contents ($fileName);
			} else {
				throw new exceptionlist ('Failed to open file: ' . $fileName,ERROR_THEME);
			}
	}

	private function convertFile ($file = NULL) {
		return 'themes/' . $this->themedir . '/' . $file;
	} /* private function convertFile ($file) */

	private function loadGroup ($string) {
		return explode (',',$string);
	}

	private function loadSideBar () {
		$childsOfSideBar = $this->loadGroup ($this->childsOfSideBar);
		$onThisPage = $this->loadGroup ($this->pages[$this->getPageID ()]);
		// implode char is empty because between the parts must be nothing
		$intersect = implode ('',array_intersect ($childsOfSideBar,$onThisPage));
		$this->fileCont = preg_replace ('#{sidebar}#',$intersect,$this->fileCont);
	}

	private function loadNavigationBar () {
		$childsOfNavigation = $this->loadGroup ($this->childsOfNavigation);
		$onThisPage = $this->loadGroup ($this->pages[$this->getPageID ()]);
		// implode char is empty because between the parts must be nothing
		$intersect = implode ('',array_intersect ($childsOfNavigation,$onThisPage));
		$this->fileCont = preg_replace ('#{navigation}#',$intersect,$this->fileCont);
	}

	private function showNewsItem ($item,$full = false) {
		if ($full) {
			$output = $this->items['news.fullitem'];
		} else {
			$output = $this->items['news.item'];
		}
		$this->includes ($output);
		preg_match_all ('#{news (.+?)}#',$output,$matches);
		foreach ($matches[0] as $number => $match) {
			$what = $matches[1][$number];
			switch ($what) {
				case 'subject':
					$tmp = $item[FIELD_NEWS_SUBJECT];
					break;
				case 'author':
					$tmp = $item[FIELD_NEWS_AUTHOR];
					break;
				case 'date':
					$tmp = showDate ($item[FIELD_NEWS_DATE]);
					break;
				case 'message':
					$tmp = $item[FIELD_NEWS_MESSAGE];
					break;
				case 'linkreadcomments':
					$tmp = 'news.php?action=viewcomments&id=' . $item[FIELD_NEWS_ID] . '#readcomments';
					break;
				case 'linknewcomment':
					$tmp = 'news.php?action=postcommentform&on_news_id=' . $item[FIELD_NEWS_ID];
					break;
				default:
					$tmp = NULL;
			}
			$output = ereg_replace ($match,$tmp,$output);
		}
		return $output;
	}

	private function getNewsItems () {
		$newsitems = $this->news->getAllNews ($this->get ('offset'),$this->get ('category'));
		$news = NULL;
		$output = $this->items['news.index'];
		foreach ($newsitems as $item) {
			$news .= $this->showNewsItem ($item);
		}
		$output = str_replace ('{newsitems}',$news,$output);
		return $output;
	}

	private function showHeadline ($item) {
		$output = $this->items['news.headline'];
		$this->includes ($output);
		preg_match_all ('#{headline (.+?)}#',$output,$matches);
		foreach ($matches[0] as $number => $match) {
			$what = $matches[1][$number];
			switch ($what) {
				case 'subject':
					$tmp = $item[FIELD_NEWS_SUBJECT];
					break;
				case 'author':
					$tmp = $item[FIELD_NEWS_AUTHOR];
					break;
				case 'date':
					$tmp = showDate ($item[FIELD_NEWS_DATE]);
					break;
				case 'message':
					$tmp = $item[FIELD_NEWS_MESSAGE];
					break;
				case 'link':
					$tmp = 'news.php?action=viewcomments&id=' . $item[FIELD_NEWS_ID];
					break;
				default:
					$tmp = NULL;
			}
			$output = ereg_replace ($match,$tmp,$output);
		}
		return $output;
	}

	private function getHeadlines () {
		$output = $this->items['news.headlines'];
		$this->includes ($output);
		preg_match ('/{headlines}/si',$output,$match);
		if ($match) {
			$newsitems = $this->news->getHeadlines ();
			$outputitems = NULL;
			foreach ($newsitems as $item) {
				$outputitems .= $this->showHeadline ($item);
			}
			$output = str_replace ($match,$outputitems,$output);
		}
		return $output;
	}

	private function showMessages ($what) {
		$output = NULL;
		switch ($what) {
			case 'errors':
				if ($this->get ('error') !== false) {
					$output = $this->items['message.error'];
					$output = ereg_replace ('{message error}',$this->get ('error'),$output);
					$output = ereg_replace ('{message link}',$this->get ('errorid'),$output);
				}
				break;
			case 'warnings':
				if ($this->get ('warning') !== false) {
					$output = $this->items['message.warning'];
					$output = ereg_replace ('{message warning}',$this->get ('warning'),$output);
					$output = ereg_replace ('{message link}',$this->get ('warningid'),$output);
				}
				break;
			case 'notes':
				if ($this->get ('note') !== false) {
					$output = $this->items['message.note'];
					$output = ereg_replace ('{message note}',$this->get ('note'),$output);
					$output = ereg_replace ('{message link}',$this->get ('noteid'),$output);
				}
				break;
			default:
				throw exceptionlist ('Uknown message type');
		}
		return $output;
	}

	private function loadNavigation ($usernav = false) {
		$lang = $this->config->getConfigByNameType ('general/language',TYPE_STRING);
		$sql = 'SELECT ' . FIELD_PAGES_SHOWN_NAME . ',' . FIELD_PAGES_LINK;
		$sql .= ' FROM '  . TBL_PAGES;
		$sql .= ' WHERE ' . FIELD_PAGES_IN_NAVIGATION . '=\'' . YES . '\'';
		if ($usernav != true) {
			$sql .= ' AND ' . FIELD_PAGES_IN_USER_NAVIGATION . '=\'' . NO . '\'';
		} else {
			$sql .= ' AND ' . FIELD_PAGES_IN_USER_NAVIGATION . '=\'' . YES . '\'';
		}
		$sql .= ' AND ' . FIELD_PAGES_LANGUAGE . '=\'' . $lang . '\'';
		$query = $this->database->query ($sql);
		$output = NULL;
		while ($item = $this->database->fetch_array ($query)) {
			$output .= $this->items['navigation.item'];
			$output = ereg_replace ('{navigation link}',$item[FIELD_PAGES_LINK],$output);
			$output = ereg_replace ('{navigation name}',$item[FIELD_PAGES_SHOWN_NAME],$output);
		}
		return $output;
	}

	private function loadPoll () {
		$poll = $this->poll->getPollByID ($this->poll->getIDCurrentPollByLanguage ('dutch'));
		preg_match_all ('/{poll (.+?)}/is',$this->fileCont,$matches);
		foreach ($matches[0] as $number => $match) {
			switch ($matches[1][$number]) {
				case 'question':
					$this->fileCont = str_replace ($match,$poll[FIELD_POLL_QUESTION],$this->fileCont);
					break;
				case 'choices':
					$choices = explode (';',$poll[FIELD_POLL_CHOICES]);
					$output = NULL;
					foreach ($choices as $number => $choice) {
						$output .= $this->items['poll.choice'];
						$output = str_replace ('{choice text}',$choice,$output);
						// stupid thing with integers
						$output = str_replace ('{choice number}',"$number",$output);
					}
					$this->fileCont = str_replace ($match,$output,$this->fileCont);
					break;
				case 'voteaction':
					$this->fileCont = str_replace ($match,
						'polls.php?action=vote&id=' . $poll[FIELD_POLL_ID],
						$this->fileCont);
					break;
				case 'votemethod':
					$this->fileCont = str_replace ($match,'post',$this->fileCont);
					break;
				case 'results':
					$choices = explode (';',$poll[FIELD_POLL_CHOICES]);
					$results = explode (';',$poll[FIELD_POLL_RESULTS]);
					$output = NULL;
					foreach ($choices as $number => $choice) {
						$output .= $this->items['poll.result'];
						$output = str_replace ('{choice text}',$choice,$output);
						$output = str_replace ('{choice result}',$results[$number],$output);
						$totalvotes = array_sum ($results);
						$procent = round ($results[$number] / $totalvotes * 100);
						$output = str_replace ('{choice resultprocent}',$procent,$output);
					}
					$this->fileCont = str_replace ($match,$output,$this->fileCont);
					break;
			}
		}
	}

	private function showComment ($comment) {
		$output = $this->items['newscomment.item'];
		$this->includes ($output);
		preg_match_all ('/{comment (.+?)}/is',$output,$matches);
		foreach ($matches[0] as $number => $match) {
			$replace = NULL;
			switch ($matches[1][$number]) {
				case 'subject':
					$replace = $comment[FIELD_COMMENTS_SUBJECT];
					break;
				case 'author':
					$replace = $comment[FIELD_COMMENTS_AUTHOR];
					break;
				case 'date':
					$replace = formatDate ($comment[FIELD_COMMENTS_DATE]);
					break;
				case 'newcommentlink':
					$replace = 'news.php?action=postcommentform';
					$replace .= '&amp;' . POST_ID_NEWS . '=' . $comment[FIELD_COMMENTS_ID_NEWS];
					$replace .= '&amp;' . POST_ID_COMMENT . '=' . $comment[FIELD_COMMENTS_ID];
					break;
				case 'message':
					$replace = $comment[FIELD_COMMENTS_MESSAGE];
					break;
			}
			$output = str_replace ($match,$replace,$output);
		}
		return $output;
	}

	private function startCommentThread ($parent) {
		//$parent = $this->news->getComment ($parentID);
		$output = $this->showComment ($parent);
		foreach ($this->news->getThreadChildren ($this->get (GET_NEWSID),$parent) as $child) {
			$output .= $this->items['thread.open'];
			$output .= $this->startCommentThread ($child);
			$output .= $this->items['thread.close'];
		}
		return $output;
	}

	private function showCommentsFlat ($newsID) {
		$output = NULL;
		$comments = $this->news->getAllComments ($newsID,$this->get ('offset'));
		foreach ($comments as $comment) {
			$output .= $this->showComment ($comment);
		}
		return $output;
	}

	private function showComments () {
		if ($this->config->getConfigByNameType ('news/threaded',TYPE_BOOL) == YES) {
			$output = $this->items['comments.threaded'];
			$this->includes ($output);
			$comments = NULL;
			foreach ($this->news->startthreads ($this->get (GET_NEWSID)) as $parent) {
				$comments .= $this->startCommentThread ($parent);
			}
			$output = str_replace ('{comments}',$comments,$output);
		} else {
			$output = $this->items['comments.nonthreaded'];
			$this->includes ($output);
			$comments = $this->showCommentsFlat ($this->get (GET_NEWSID));
			$output = str_replace ('{comments}',$comments,$output);
		}
		return $output;
	}

	private function showNewsCategories () {
		$output = NULL;
		$language = $this->config->getConfigByNameType ('general/language',TYPE_STRING);
		$categories = $this->news->getAllCategoriesByLanguage ($language);
		foreach ($categories as $category) {
			$output .= $this->items['news.categoryoption'];
			$output = str_replace ('&category.name;',$category[FIELD_CATEGORIES_NAME],$output);
		}
		return $output;
	}

	private function showCommentNavigator () {
		$output = NULL;
		if (! $this->config->getConfigByNameType ('news/threaded',TYPE_BOOL)) {
			$output .= $this->items['comments.navigator'];
			$this->includes ($output);
		}
		return $output;
	}

	private function loadItems () {
		$this->items['site.title'] =
			$this->config->getConfigByNameType ('general/sitename',TYPE_STRING);
		$this->items['site.description'] =
			$this->config->getConfigByNameType ('general/description',TYPE_STRING);
		$this->items['page.title'] = $this->getPageTitle ();
		$this->items['page.content'] = $this->getPageContent ();
		$this->items['news.items'] = $this->getNewsItems ();
		$this->items['news.headlines'] = $this->getHeadlines ();
		$this->items['page.errors'] = $this->showMessages ('errors');
		$this->items['page.warnings'] = $this->showMessages ('warnings');
		$this->items['page.notes'] = $this->showMessages ('notes');
		$this->items['site.navigation'] = $this->loadNavigation ();
		$this->items['user.navigation'] = $this->loadNavigation (true);
		$this->items['userlogin.method'] = 'post';
		$this->items['userlogin.action'] = './users.php?action=login';
		$this->items['userlogin.username'] = POST_NAME;
		$this->items['userlogin.password'] = POST_PASSWORD;
		$this->items['to.registerform'] = './users.php?action=registerform';
		$this->items['to.lostpasswordform'] = './users.php?action=lostpasswordform';
		$this->items['to.postnewsform'] = 'news.php?action=postnewsform';
		$this->items['newcomment.action'] = 'news.php?action=postcomment';

		$this->items['prevcomments.link'] = 'news.php?action=viewcomments&id=' . $this->get (GET_ID);
		$prev = $this->get ('offset') - $this->config->getConfigByNameType ('news/postsonpage',TYPE_INT);
		$this->items['prevcomments.link'] .= '&offset=' . $prev;

		$this->items['nextcomments.link'] = 'news.php?action=viewcomments&id=' . $this->get (GET_ID);
		$next = $this->get ('offset') + $this->config->getConfigByNameType ('news/postsonpage',TYPE_INT);
		$this->items['nextcomments.link'] .= '&offset=' . $next;

		$this->items['newcomment.method'] = 'post';
		$this->items['newcomment.subject'] = POST_SUBJECT;
		$this->items['newcomment.message'] = POST_MESSAGE;
		$this->items['newcomment.on_comment'] = POST_ID_COMMENT;
		$this->items['newcomment.on_news'] = POST_ID_NEWS;
		$this->items['postnews.availablecategories'] = $this->showNewsCategories ();
		$this->items['postnews.category'] = 'category';
		$this->items['postnews.message'] = 'message';
		$this->items['postnews.subject'] = 'subject';
		$this->items['postnews.action'] = 'news.php?action=postnews';
		$this->items['postnews.method'] = 'post';
		$this->items['comments.navigator'] = $this->showCommentNavigator ();
		if ($this->getPageID () == 'news.php?action=viewcomments') {
			preg_match_all ('/{news (.+?)}/is',$this->fileCont,$matches);
			foreach ($matches[0] as $number => $match) {
				switch ($matches[1][$number]) {
					case 'item':
						$item = $this->news->getNews ($this->get ('id'));
						$replace = $this->showNewsItem ($item,true);
						break;
					case 'comments':
						$replace = $this->showComments ($this->get ('id'));
						break;
				}
				$this->fileCont = str_replace ($match,$replace,$this->fileCont);
			}
		}
		if ($this->getPageID () == 'news.php?action=postcommentform') {
			preg_match_all ('/{newcomment (.+?)}/is',$this->fileCont,$matches);
			foreach ($matches[0] as $number => $match) {
				switch ($matches[1][$number]) {
					case 'id_on_comment':
						$replace = $this->get (POST_ID_COMMENT);
						break;
					case 'on_news':
						$replace = $this->get (POST_ID_NEWS);
						break;
				}
				$this->fileCont = str_replace ($match,$replace,$this->fileCont);
			}
		}
		preg_match_all ('#&(.+?);#',$this->fileCont,$matches);
		foreach ($matches[0] as $number => $match) {
			if (key_exists ($matches[1][$number],$this->items)) {
				$item = $this->items[$matches[1][$number]];
				$this->fileCont = ereg_replace ($match,$item,$this->fileCont);
			}
		}
		// the 'i' is appendend to be not case sesitive
		// the 's' is appendend to inlcude the newline char in DOT

		$varlist['loggedin'] = $this->user->isLoggedIn ();
		$varlist['pollvoted'] = $this->poll->userHasVoted ($this->user->getConfig ('name'));
		$limit = $this->news->getLimitComments ($this->get (GET_ID),$this->get ('offset'));
		if ($limit['previous'] < 0) {
			$varlist['commentsprev'] = false;
		} else {
			$varlist['commentsprev'] = true;
		}

		if ($limit['next'] >= $limit['total']) {
			$varlist['commentsnext'] = false;
		} else {
			$varlist['commentsnext'] = true;
		}

		preg_match_all ('/\{if (.+?)\}(.+?)\{endif\}/is',$this->fileCont,$matches);
		foreach ($matches[0] as $number => $match) {
			$replace = NULL;
			trim ($matches[1][$number]);
			if ($matches[1][$number][0] == '!') {
				$var = substr ($matches[1][$number],1);
				$not = true;
			} else {
				$var = $matches[1][$number];
				$not = false;
			}

			if (array_key_exists ($var,$varlist)) {
				if ($varlist[$var] == true) {
					if ($not == false) {
						$replace = $matches[2][$number];
					}
				} else {
					if ($not == true) {
						$replace = $matches[2][$number];
					}
				}
			} else {
				throw new exceptionlist ('Uknown variable');
			}
			// I'm using str_replace and not ereg_replace because
			// ereg causes problems with a '?'
			// and str_replace is faster
			$this->fileCont = str_replace ($match,$replace,$this->fileCont);
		}
	}

	private function includes (&$string) {
		preg_match_all ('#{include (.+?)}#',$string,$matches);
		foreach ($matches[0] as $number => $match) {
			$fileName = $this->convertFile ($matches[1][$number]);
			$fileC = $this->file ($fileName);
			$string = ereg_replace ($match,$fileC,$string);
		}
	}

	private function localize () {
		// TODO
	}

	private function getPageID () {
		$pagename = $_SERVER['PHP_SELF'];
		// removes everything before and '/' sign itself
		// example /dir/yapcas/index.php --> index.php
		$ID = preg_replace ('/(.+?)*\/(.+?)/','\\2',$pagename);
		// add the action if needed
		if ($this->get ('action') !== false) {
			$ID .= '?action=' . $this->get ('action');
		}
		return $ID;
	}

	public function get ($string) {
		if (! empty ($_GET[$string])) {
			return $_GET[$string];
		} else {
			return false;
		}
	}

	public function getPageTitle () {
		$ID = $this->getPageID ();
		$language =
			$this->config->getConfigByNameType ('general/language',TYPE_STRING);
		$sql = 'SELECT * FROM ' . TBL_PAGES;
		$sql .= ' WHERE name=\'' . $ID . '\'';
		$sql .= 'AND language=\'' . $language . '\' LIMIT 1';
		$query = $this->database->query ($sql);
		if ($this->database->num_rows ($query) == 0) {
			$pagetitle = $this->lang->translate ('Untitled');
		} else {
			$pagetitle = $this->database->fetch_array ($query);
			$pagetitle = $pagetitle['shown_name'];
		}
		return $pagetitle;
	}

	private function getPageContent () {
		$ID = $this->getPageID ();
		$language =
			$this->config->getConfigByNameType ('general/language',TYPE_STRING);
		$sql = 'SELECT * FROM ' . TBL_PAGES;
		$sql .= ' WHERE name=\'' . $ID . '\'';
		$sql .= 'AND language=\'' . $language . '\' LIMIT 1';
		$query = $this->database->query ($sql);
		if ($this->database->num_rows ($query) == 0) {
			$content = NULL;
		} else {
			$content = $this->database->fetch_array ($query);
			$content = $content['content'];
		}
		return $content;
	}

	public function redirect ($link) {
		error_reporting (E_NONE);
		header ('Location: ' . $link);
		$output = $this->getfile ('themes/' . $this->themedir . '/redirect.html');
		$output = ereg_replace ('%redirect.content',$link,$output);
		echo $output;
		exit ();
	}

	public function loadSkinFile ($skinFile,$loginReq = false) {
		try {
			if (($loginReq == true) and ($this->user->isLoggedIn () == false)) {
				$this->redirect ('index.php?error=' .
					$this->lang->translate ('You need to be logged in to access this page'));
			}
			$this->fileCont = $this->file ($this->convertFile ($skinFile));
			$this->loadSideBar ();
			$this->loadNavigationBar ();
			$this->loadItems ();
			$this->includes ($this->fileCont);
			$this->loadItems ();
			$this->loadPoll ();
			$this->localize ();
			echo $this->fileCont;
		}
		catch (exceptionlist $e) {
			echo $e->getMessage ();
		}
	} /* public function loadSkinFile ($skinFile,$loginReq = true) */
} /* CSkin */
?>
