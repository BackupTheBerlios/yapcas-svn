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
/**
* File that take care of the skin SubSystem
*
* @package skin
* @author Nathan Samson
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
/**
* class that take care of the skin SubSystem
*
* @version 0.4cvs
* @author Nathan Samson
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @todo cleanup the code
*/
class CSkin {
	function __construct () {
		error_reporting (E_ALL);
		session_start ();
		// check for PHP version
		$req = '5.0.0';
		if (version_compare ($req,phpversion(),'>=')) {
			die ('PHP Version ' . $req .' or higher is required');
		}
		// as it crashes between this and load of the config
		// we need to have all debug info
		// FIXME
		if (! file_exists ('.install.php')) {
			include_once ('kernel/config.class.php');
			include_once ('kernel/language.class.php');
			$config = new CConfig ();
			$config->addConfigByFileName ('site.config.php',TYPE_STRING,'database/tblprefix',0);
			define ('TBL_PREFIX',$config->getConfigByNameType ('database/tblprefix',TYPE_STRING));
			include_once ('kernel/help.class.php');
			include_once ('kernel/users.class.php');
			include_once ('kernel/news.class.php');
			include_once ('kernel/polls.class.php');
			include_once ('kernel/exception.class.php');
			include_once ('kernel/database.class.php');
			include_once ('kernel/constants.php');
			define ('TBL_PAGES',TBL_PREFIX . 'pages');
			$config->addConfigByFileName ('site.config.php',TYPE_INT,'general/errorreporting',0);
			$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/webmastermail',0);
			error_reporting ($config->getConfigByNameType('general/errorreporting',TYPE_INT));
			$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/databasetype',0);
			$dbType = $config->getConfigByNameType ('general/databasetype',TYPE_STRING);
			$dbclass = new CDatabase ();
			$database = $dbclass->load ($dbType,$config,'site.config.php');
			$database->connect ();
			// TODO
			$tables = array ();
			if (checkDatabase ($database,$tables)) {
				// Database seems to be OK
				$config->addConfigByFileName ('site.config.php',TYPE_BOOL,'user/activatemail',0);
				$lang = new CLang ();
				$user = new CUser ($database,$config->getConfigByNameType ('user/activatemail',TYPE_BOOL),$lang);
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('contentlanguage',$user,'contentlanguage','site.config.php'),
					'general/contentlanguage',TYPE_STRING,DEFAULT_CONTENT_LANG);
				$contentLang = $config->getConfigByNameType ('general/contentlanguage',TYPE_STRING);
				$news = new CNews ($database,$lang,$contentLang);
				$poll = new CPoll ($database,$config,$lang);
				$this->help = new CHelp ($database,$config,$lang);
				$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/httplink');
				$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/sitename');
				$config->addConfigByFileName ('site.config.php',TYPE_STRING,'general/description');
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('timezone',$user,'timezone','site.config.php'),
					'general/timezone',TYPE_FLOAT,0);
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('timeformat',$user,'timeformat','site.config.php'),
					'general/timeformat',TYPE_STRING,'');
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('uilanguage',$user,'uilanguage','site.config.php'),
					'general/uilanguage',TYPE_STRING,STANDARD_LANGUAGE);
				$lang->update ($config->getConfigByNameType('general/uilanguage',TYPE_STRING));
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('theme',$user,'theme','site.config.php'),
					'general/theme',TYPE_STRING,'moderngray');
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('threaded',$user,'threaded','site.config.php'),
					'news/threaded',TYPE_BOOL,YES);
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('headlines',$user,'headlines','site.config.php'),
					'news/headlines',TYPE_INT,5);
				$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
					array('postsonpage',$user,'postsonpage','site.config.php'),
					'news/postsonpage',TYPE_INT,10);
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
				$this->BBCTags = array ('b','u','i','quote');
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
				include_once ('kernel/config.class.php');
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

	function replaceBBCTag ($text,$bbctag,$htmlopen,$htmlclose) {
		$text = str_replace ('[' . $bbctag . ']',$htmlopen,$text);
		$text = str_replace ('[/' . $bbctag . ']',$htmlclose,$text);
		return $text;
	}

	private function formatMessage ($input,$useEmot = true,$useBBC = true) {
		$output = strip_tags ($input);
		$output = nl2br ($output);
		if ($useEmot) {
			foreach ($this->getAllEmoticons (true) as $smiley) {
				$emot = $this->items['emoticon'];
				$emot = str_replace ('{emot img}',$this->convertImage ($smiley['image']),$emot);
				$emot = str_replace ('{emot name}',$smiley['name'],$emot);
				$output = str_replace ($smiley['deftext'],$emot,$output);
				foreach ($smiley['alttexts'] as $other) {
					$altemot = $this->items['emoticon'];
					$altemot = str_replace ('{emot img}',$this->convertImage ($smiley['image']),$altemot);
					$altemot = str_replace ('{emot name}',$smiley['name'],$altemot);
					$output = str_replace ($other,$altemot,$output);
				}
			}
		}
		if ($useBBC == true) {
			foreach ($this->BBCTags as $tag) {
				$output = $this->replaceBBCTag ($output,$tag,
					$this->BBC[$tag]['open'],$this->BBC[$tag]['close']);
			}
		}
		return $output;
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

	private function convertImage ($file = NULL) {
		return 'themes/' . $this->themedir . '/images/' . $file;
	} /* private function convertFile ($file) */

	private function loadGroup ($string) {
		$array = explode (',',$string);
		foreach ($array as $key => $item) {
			// if it is defined as group, replace it
			if (array_key_exists ($item,$this->groups)) {
				$array[$key] = $this->groups[$item];
				$array = $this->loadGroup (implode (',',$array));
			} else {
				$this->parse ($item);
				$array[$key] = $item;
			}
		}
		return $array;
	}

	private function loadSideBar () {
		$childsOfSideBar = $this->loadGroup ($this->childsOfSideBar);
		$onThisPage = $this->loadGroup ($this->pages[$this->getPageID ()]);
		// implode char is empty because between the parts must be nothing
		$intersect = implode ('',array_intersect ($childsOfSideBar,$onThisPage));
		$this->fileCont = preg_replace ('/{sidebar}/',$intersect,$this->fileCont);
	}

	private function loadNavigationBar () {
		$childsOfNavigation = $this->loadGroup ($this->childsOfNavigation);
		$onThisPage = $this->loadGroup ($this->pages[$this->getPageID ()]);
		// implode char is empty because between the parts must be nothing
		$intersect = implode ('',array_intersect ($childsOfNavigation,$onThisPage));
		$this->fileCont = preg_replace ('/{navigation}/',$intersect,$this->fileCont);
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
			$tmp = NULL;
			$what = $matches[1][$number];
			switch ($what) {
				case 'subject':
					$tmp = $item[FIELD_NEWS_SUBJECT];
					break;
				case 'author':
					$tmp = $item[FIELD_NEWS_AUTHOR];
					break;
				case 'date':
					$tmp = $this->formatDate ($item[FIELD_NEWS_DATE]);
					break;
				case 'message':
					$tmp = $this->formatMessage ($item[FIELD_NEWS_MESSAGE]);
					break;
				case 'linkreadcomments':
					$tmp = 'news.php?action=viewcomments&id=' . $item[FIELD_NEWS_ID] . '#readcomments';
					break;
				case 'linknewcomment':
					$tmp = 'news.php?action=postcommentform&on_news_id=' . $item[FIELD_NEWS_ID];
					break;
				case 'editnewsbutton':
					if ($item[FIELD_NEWS_AUTHOR] == $this->user->getConfig ('name')) {
						$but = $this->items['editnews.button'];
						$this->showButton ($but);
						$tmp = str_replace ('{news linkeditnewsform}',
								'news.php?action=editnewsform&' . GET_ID . '=' . $item[FIELD_NEWS_ID],
								$but);
					}
			}
			$output = ereg_replace ($match,$tmp,$output);
		}
		return $output;
	}

	private function getNewsItems () {
		$postsOnPage = $this->config->getConfigByNameType ('news/postsonpage',TYPE_NUMERIC);
		$newsitems = $this->news->getAllNews ($postsOnPage,$this->get ('offset'),$this->get ('category'));
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
					$tmp = $this->formatDate ($item[FIELD_NEWS_DATE]);
					break;
				case 'message':
					$tmp = $this->formatMessage ($item[FIELD_NEWS_MESSAGE]);
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
			$maxHeadlines = $this->config->getConfigByNameType ('news/headlines',TYPE_NUMERIC);
			$newsitems = $this->news->getHeadlines ($maxHeadlines);
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
		$lang = $this->config->getConfigByNameType ('general/contentlanguage',TYPE_STRING);
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
		$contentLang = $this->config->getConfigByNameType ('general/contentlanguage',TYPE_STRING);
		$pollID = $this->poll->getIDCurrentPollByLanguage ($contentLang);
		if ($pollID === false) {
			return NULL;
		}
		$poll = $this->poll->getPollByID ($pollID);
		$output = $this->file ($this->convertFile ('shortviewpoll.html'));
		preg_match_all ('/{poll (.+?)}/is',$output,$matches);
		foreach ($matches[0] as $number => $match) {
			switch ($matches[1][$number]) {
				case 'question':
					$output = str_replace ($match,$poll[FIELD_POLL_QUESTION],$output);
					break;
				case 'choices':
					$choices = explode (';',$poll[FIELD_POLL_CHOICES]);
					$html = NULL;
					foreach ($choices as $number => $choice) {
						$html .= $this->items['poll.choice'];
						$html = str_replace ('{choice text}',$choice,$html);
						// stupid thing with integers
						$html = str_replace ('{choice number}',"$number",$html);
					}
					$output = str_replace ($match,$html,$output);
					break;
				case 'voteaction':
					$output = str_replace ($match,
						'polls.php?action=vote&id=' . $poll[FIELD_POLL_ID],
						$output);
					break;
				case 'votemethod':
					$output = str_replace ($match,'post',$output);
					break;
				case 'results':
					$choices = explode (';',$poll[FIELD_POLL_CHOICES]);
					$results = explode (';',$poll[FIELD_POLL_RESULTS]);
					$html = NULL;
					foreach ($choices as $number => $choice) {
						$html .= $this->items['poll.result'];
						$html = str_replace ('{choice text}',$choice,$html);
						$html = str_replace ('{choice result}',$results[$number],$html);
						$totalvotes = array_sum ($results);
						$procent = round ($results[$number] / $totalvotes * 100);
						$html = str_replace ('{choice resultprocent}',$procent,$html);
					}
					$output = str_replace ($match,$html,$output);
					break;
			}
		}
		return $output;
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
					$replace = $this->formatDate ($comment[FIELD_COMMENTS_DATE]);
					break;
				case 'newcommentlink':
					$replace = 'news.php?action=postcommentform';
					$replace .= '&amp;' . POST_ID_NEWS . '=' . $comment[FIELD_COMMENTS_ID_NEWS];
					$replace .= '&amp;' . POST_ID_COMMENT . '=' . $comment[FIELD_COMMENTS_ID];
					break;
				case 'message':
					$replace = $this->formatMessage ($comment[FIELD_COMMENTS_MESSAGE]);
					break;
				case 'linkeditcommentform':
					$replace = 'news.php?action=editcommentform&' . GET_ID . '=' . $comment[FIELD_COMMENTS_ID];
					break;
				case 'editcommentbutton':
					if ($comment[FIELD_COMMENTS_AUTHOR] == $this->user->getConfig ('name')) {
						$but = $this->items['editcomment.button'];
						$this->showButton ($but);
						$replace = str_replace ('{comment linkeditcommentform}',
								'news.php?action=editcommentform&' . GET_ID . '=' . $comment[FIELD_COMMENTS_ID],
								$but);
					}
			}
			$output = str_replace ($match,$replace,$output);
		}
		return $output;
	}

	private function startCommentThread ($parent) {
		//$parent = $this->news->getComment ($parentID);
		$output = $this->showComment ($parent);
		foreach ($this->news->getThreadChildren ($parent) as $child) {
			$output .= $this->items['thread.open'];
			$output .= $this->startCommentThread ($child);
			$output .= $this->items['thread.close'];
		}
		return $output;
	}

	private function showCommentsFlat ($newsID) {
		$output = NULL;
		$postsOnPage = $this->config->getConfigByNameType ('news/postsonpage',TYPE_NUMERIC);
		$comments = $this->news->getAllComments ($newsID,$postsOnPage,$this->get ('offset'));
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
		$contentLang = $this->config->getConfigByNameType ('general/contentlanguage',TYPE_STRING);
		$categories = $this->news->getAllCategoriesByLanguage ($contentLang);
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

	private function showNewsNavigator () {
		$output = NULL;
		$output .= $this->items['news.navigator'];
		$this->includes ($output);
		return $output;
	}

	private function showButton (&$subject) {
		preg_match_all ('/{button (.+?) (.+?) }/',$subject,$matches);
		foreach ($matches[0] as $number => $match) {
			$button = $this->items['button'];
			$button = str_replace ('{button text}',$matches[1][$number],$button);
			$button = str_replace ('{button action}',$matches[2][$number],$button);
			$subject = str_replace ($match,$button,$subject);
		}
		return $subject;
	}

	private function showUBBFormat () {
		$output = NULL;
		$i = 0;
		foreach ($this->BBCTags as $tag) {
			$output .= $this->items['bbc.button'];
			$output = str_replace ('{bbc tag}',$tag,$output);
			$output = str_replace ('{bbc code}',$i,$output);
			$output = str_replace ('{bbc key}',$tag,$output);
			$i = $i+2;
		}
		return $output;
	}

	/**
	 * Loads the emoticon set from the file 'smiley' in the theme dir
	 *
	 * A line in smiley looks like this 'something.gif ;) ;-)'
	 * everything is splitted on a space ' '
	 * the first word is the name of the image relative to  themedir/images
	 * the second is the default chars to create the emoticons
	 * the later ones are alternative ones
	 *
	 * a line can also start with an '!'
	 * this would say that that line is not important (commented out)
	 *
	 * if the line is 'break' only the emoticons defined before this line are showed
	 * if $all == false
	 *
	 * empty lines are igonored
	 *
	 * if the first line is 'VERSION 2' (case-inseitive) the 2 word is the name
	 * of the emoticons
	 * whole line looks than 'sigh.gif sigh :sigh: ::sigh::'
	*/
	private function getAllEmoticons ($all = false) {
		$items = file ($this->convertFile ('smiley'));
		$version2 = false;
		if (strtoupper ($items[0]) == 'VERSION 2') {
			$version2 = true;
			// now removes the first line
			array_shift ($items);
		}
		foreach ($items as $item) {
			// remove some chars at the beginning and end of the line
			$item = trim ($item);
			if (empty ($item)) {
				continue;
			} else if ($item == 'break') {
				if (! $all) {
					return $emot;
				} else {
					continue;
				}
			}
			if ($item[0] != '!') {
				// first creating an empty array, otherwise othertexts will be filled up
				$em = array ();
				$item = split (' ',$item);
				$em['image'] = $item[0];
				if ($version2) {
					$em['name'] = $item[1];
					$em['deftext'] = $item[2];
					$i = 3;
				} else {
					$em['name'] = NULL;
					$em['deftext'] = $item[1];
					$i = 2;
				}
				$em['alttexts'] = array ();
				while (array_key_exists ($i,$item)) {
					$em['alttexts'][] = $item[$i];
					$i++;
				}
				$emot[] = $em;
			}
		}
		return $emot;
	}

	private function showSmilies ($all = false) {
		$output = NULL;
		$i = 0;
		$emoticons = $this->getAllEmoticons ($all);
		foreach ($emoticons as $emoticon) {
			$output .= $this->items['emoticon.button'];
			$output = str_replace ('{emot text}',$emoticon['deftext'],$output);
			$output = str_replace ('{emot image}',$this->convertImage ($emoticon['image']),$output);
			$i++;
		}
		return $output;
	}

	private function showBBCJSArray () {
		$i = 0;
		$output = NULL;
		foreach ($this->BBCTags as $tag) {
			if ($output != NULL) { $output .= ','; }
			$output .= '\'[' . $tag . ']\',';
			$output .= '\'[/' . $tag . ']\'';
			$i = $i+2;
		}
		return $output;
	}

	/**
	 * Ths output an html option item
	 *
	 * @param array $html an html containing some definition
	 * @param mixed $curVal the current value
	 * @param string $name the name of the field
	 * @param array|'string'|'numeric' $posOpts the possible options, if 'string' than all strings are allowed, if 'numeric' all numerics are possible, if array every item in the array are possible
	 * @param array|'string'|'numeric' $showOpts
	*/
	public function options ($html,$curVal,$name,$posOpts,$showOpts) {
		if ($posOpts == 'string') {
			$output = $html['open'];
			$output .= $html['option'];
			$output .= $html['close'];
			$output = str_replace ('{option curval}',$curVal,$output);
			$output = str_replace ('{option name}',$name,$output);
		} else if ($posOpts == 'numeric') {
			$output = $html['open'];
			$output .= $html['option'];
			$output .= $html['close'];
			$output = str_replace ('{option curval}',$curVal,$output);
			$output = str_replace ('{option name}',$name,$output);
		} else if ($posOpts == 'bool') {
			$output = $html['open'];
			if ($curVal == YES) {
				$output .= $html['yes'];
			} else {
				$output .= $html['no'];
			}
			$output .= $html['close'];
			$output = str_replace ('{option name}',$name,$output);
		} else {
			$output = $html['open'];
			foreach ($posOpts as $option) {
				if ($option == $curVal) {
					$output .= $html['selectedoption'];
				} else {
					$output .= $html['option'];
				}
				$output = str_replace ('{option item}',$option,$output);
			}
			$output .= $html['close'];
			$output = str_replace ('{option name}',$name,$output);
		}
		return $output;
	}

	private function showAllUsers () {
		$output = NULL;
		foreach ($this->user->getAllUsersName () as $userName) {
			$output .= $this->items['userlist.item'];
			$output = str_replace ('{item name}',$userName,$output);
			$output = str_replace ('{item link}','users.php?action=viewuser&name=' . $userName,$output);
		}
		return $output;
	}

	private function showAllPolls () {
		$output = NULL;
		$contLang = $this->config->getConfigByNameType ('general/contentlanguage',TYPE_STRING);
		foreach ($this->poll->getAllPollsByLanguage ($contLang) as $poll) {
			$output .= $this->includes ($this->items['viewpoll.item']);
			$output = str_replace ('{poll question}',$poll[FIELD_POLL_QUESTION],$output);
			$choices = explode (';',$poll[FIELD_POLL_CHOICES]);
			$results = explode (';',$poll[FIELD_POLL_RESULTS]);
			$html = NULL;
			foreach ($choices as $number => $choice) {
				$html .= $this->items['poll.result'];
				$html = str_replace ('{choice text}',$choice,$html);
				$html = str_replace ('{choice result}',$results[$number],$html);
				$totalvotes = array_sum ($results);
				$procent = round ($results[$number] / $totalvotes * 100);
				$html = str_replace ('{choice resultprocent}',$procent,$html);
			}
			$output = str_replace ('{poll results}',$html,$output);
		}
		return $output;
	}

	private function showHelpIndexItem ($index) {
		$output = NULL;
		foreach ($index as $child) {
			$tmp = $this->items['help.indexitem'];
			$categories = $this->showHelpIndexItem ($child['childs']);
			$tmp = str_replace ('{helpindex name}',$child['name'],$tmp);
			$tmp = str_replace ('{helpindex categories}',$categories,$tmp);
			$tmp = str_replace ('{helpindex id}','cat'.$child['id'],$tmp);
			$questions = NULL;
			foreach ($child['questions'] as $q) {
				$tmpq = $this->items['help.indexquestion'];
				$tmpq = str_replace ('{indexquestion link}','#ques'.$q[FIELD_HELP_QUESTION_ID],$tmpq);
				$tmpq = str_replace ('{indexquestion id}','ques'.$q[FIELD_HELP_QUESTION_ID],$tmpq);
				$tmpq = str_replace ('{indexquestion question}',$q[FIELD_HELP_QUESTION_QUESTION],$tmpq);
				$questions .= $tmpq;
			}
			$tmp = str_replace ('{helpindex questions}',$questions,$tmp);
			$output .= $tmp;
		}
		return $output;
	}

	private function showHelpIndex () {
		$contentLanguage = 'nl';
		$index = $this->help->getIndexByLanguage ($contentLanguage);
		return $this->showHelpIndexItem ($index);
	}

	private function showFAQItem ($index) {
		$output = NULL;
		foreach ($index as $child) {
			$tmp = $this->items['help.faqcategory'];
			$categories = $this->showFAQItem ($child['childs']);
			$tmp = str_replace ('{helpindex name}',$child['name'],$tmp);
			$tmp = str_replace ('{helpindex categories}',$categories,$tmp);
			$tmp = str_replace ('{helpindex id}','cat'.$child['id'],$tmp);
			$questions = NULL;
			foreach ($child['questions'] as $q) {
				$tmpq = $this->items['help.faqquestion'];
				$tmpq = str_replace ('{indexquestion link}','#ques'.$q[FIELD_HELP_QUESTION_ID],$tmpq);
				$tmpq = str_replace ('{indexquestion id}','ques'.$q[FIELD_HELP_QUESTION_ID],$tmpq);
				$tmpq = str_replace ('{indexquestion question}',$q[FIELD_HELP_QUESTION_QUESTION],$tmpq);
				$tmpq = str_replace ('{indexquestion answer}',$q[FIELD_HELP_QUESTION_ANSWER],$tmpq);
				$questions .= $tmpq;
			}
			$tmp = str_replace ('{helpindex questions}',$questions,$tmp);
			$output .= $tmp;
		}
		return $output;
	}

	private function showFAQ () {
		$contentLanguage = 'nl';
		$index = $this->help->getIndexByLanguage ($contentLanguage);
		return $this->showFAQItem ($index);
	}

	private function initItems () {
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
		$this->items['to.lostpasswordform'] = './users.php?action=sendpasswordform';
		$this->items['to.postnewsform'] = 'news.php?action=postnewsform';
		$this->items['newcomment.action'] = 'news.php?action=postcomment';

		$this->items['prevcomments.link'] = 'news.php?action=viewcomments&id=' . $this->get (GET_ID);
		$prev = $this->get ('offset') - $this->config->getConfigByNameType ('news/postsonpage',TYPE_INT);
		$this->items['prevcomments.link'] .= '&offset=' . $prev;

		$this->items['nextcomments.link'] = 'news.php?action=viewcomments&id=' . $this->get (GET_ID);
		$next = $this->get ('offset') + $this->config->getConfigByNameType ('news/postsonpage',TYPE_INT);
		$this->items['nextcomments.link'] .= '&offset=' . $next;

		$this->items['prevnews.link'] = 'index.php';
		$prev = $this->get ('offset') - $this->config->getConfigByNameType ('news/postsonpage',TYPE_INT);
		$this->items['prevnews.link'] .= '?offset=' . $prev;

		$this->items['nextnews.link'] = 'index.php';
		$next = $this->get ('offset') + $this->config->getConfigByNameType ('news/postsonpage',TYPE_INT);
		$this->items['nextnews.link'] .= '?offset=' . $next;

		$this->items['newcomment.method'] = 'post';
		$this->items['newcomment.subject'] = POST_SUBJECT;
		$this->items['newcomment.message'] = POST_MESSAGE;
		$this->items['newcomment.on_comment'] = POST_ID_COMMENT;
		$this->items['newcomment.on_news'] = POST_ID_NEWS;
		$this->items['postnews.availablecategories'] = $this->showNewsCategories ();
		$this->items['postnews.category'] = POST_CATEGORY;
		$this->items['postnews.message'] = POST_MESSAGE;
		$this->items['postnews.subject'] = POST_SUBJECT;
		$this->items['postnews.action'] = 'news.php?action=postnews';
		$this->items['postnews.method'] = 'post';
		$this->items['editcomment.message'] = POST_MESSAGE;
		$this->items['editcomment.subject'] = POST_SUBJECT;
		$this->items['editcomment.method'] = 'post';
		$this->items['editnews.message'] = POST_MESSAGE;
		$this->items['editnews.subject'] = POST_SUBJECT;
		$this->items['editnews.method'] = 'post';
		$this->items['comments.navigator'] = $this->showCommentNavigator ();
		$this->items['news.navigator'] = $this->showNewsNavigator ();
		$this->items['format.bbc'] = $this->showUBBFormat ();
		$this->items['format.smilies'] = $this->showSmilies ();
		$this->items['format.allsmilies'] = $this->showSmilies (true);
		$this->items['bbc.jsarray'] = $this->showBBCJSArray ();
		$this->items['poll.viewcurrentpoll'] = $this->loadPoll ();
		$this->items['registerform.action'] = 'users.php?action=register';
		$this->items['registerform.method'] = 'post';
		$this->items['registerform.email'] = POST_EMAIL;
		$this->items['registerform.name'] = POST_NAME;
		$this->items['registerform.password1'] = POST_PASSWORD1;
		$this->items['registerform.password2'] = POST_PASSWORD2;
		$this->items['changeoptions.action'] = 'users.php?action=changeoptions';
		$this->items['changeoptions.method'] = 'post';
		$this->items['sendpassword.action'] = 'users.php?action=sendpassword';
		$this->items['sendpassword.method'] = 'post';
		$this->items['sendpassword.name'] = POST_NAME;
		$this->items['sendpassword.email'] = POST_EMAIL;
		$this->items['viewusers.list'] = $this->showAllUsers ();
		$this->items['to.pollslist'] = 'polls.php?action=allpolls';
		$this->items['viewpolls.list'] = $this->showAllPolls ();
		$this->items['help.index'] = $this->showHelpIndex ();
		$this->items['help.faq'] = $this->showFAQ ();
	}

	private function includes (&$string) {
		preg_match_all ('#{include (.+?)}#',$string,$matches);
		foreach ($matches[0] as $number => $match) {
			$fileName = $this->convertFile ($matches[1][$number]);
			$fileC = $this->file ($fileName);
			$this->parse ($fileC);
			$string = ereg_replace ($match,$fileC,$string);
		}
		return $string;
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
			$this->config->getConfigByNameType ('general/contentlanguage',TYPE_STRING);
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
			$this->config->getConfigByNameType ('general/contentlanguage',TYPE_STRING);
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
		if (($loginReq == true) and ($this->user->isLoggedIn () == false)) {
			$this->redirect ('index.php?error=' .
				$this->lang->translate ('You need to be logged in to access this page'));
		}
		$this->fileCont = $this->file ($this->convertFile ($skinFile));
		$this->initItems ();
		$this->parse ($this->fileCont);
		$this->loadSideBar ();
		$this->loadNavigationBar ();
		$this->localize ();
		echo $this->fileCont;
	} /* public function loadSkinFile ($skinFile,$loginReq = true) */

	public function installedSkins () {
		$files = scandir ('themes');
		$installed = array ();
		foreach ($files as $file) {
			if ((is_dir ('themes/' . $file)) and
				(file_exists ('themes/' . $file . '/theme.php'))) {
				$installed[] = $file;
			}
		}
		return $installed;
	}

	public function formatDate ($time) {
		$timezone = $this->config->getConfigByNameType ('general/timezone',TYPE_INT);
		$time = $time + $timezone * 60 * 60;
		$timeformat = $this->config->getConfigByNameType ('general/timeformat',TYPE_STRING);
		return date ($timeformat,$time);
	}

	public function getUTCtime () {
		$time = time ();
		$UTCtime = $time - (date ('Z'));
		return $UTCtime;
	}

	public function catchError ($exc,$link,$message,$moreinf) {
		if ($exc->fatal) {
			$link .= 'error=' . $message;
			$link .= '<ul>';
			while ($exc != NULL) {
				$link .= '<li>' . $exc->getMessage () . '</li>';
				$exc = $exc->getNext ();
			}
			$link .= '</ul>';
		} else {
			$link .= 'warning=' . 'Your action can be not completed: ' . $exc->getMessage ();
		}
		if (($moreinf == true) and (isset ($exc->debuginfo))) {
			$link .= ': ' . $exc->debuginfo;
		}
		return $link;
	}

	private function replaceViewComments (&$text) {
		if ($this->getPageID () == 'news.php?action=viewcomments') {
			preg_match_all ('/{news (.+?)}/is',$text,$matches);
			foreach ($matches[0] as $number => $match) {
				switch ($matches[1][$number]) {
					case 'item':
						$item = $this->news->getNewsByID ($this->get ('id'));
						$replace = $this->showNewsItem ($item,true);
						break;
					case 'comments':
						$replace = $this->showComments ($this->get ('id'));
						break;
				}
				$text = str_replace ($match,$replace,$text);
			}
		}
	}

	private function replacePostCommentsForm (&$text) {
		if ($this->getPageID () == 'news.php?action=postcommentform') {
			preg_match_all ('/{newcomment (.+?)}/is',$text,$matches);
			foreach ($matches[0] as $number => $match) {
				switch ($matches[1][$number]) {
					case 'id_on_comment':
						$replace = $this->get (POST_ID_COMMENT);
						break;
					case 'on_news':
						$replace = $this->get (POST_ID_NEWS);
						break;
				}
				$text = str_replace ($match,$replace,$text);
			}
		}
	}

	private function replaceEditCommentForm (&$text) {
		if ($this->getPageID () == 'news.php?action=editcommentform') {
			preg_match_all ('/{editcomment (.+?)}/is',$text,$matches);
			$comment = $this->news->getCommentByID ($this->get (GET_ID));
			foreach ($matches[0] as $number => $match) {
				$replace = NULL;
				switch ($matches[1][$number]) {
					case 'action':
						$replace = 'news.php?action=editcomment&amp;' . GET_ID . '=' . $this->get (GET_ID);
						break;
					case 'currentsubject':
						$replace = $comment[FIELD_COMMENTS_SUBJECT];
						break;
					case 'currentmessage':
						$replace = $comment[FIELD_COMMENTS_MESSAGE];
						break;
				}
				$text = str_replace ($match,$replace,$text);
			}
		}
	}

	private function replaceEditNewsForm (&$text) {
		if ($this->getPageID () == 'news.php?action=editnewsform') {
			preg_match_all ('/{editnews (.+?)}/is',$text,$matches);
			$comment = $this->news->getNewsByID ($this->get (GET_ID));
			foreach ($matches[0] as $number => $match) {
				$replace = NULL;
				switch ($matches[1][$number]) {
					case 'action':
						$replace = 'news.php?action=editnews&amp;' . GET_ID . '=' . $this->get (GET_ID);
						break;
					case 'currentsubject':
						$replace = $comment[FIELD_NEWS_SUBJECT];
						break;
					case 'currentmessage':
						$replace = $comment[FIELD_NEWS_MESSAGE];
						break;
				}
				$text = str_replace ($match,$replace,$text);
			}
		}
	}

	/**
	 *
	 *
	 * @todo change installedLanguages in contentlanguage in $this->availableLanguages ()
	*/
	private function replaceChangeOptionsForm (&$text) {
		if ($this->getPageID () == 'users.php?action=changeoptionsform') {
			preg_match_all ('/{option (.+?)}/is',$text,$matches);
			foreach ($matches[0] as $number => $match) {
				$replace = NULL;
				switch ($matches[1][$number]) {
					case 'email':
						$curVal = $this->user->getConfig ('email');
						$replace = $this->options ($this->options['email'],$curVal,POST_EMAIL,'string','string');
						break;
					case 'job':
						$curVal = $this->user->getDBConfig ('job');
						$replace = $this->options ($this->options['job'],$curVal,POST_NEW_JOB,'string','string');
						break;
					case 'jabber':
						$curVal = $this->user->getDBConfig ('jabber');
						$replace = $this->options ($this->options['jabber'],$curVal,POST_NEW_JABBER,'string','string');
						break;
					case 'msn':
						$curVal = $this->user->getDBConfig ('msn');
						$replace = $this->options ($this->options['msn'],$curVal,POST_NEW_MSN,'string','string');
						break;
					case 'yahoo':
						$curVal = $this->user->getDBConfig ('yahoo');
						$replace = $this->options ($this->options['yahoo'],$curVal,POST_NEW_YAHOO,'string','string');
						break;
					case 'intrests':
						$curVal = $this->user->getDBConfig ('intrests');
						$replace = $this->options ($this->options['intrests'],$curVal,POST_NEW_INTRESTS,'string','string');
						break;
					case 'aim':
						$curVal = $this->user->getDBConfig ('aim');
						$replace = $this->options ($this->options['aim'],$curVal,POST_NEW_AIM,'string','string');
						break;
					case 'website':
						$curVal = $this->user->getDBConfig ('website');
						$replace = $this->options ($this->options['website'],$curVal,POST_NEW_WEBSITE,'string','string');
						break;
					case 'icq':
						$curVal = $this->user->getDBConfig ('icq');
						$replace = $this->options ($this->options['icq'],$curVal,POST_NEW_ICQ,'numeric','numeric');
						break;
					case 'adress':
						$curVal = $this->user->getDBConfig ('adress');
						$replace = $this->options ($this->options['adress'],$curVal,POST_NEW_ADRESS,'numeric','numeric');
						break;
					case 'password1':
						$curVal = NULL;
						$replace = $this->options ($this->options['password1'],$curVal,POST_NEW_PASSWORD1,'string','string');
						break;
					case 'password2':
						$curVal = NULL;
						$replace = $this->options ($this->options['password2'],$curVal,POST_NEW_PASSWORD2,'string','string');
						break;
					case 'theme':
						$curVal = $this->user->getDBConfig ('theme');
						$replace = $this->options ($this->options['theme'],$curVal,POST_THEME,$this->installedSkins (),$this->installedSkins ());
						break;
					case 'threaded':
						$curVal = $this->user->getDBConfig ('threaded');
						$replace = $this->options ($this->options['threaded'],$curVal,POST_THREADED,'bool','bool');
						break;
					case 'postsonpage':
						$curVal = $this->user->getDBConfig ('postsonpage');
						$replace = $this->options ($this->options['postsonpage'],$curVal,POST_POSTSONPAGE,'numeric','numeric');
						break;
					case 'timezone':
						$curVal = $this->user->getDBConfig ('timezone');
						$this->numTimeZones = array (-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,0,1,2,3,4,5,6,7,8,9,10,11,12);
						$this->showTimeZones = array (-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,0,1,2,3,4,5,6,7,8,9,10,11,12);
						$replace = $this->options ($this->options['timezone'],$curVal,POST_TIMEZONE,$this->numTimeZones,$this->showTimeZones);
						break;
					case 'headlines':
						$curVal = $this->user->getDBConfig ('headlines');
						$replace = $this->options ($this->options['headlines'],$curVal,POST_HEADLINES,'numeric','numeric');
						break;
					case 'timeformat':
						$curVal = $this->user->getDBConfig ('timeformat');
						$replace = $this->options ($this->options['timeformat'],$curVal,POST_TIMEFORMAT,'string','string');
						break;
					case 'uilanguage':
						$curVal = $this->lang->code2lang ($this->user->getDBConfig ('uilanguage'));
						$replace = $this->options ($this->options['language'],$curVal,POST_UILANGUAGE,$this->lang->installed (),$this->lang->installed ());
						break;
					case 'contentlanguage':
						$curVal = $this->lang->code2lang ($this->user->getDBConfig ('contentlanguage'));
						$replace = $this->options ($this->options['language'],$curVal,POST_CONTENTLANGUAGE,$this->lang->installed (),$this->lang->installed ());
						break;
				}
				$text = str_replace ($match,$replace,$text);
			}
		}
	}

	private function replaceViewUser (&$text) {
		if ($this->getPageID () == 'users.php?action=viewuser') {
			preg_match_all ('/{userprofile (.+?)}/is',$text,$matches);
			$userprofile = $this->user->getOtherProfile ($this->get ('name'));
			foreach ($matches[0] as $number => $match) {
				$replace = NULL;
				switch ($matches[1][$number]) {
					case 'email':
						$replace = $userprofile[FIELD_USERS_PROFILE_EMAIL];
						break;
					case 'job':
						$replace = $userprofile[FIELD_USERS_PROFILE_JOB];
						break;
					case 'jabber':
						$replace = $userprofile[FIELD_USERS_PROFILE_JABBER];
						break;
					case 'msn':
						$replace = $userprofile[FIELD_USERS_PROFILE_MSN];
						break;
					case 'yahoo':
						$replace = $userprofile[FIELD_USERS_PROFILE_YAHOO];
						break;
					case 'intrests':
						$replace = $userprofile[FIELD_USERS_PROFILE_INTRESTS];
						break;
					case 'aim':
						$replace = $userprofile[FIELD_USERS_PROFILE_AIM];
						break;
					case 'website':
						$replace = $userprofile[FIELD_USERS_PROFILE_WEBSITE];
						break;
					case 'icq':
						$replace = $userprofile[FIELD_USERS_PROFILE_ICQ];
						break;
					case 'adress':
						$replace = $userprofile[FIELD_USERS_PROFILE_ADRESS];
						break;
				}
				$text = str_replace ($match,$replace,$text);
			}
		}
	}

	private function parseIf (&$text) {
		$varlist['loggedin'] = $this->user->isLoggedIn ();
		$varlist['pollvoted'] = $this->poll->userHasVoted ($this->user->getConfig ('name'));
		$postsOnPage = $this->config->getConfigByNameType ('news/postsonpage',TYPE_NUMERIC);
		$limit = $this->news->getLimitComments ($postsOnPage,$this->get (GET_ID),$this->get ('offset'));
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
		$postsOnPage = $this->config->getConfigByNameType ('news/postsonpage',TYPE_NUMERIC);
		$limit = $this->news->getLimitNews ($postsOnPage,$this->get ('offset'),$this->get ('category'));
		if ($limit['previous'] < 0) {
			$varlist['newsprev'] = false;
		} else {
			$varlist['newsprev'] = true;
		}

		if ($limit['next'] >= $limit['total']) {
			$varlist['newsnext'] = false;
		} else {
			$varlist['newsnext'] = true;
		}

		preg_match_all ('/\{if (.+?)\}(.+?)\{endif\}/is',$text,$matches);
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
			$text = str_replace ($match,$replace,$text);
		}
	}

	private function parse (&$text) {
		preg_match_all ('/&(.*);/',$text,$matches);
		foreach ($matches[0] as $number => $match) {
			if (key_exists ($matches[1][$number],$this->items)) {
				$item = $this->items[$matches[1][$number]];
				$this->parse ($item);
				$text = str_replace ($match,$item,$text);
			}
		}
		$this->parseIf ($text);
		$this->includes ($text);
		$this->replaceViewComments ($text);
		$this->replacePostCommentsForm ($text);
		$this->replaceEditCommentForm ($text);
		$this->replaceEditNewsForm ($text);
		$this->replaceChangeOptionsForm ($text);
		$this->replaceViewUser ($text);
	}
} /* CSkin */
?>
