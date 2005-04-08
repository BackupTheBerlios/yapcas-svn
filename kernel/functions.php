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
	class lang {
		function updatelang ( $l ) {
			include ('lang/'. $l .'/news.lang.php');
			include ('lang/'. $l .'/users.lang.php');
			include ('lang/'. $l .'/site.lang.php');
			include ('lang/'. $l .'/database.lang.php');
			include ('lang/'. $l .'/polls.lang.php');
		}
	}

	function loadlang ( $language ) {
		$l = new lang ();
		$l->updatelang ( $language );
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
					array_push ( $databases,$file );
				}
			}
			closedir ( $handle );
		} 
		return $databases;
	}
	
	function languagesinstalled () {
		$languages = array ();
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
		$tables = $database->get_all_tables ();
		if ( in_array ( 'news',$tables ) AND in_array ( 'users',$tables ) AND in_array ( 'categories',$tables ) AND in_array ( 'ipblocks',$tables ) AND in_array ( 'comments',$tables ) AND in_array ( 'pages',$tables ) ) {
			return true;
		} else {
			return false;
		}
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
	
	function loaddbclass ( $dbtype ) {
		switch ( $dbtype ) {
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
	
	function error_handling ( $error,$baselinksucceed,$baselinkfailed,$messagesucceed,$messagefailed ) {
			global $database;
			switch ( $error->succeed ) {
				case false:
					$link = $baselinkfailed;
					switch ( $error->fatal ) {
						case true:
							$link .= '&error=' . $messagefailed . ': ' .  $error->error;
							break;
						case false:
							$link .= '&warning=' . $error->error . '&note=' . $messagesucceed;
							break;
					} 
					break;
				case true:
					$link = $baselinksucceed;
					$link .= '&note=' . $messagesucceed;
					break;
			}
			return $link;
	}
	
	function loadall () {	  
		session_start ();
		include_once ( 'kernel/themes.class.php' );	
		include ( 'kernel/constants.php' );	
		global $theme; 
		$theme = new theme ();		
	}
	
	function setdate ( $time ) {
		global $user;
		$timezone = $user->gettimezone ();
		$time = $time + ( $timezone * 60 * 60 );
		$timeformat = $user->gettimeformat ();
		return date ( $timeformat,$time );
	}
	
	function getUTCtime () {
		global $config;
		$sitetimezone = $config->site->timezone;
		$time = time ();
		$UTCtime = $time - ( $sitetimezone * 60 * 60 );
		return $UTCtime;
	}
	
	class error {
		function error () {
			$this->database = false;
			$this->error = NULL;
			$this->succeed = true; 
			$this->value = NULL;
			$this->fatal = true;
		} 
	}
	
?>
