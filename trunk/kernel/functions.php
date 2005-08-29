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
/**
* File that take care of the skin SubSystem
*
* @package skin
* @author Nathan Samson
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @todo remove this file and copy everything to dkin.class.php
*/

/**
* class that take care of the lang SubSystem
*
* @package skin
* @author Nathan Samson
*/
class lang {
	function __construct () {
		$this->lang = array ();
	}

	function updatelang ($l) {
		if ($l == 'english') {
			unset ($this->lang);
			$this->lang = array ();
		} else {
			if (file_exists ('lang/'. $l .'/news.lang.php')) {
				include ('lang/'. $l .'/news.lang.php');
			}
			if (file_exists ('lang/'. $l .'/users.lang.php')) {
				include ('lang/'. $l .'/users.lang.php');
			}
			if (file_exists ('lang/'. $l .'/polls.lang.php')) {
				include ('lang/'. $l .'/polls.lang.php');
			}
			if (file_exists ('lang/'. $l .'/site.lang.php')) {
				include ('lang/'. $l .'/site.lang.php');
			}
			if (file_exists ('lang/'. $l .'/database.lang.php')) {
				include ('lang/'. $l .'/database.lang.php');
			}
		}
	}

	function translate ($string) {
		if (array_key_exists($string,$this->lang)) {
			return $this->lang[$string];
		} else {
			return $string;
		}
	}
}

function lang2code ($lang) {
	switch (strtolower ($lang)) {
		case 'english':
			return 'en';
		case 'dutch':
			return 'nl';
		default:
			return 'en';
	}
}

function code2lang ($code) {
	switch (strtolower ($code)) {
		case 'en':
			return 'english';
		case 'nl':
			return 'dutch';
		default:
			return 'english';
	}
}

function loadlang ($language) {
	$l = new lang ();
	$l->updatelang ($language);
	return $l;
}


function themesinstalled () {
	$themes = array ();
	$dir = "themes/";
	if ( $handle = opendir ( $dir ) ) {
		while ( false !== ( $file = readdir ( $handle ) ) ) {
			if ( ( $file != '.' ) AND ( $file != '..' ) AND ( $file != '.svn' ) ) {
				$themes[] = $file;
			}
		}
		closedir ( $handle );
	}
	return $themes;
}

function languagesinstalled () {
	$languages = array ();
	array_push ($languages,'english');
	$dir = "lang/";
	if ( $handle = opendir ( $dir ) ) {
		while ( false !== ( $file = readdir ( $handle ) ) ) {
			if ( ( $file != '.' ) AND ( $file != '..' ) AND ( $file != '.svn' ) ) {
				array_push ( $languages,$file );
			}
		}
		closedir ( $handle );
	} 
	return $languages;
}

function isinstalled () {
	if ( file_exists ( 'site.config.php' ) ) {
		if ( count ( databasesinstalled () ) != 0 ) {
			if ( count ( languagesinstalled () ) != 0 ) {
				return true;
			} else {
				return false;
			}
		} else {
			 return false;
		}
	} else {
		return false;
	}
}

function formatDate ($time) {
	global $config;
	$timezone = $config->getConfigByNameType ('general/timezone',TYPE_INT);
	$time = $time + $timezone * 60 * 60;
	$timeformat = $config->getConfigByNameType ('general/timeformat',TYPE_STRING);
	return date ($timeformat,$time);
}

function getUTCtime (&$config = NULL) {
	$time = time ();
	$UTCtime = $time - (date ('Z'));
	return $UTCtime;
}

function catch_error ($exc,$link,$message,$moreinf) {
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

function init () {
	session_start ();
	// check for PHP version
	$req = '5.0.0';
	if (version_compare ($req,phpversion(),'>=')) {
		die ('PHP Version ' . $req .' or higher is required');
	}
	include_once ('kernel/exception.class.php');
	include ('kernel/constants.php');
	global $skin; 
	include ('kernel/database.class.php');
	include ('kernel/skin.class.php');
	$skin = new CSkin ();
}
?>
