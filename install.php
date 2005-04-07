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
	if ( empty ( $_POST ) ) {
		include_once ( 'kernel/constants.php' );	
		include ( 'themes/moderngray/install.html' );
	} else {
		include_once ( 'kernel/functions.php' );	
		if ( empty ( $_POST['language'] ) ) {
			$language = 'english';
		} else {
			$language = $_POST['language'];
		}
		global $lang;
		$lang = loadlang ( $language );
		// create config-file
		$page = '
			&lt;?php
			$this->safety->error_reporting = E_NONE;<br />
			
			$this->database->type = ' . $_POST['type'] . '; <br />
			$this->database->host = \''. $_POST['host'] .'\';<br />
			$this->database->user = \'' . $_POST['user'] .'\';<br />
			$this->database->password = \'' . $_POST['password'] . '\';<br />
			$this->database->name = \'' . $_POST['name'] .'\';<br />
				
			$this->news->enable_threaded = true;<br />
			$this->news->standard->view = threaded;<br />
			$this->news->standard->postsonpage = 20;<br />
	
		
			$this->site->timezone = +2;	<br />
			$this->site->timeformat = \'H:i:s d/m/Y\';		<br />
			$this->site->name = \'YaPC(a)S\';<br />
			$this->site->description = NULL;<br />
			$this->site->copyright = \'&copy; 2004-2005 Nathan Samson\';<br />
			$this->site->language = \'dutch\';<br />
			$this->site->theme = \'moderngray\';<br />
			$this->news->postsonpage = 10;<br />
			$this->news->headlines = 20;<br />
			$this->users->activate = false;<br />
			?&gt;
		';
			echo $page;
			echo '<br />--------------------------------------------<br />';
			echo 'Installed, copy above text in site.config.php';
			
			loaddbclass ( $_POST['type'] );
			$config->safety->error_reporting = E_ALL;
			$config->database->host = $_POST['host'];
			$config->database->user = $_POST['user'];
			$config->database->password = $_POST['password'];
			$config->database->name = $_POST['name'];
			$database = new database ( $config );
			$error = $database->connect ();
			$query =
				"CREATE TABLE categories (
				name varchar(50) NOT NULL,
				language varchar(50) NOT NULL,
				description text,
				image varchar(255),
				alternate varchar(255),
				PRIMARY KEY  (name,language)
			)";
			$error = $database->query ( $query );
			echo $error->error;
			
			$query="
				CREATE TABLE comments (
				id serial NOT NULL,
				subject varchar(255) NOT NULL,
				message text NOT NULL,
				author varchar(75) NOT NULL,
				date int NOT NULL,
				id_news int NOT NULL,
				comment_on_news varchar(1) NOT NULL default 'Y',
				id_on_comment int,
				PRIMARY KEY  (id)
			)";
			$error = $database->query ( $query );
			echo $error->error;
			
			$query="
				CREATE TABLE ipblocks (
				ip varchar(22) NOT NULL,
				date int NOT NULL,
				reason text NOT NULL,
				PRIMARY KEY  (ip)
			)";
			$error = $database->query ( $query );
			echo $error->error;
			
			$query="
				CREATE TABLE news (
				id serial NOT NULL,
				subject varchar(50) NOT NULL,
				message text NOT NULL,
				language varchar(50) NOT NULL,
				comments int default '0',
				author varchar(255) NOT NULL,
				date int NOT NULL,
				category varchar(50) NOT NULL,
				PRIMARY KEY (id)
			)";
			$error = $database->query ( $query );
			echo $error->error;
			
			$query="
				CREATE TABLE pages (
				name varchar(250) NOT NULL,
				language varchar(35) NOT NULL,
				content text,
				shown_name varchar(30) NOT NULL,
				show_in_nav varchar(1) NOT NULL default 'Y',
				PRIMARY KEY  (name,language)
			)";
			$error = $database->query ( $query );
			echo $error->error;
			
			$query="
				CREATE TABLE users (
				name varchar(255) NOT NULL,
				password varchar(32) NOT NULL,
				email varchar(50) UNIQUE NOT NULL,
				type varchar(50) NOT NULL default 'users',
				language varchar(50),
				theme varchar(50),
				ip text NOT NULL,
				threaded varchar(1),
				postsonpage int,
				timezone int,
				timeformat varchar(15),
				headlines int,
				activated varchar(1) NOT NULL default 'Y',
				blocked varchar(1) NOT NULL default 'N',
				PRIMARY KEY  (name)
			)";
			$error = $database->query ( $query );
			echo $error->error;
			
			foreach ( languagesinstalled() as $language ) {
				$lang = loadlang ( $language );
				$query = "INSERT INTO pages (name,language,shown_name) VALUES ('index.php','". $language ."','". $lang->site->index ."')";
				$error = $database->query ( $query );
				echo $error->error;
				unset ( $lang );
			}
	}
?>