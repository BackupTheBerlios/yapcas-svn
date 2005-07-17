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
// check for PHP version
if (! PHP_VERSION >= 5) {
	die ('PHP Version 5 or higher is required');
}
if (!defined ('EXCEPTION_CLASS')) {
	include ('kernel/exception.class.php');
}
include ('kernel/constants.php');

function checkDatabase ($database,$tables) {
	// TODO
	return true;
}

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
				array_push ( $themes,$file );
			}
		}
		closedir ( $handle );
	} 
	return $themes;
}

function databasesinstalled () {
	$databases = array ();
	$dir = "kernel/databases/";
	if ( $handle = opendir ( $dir ) ) {
		while ( false !== ( $file = readdir ( $handle ) ) ) {
			if ( ( $file != '.' ) AND ( $file != '..' ) AND ( $file != '.svn' ) ) {
				$file = preg_replace ('#(.+?)\.database\.php#','\\1',$file);
				array_push ( $databases,$file );
			}
		}
		closedir ( $handle );
	} 
	return $databases;
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

function databasecheck ( $database ) {
	/*$tables = $database->get_all_tables ();
	if ( in_array ( 'news',$tables ) AND in_array ( 'users',$tables ) AND in_array ( 'categories',$tables ) AND in_array ( 'ipblocks',$tables ) AND in_array ( 'comments',$tables ) AND in_array ( 'pages',$tables ) ) {
		return true;
	} else {
		return false;
	}*/
	return true;
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

function loaddbclass ($dbtype) {
	switch ($dbtype) {
		case MySQL4: 
			include_once ('kernel/databases/mysql4.database.php');
			break;
		case MySQL3:
			include_once ('kernel/databases/mysql4.database.php');
			// if I see that there are incompatabilities with MySQL4 <-> MySQL3 i will fix them
			// on the moment is MySQL3 unsupported because I haven't a MySQL3 server 
			break;
		case PostgreSQL:
			include_once ('kernel/databases/postgresql.database.php');
			break;
		default:
			echo 'SELECT a database type, default is used';
			include_once ('kernel/databases/mysql4.database.php');
	}
}

function error_handling ( $errors,$baselinksucceed,$baselinkfailed ) {
		foreach ( $errors as $error ) {
			if ( $error->succeed == true ) {
				array_unshift ( $errors,$error ); // be sure that succeed messafe is at begin
				break;
			}
		}
		unset ($link); // be sure $link is not set
		foreach ( $errors as $error ) {
			if ( isset ( $link ) ) {
				if ( $error->succeed == true ) {
					$link .= '&note=' . $error->message;
				} else {
					if ( $error->fatal == false ) {
						$link .= '&warning=' . $error->error;
					} else {
						$link .= '&error=' . $error->error;
					}
				}
			} else {
				if ( $error->succeed == true ) {
					$link = $baselinksucceed;
					$link .= '&note=' . $error->message;
				} else {
					if ( $error->fatal == false ) {
						// should never happen since there is a succeed message, 
						// $link will be set
						//FIXME: give errors to log
						$link = $baselinksucceed;
						$link .= '&warning=' . $error->error;
					} else {
						$link = $baselinkfailed;
						$link .= '&error=' . $error->error;
					}
				}
			}
			$link = $link . '&errorid=' . $error->number;
		}
		header ( 'Location: ' . $link );
		return $link;
}

function loadall () {
	session_start ();
	include_once ( 'kernel/themes.class.php' );
	include ( 'kernel/constants.php' );
	global $theme; 
	$theme = new theme ();
}

function setdate ($time) {
	global $config;
	$timezone = $config->getConfigByNameType ('general/timezone',TYPE_INT);
	$timeme = $time + $timezone * 60 * 60;
	$timeformat = $config->getConfigByNameType ('general/timeformat',TYPE_STRING);
	return date ($timeformat,$time);
}

function getUTCtime (&$config) {
	try {
		$sitetimezone = $config->getConfigByNameType ('general/servertimezone',TYPE_INT);
		$time = time ();
		$UTCtime = $time - ($sitetimezone * 60 * 60);
		return $UTCtime;
	}
	catch (exceptionlist $e) {
		throw $e;
	}
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
?>
