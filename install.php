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
include ('kernel/functions.php');
include ('kernel/config.class.php');
if (! defined ('EXCEPTION_CLASS')) {
	include ('kernel/exception.class.php');
}

function replacedbtypes ($databases) {
	$option['open'] = '<select name="databasetype">';
	$option['close'] = '</select>';
	$option['option']  = '<option>%option</option>';
	$output = $option['open'];
	foreach ($databases as $db) {
		$output .= $option['option'];
		$output = ereg_replace ('%option',$db,$output);
	}
	$output .= $option['close'];
	return $output;
}

if (empty ($_POST['submit'])) {
	// show the install page
	if (file_exists ('site.config.php')) {
		if (! is_writable ('site.config.php')) {
			die ('make site.config.php writable');
		}
	} else {
		die ('create site.config.php first, be sure it is writable');
	}
	$output = file_get_contents ('install.html');
	$output = preg_replace ('#%(.+?)\.lang#','\\1',$output);
	$output = preg_replace ('#%(.+?)\.name#','\\1',$output);
	$output = ereg_replace ('%database.options',replacedbtypes (databasesinstalled ()),$output);
	echo $output;
} else if ($_POST['submit'] == 'install') {
	// install config script
	$config = '<?php ';
	//$config .= '// general';
	$config .= "\$config['general']['servertimezone'] = $_POST[servertimezone];";
	$config .= "\$config['general']['timezone'] = 0;";
	$config .= "\$config['general']['timeformat'] = 'H:i:s d/m/y';";
	$config .= "\$config['general']['sitename'] = '$_POST[sitename]';";
	$config .= "\$config['general']['description'] = '$_POST[description]';";
	$config .= "\$config['general']['language'] = '$_POST[language]';";
	$config .= "\$config['general']['langcode'] = 'en';";
	$config .= "\$config['general']['theme'] = 'moderngray';";
	$config .= "\$config['general']['databasetype'] = $_POST[databasetype];";
	$config .= "\$config['general']['webmastermail'] = '$_POST[webmastermail]';";
	$config .= "\$config['general']['errorreporting'] = E_NONE;";
	//$config .= '// database';
	$config .= "\$config['database']['host'] = '$_POST[databasehost]';";
	$config .= "\$config['database']['user'] = '$_POST[databaseuser]';";
	$config .= "\$config['database']['password'] = '$_POST[databasepassword]';";
	$config .= "\$config['database']['name'] = '$_POST[databasename]';";
	$config .= "\$config['database']['tblprefix'] = '$_POST[databaseprefix]';";
	//$config .= '// news';
	$config .= "\$config['news']['headlines'] = 10;";
	$config .= "\$config['news']['postsonpage'] = 5;";
	$config .= "\$config['news']['threaded'] = true;";
	//$config .= "// user";
	$activatemail = $_POST['activatemail'];
	convertToStandard ($activatemail);
	$config .= "\$config['user']['activatemail'] = '$activatemail' ;";
	$config .= ' ?>';
	$handle = fopen ('site.config.php','w');
	fwrite ($handle,$config);
	fclose ($handle);
	// do dbstuff
	try {
		$config = new config ();
		loaddbclass ($_POST['databasetype']);
		$database = new database ($config,'site.config.php');
		$database->connect ();
		$queries = file_get_contents ('kernel/sql/users.sql');
		$queries .= file_get_contents ('kernel/sql/news.sql');
		$queries .= file_get_contents ('kernel/sql/polls.sql');
		$queries .= file_get_contents ('kernel/sql/basic.sql');
		$queries .= file_get_contents ('kernel/sql/help.sql');
		// TODO
		$languages = languagesinstalled ();
		foreach ($languages as $language) {
			echo $language;
			$lang = loadlang ($language);
			$tmpquery = file_get_contents ('kernel/sql/basicpages.sql');
			$tmpquery = ereg_replace ('%language%',$language,$tmpquery);
			$tmpquery = ereg_replace ('%shown_logout%',$lang->translate ('logout'),$tmpquery);
			$tmpquery = ereg_replace ('%shown_index%',$lang->translate ('Home'),$tmpquery);
			$tmpquery = ereg_replace ('%shown_registerform%',$lang->translate ('Register'),$tmpquery);
			$tmpquery = ereg_replace ('%shown_sendpasswordform%',$lang->translate ('Lost Password'),$tmpquery);
			$tmpquery = ereg_replace ('%shown_changeoptionsform%',$lang->translate ('Edit Settings'),$tmpquery);
			$tmpquery = ereg_replace ('%shown_viewuserlist%',$lang->translate ('Userlist'),$tmpquery);
			$queries .= $tmpquery;
		}
		$queries = ereg_replace ('%show_logout_in_nav%',Yes,$queries);
		$queries = ereg_replace ('%show_index_in_nav%',Yes,$queries);
		$queries = ereg_replace ('%show_registerform_in_nav%',No,$queries);
		$queries = ereg_replace ('%show_sendpasswordform_in_nav%',No,$queries);
		$queries = ereg_replace ('%show_changeoptionsform_in_nav%',Yes,$queries);
		$queries = ereg_replace ('%show_viewuserlist_in_nav%',Yes,$queries);
		$queries = ereg_replace ('%show_logout_in_user_nav%',Yes,$queries);
		$queries = ereg_replace ('%show_index_in_user_nav%',No,$queries);
		$queries = ereg_replace ('%show_registerform_in_user_nav%',No,$queries);
		$queries = ereg_replace ('%show_sendpasswordform_in_user_nav%',No,$queries);
		$queries = ereg_replace ('%show_changeoptionsform_in_user_nav%',Yes,$queries);
		$queries = ereg_replace ('%show_viewuserlist_in_user_nav%',Yes,$queries);
		$queries = ereg_replace ('%prefix%',$_POST['databaseprefix'],$queries);
		//$queries .= file_get_contents ('kernel/sql/basiccontent.sql');
		//$queries .= file_get_contents ('kernel/sql/helpcontent.sql');
		$queriesarray = explode (';',$queries);
		// FIXME; stupid hack
		// unset the last arrayvlue --> it is empty
		unset ($queriesarray[count ($queriesarray)+1]);
		foreach ($queriesarray as $query) {
			// if there are too many ';'
			// do not execute them
			$query = trim ($query);
			if (! empty ($query)) {
				echo $query . '<br />';
				$database->query ($query);
			}
		}
		$database->close ();
	}
	catch (exceptionlist $e) {
		//echo $e->getMessage ();
		echo $e->debuginfo;
	}
	echo '<b>delete install.php now</b>';
} else if ($_POST['submit'] == 'testdbconn') {
	echo 'test de connectie';
} else {
	die ('error in POST');
}
?>
