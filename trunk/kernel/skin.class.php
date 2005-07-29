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

	public function file ($fileName) {
			if (is_readable ($fileName)) {
				return file_get_contents ($fileName);
			} else {
				throw new exceptionlist ('Failed to open file' . $fileName,ERROR_THEME);
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
		$onThisPage = $this->loadGroup ($this->pages['index.html']);
		$intersect = implode (',',array_intersect ($childsOfSideBar,$onThisPage));
		$this->fileCont = preg_replace ('#{sidebar}#',$intersect,$this->fileCont);
	}

	private function loadItems () {
		$this->items['site.title'] =
			$this->config->getConfigByNameType ('general/sitename',TYPE_STRING);
		$this->items['site.description'] =
			$this->config->getConfigByNameType ('general/description',TYPE_STRING);
		$this->items['page.title'] = $this->getPageTitle ();
		$this->items['page.content'] = $this->getPageContent ();
		preg_match_all ('#&(.+?);#',$this->fileCont,$matches);
		foreach ($matches[0] as $number => $match) {
			if (key_exists ($matches[1][$number],$this->items)) {
				$item = $this->items[$matches[1][$number]];
				$this->fileCont = ereg_replace ($match,$item,$this->fileCont);
			}
		}
	}

	private function includes () {
		preg_match_all ('#{include (.+?)}#',$this->fileCont,$matches);
		foreach ($matches[0] as $number => $match) {
			$fileName = $this->convertFile ($matches[1][$number]);
			$fileC = $this->file ($fileName);
			$this->fileCont = ereg_replace ($match,$fileC,$this->fileCont);
		}
	}

	private function localize () {
		// TODO
	}

	private function getPageID () {
		$pagename = $_SERVER['PHP_SELF'];
		// removes everything before and '/'
		return preg_replace ('#(.+?)/(.+?)#','\\2',$pagename);
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
			$content = $pagetitle['content'];
		}
		return $content;
	}

	public function loadSkinFile ($skinFile,$loginReq = true) {
		try {
			$this->fileCont = $this->file ($this->convertFile ($skinFile));
			$this->loadSideBar ();
			$this->loadItems ();
			$this->includes ();
			$this->localize ();
			echo $this->fileCont;
		}
		catch (exceptionlist $e) {
			echo $e->getMessage ();
		}
	} /* public function loadSkinFile ($skinFile,$loginReq = true) */
} /* CSkin */
?>