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
include_once ('kernel/database.class.php');
include_once ('kernel/exception.class.php');
include_once ('kernel/language.class.php');
include_once ('kernel/config.class.php');
$database = new CDatabase ();
function replacedbtypes ($databases) {
	$option['open'] = '<select name="databasetype">';
	$option['close'] = '</select>';
	$option['option']  = '<option>%option</option>';
	$output = $option['open'];
	foreach ($databases as $name => $db) {
		$output .= $option['option'];
		$output = ereg_replace ('%option',$name,$output);
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
	$output = ereg_replace ('%database.options',replacedbtypes ($database->getAllSupportedDatabases ()),$output);
	echo $output;
} else if ($_POST['submit'] == 'install') {
	if (empty ($POST['activatemail'])) {
		header ('install.php');
	}
	// install config script
	$config = '<?php' . NL . TAB;
	//$config .= '// general';
	$config .= "\$config['general']['servertimezone'] = $_POST[servertimezone];" . NL . TAB;
	$config .= "\$config['general']['timezone'] = 0;" . NL . TAB;
	$config .= "\$config['general']['timeformat'] = 'H:i:s d/m/y';" . NL . TAB;
	$config .= "\$config['general']['sitename'] = '$_POST[sitename]';" . NL . TAB;
	$config .= "\$config['general']['description'] = '$_POST[description]';" . NL . TAB;
	$config .= "\$config['general']['language'] = '$_POST[language]';" . NL . TAB;
	$config .= "\$config['general']['langcode'] = 'en';" . NL . TAB;
	$config .= "\$config['general']['theme'] = 'moderngray';" . NL . TAB;
	$config .= "\$config['general']['databasetype'] = $_POST[databasetype];" . NL . TAB;
	$config .= "\$config['general']['webmastermail'] = '$_POST[webmastermail]';" . NL . TAB;
	$config .= "\$config['general']['errorreporting'] = E_NONE;" . NL . TAB;
	//$config .= '// database';
	$config .= "\$config['database']['host'] = '$_POST[databasehost]';" . NL . TAB;
	$config .= "\$config['database']['user'] = '$_POST[databaseuser]';" . NL . TAB;
	$config .= "\$config['database']['password'] = '$_POST[databasepassword]';" . NL . TAB;
	$config .= "\$config['database']['name'] = '$_POST[databasename]';" . NL . TAB;
	$config .= "\$config['database']['tblprefix'] = '$_POST[databaseprefix]';" . NL . TAB;
	//$config .= '// news';
	$config .= "\$config['news']['headlines'] = 10;" . NL . TAB;
	$config .= "\$config['news']['postsonpage'] = 5;" . NL . TAB;
	$config .= "\$config['news']['threaded'] = true;" . NL . TAB;
	//$config .= "// user" . NL . TAB;
	$activatemail = $_POST['activatemail'] . NL . TAB;
	if ($activatemail == YES) {
		$activatemail = 'true' . NL . TAB;
	} elseif ($activatemail == NO) {
		$activatemail = 'false' . NL . TAB;
	} else {
		// Can never happen
		$activatemail = 'true'  . NL . TAB;
	}
	$config .= "\$config['user']['activatemail'] = $activatemail;" . NL;
	$config .= '?>';
	$handle = fopen ('site.config.php','w');
	fwrite ($handle,$config);
	fclose ($handle);
	// do dbstuff
	try {
		$config = new CConfig ();
		$d = $database->load ($_POST['databasetype']);
		$d->connect ($_POST['databasehost'],$_POST['databaseuser'],$_POST['databasepassword'],$_POST['databasename']);
		define ('TBL_PREFIX',$_POST['databaseprefix']);
		$queries = file_get_contents ('kernel/sql/users.sql');
		$queries .= file_get_contents ('kernel/sql/news.sql');
		$queries .= file_get_contents ('kernel/sql/polls.sql');
		$queries .= file_get_contents ('kernel/sql/basic.sql');
		$queries .= file_get_contents ('kernel/sql/help.sql');
		// TODO
		include_once ('kernel/language.class.php');
		$languages = new CLang ();
		foreach ($languages->installed () as $language) {
			echo $language;
			$languages->update ($language);
			$tmpquery = file_get_contents ('kernel/sql/basicpages.sql');
			$tmpquery = ereg_replace ('%language%',$language,$tmpquery);
			$tmpquery = ereg_replace ('%shown_logout%',$languages->translate ('logout'),$tmpquery);
			$tmpquery = ereg_replace ('%shown_index%',$languages->translate ('Home'),$tmpquery);
			$tmpquery = ereg_replace ('%shown_registerform%',$languages->translate ('Register'),$tmpquery);
			$tmpquery = ereg_replace ('%shown_sendpasswordform%',$languages->translate ('Lost Password'),$tmpquery);
			$tmpquery = ereg_replace ('%shown_changeoptionsform%',$languages->translate ('Edit Settings'),$tmpquery);
			$tmpquery = ereg_replace ('%shown_viewuserlist%',$languages->translate ('Userlist'),$tmpquery);
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
				$d->query ($query);
			}
		}
		include_once ('kernel/users.class.php');
		$user = new CUser ($d,false,$lang);
		//$user->register ();
		$d->close ();
	}
	catch (exceptionlist $e) {
		echo $e->getMessage ();
		echo $e->debuginfo;
	}
	echo '<b>delete install.php now</b>';
} else if ($_POST['submit'] == 'testdbconn') {
	echo 'test de connectie';
} else {
	die ('error in POST');
}
?>
