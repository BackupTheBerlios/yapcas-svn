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
include ('kernel/skin.class.php');
$skin = new CSkin ();

if ( ! empty ( $_GET['action'] ) ) {
	$action = $_GET['action'];
} else {
	$theme->redirect ('index.php');
}

try {
	$errorrep = $config->getConfigByNameType ('general/errorreporting',TYPE_INT);
}
catch (exceptionlist $e) {
	// this is a big errror so $errorep = true
	$link = $skin->catchError ($e,'index.php?',$lang->translate ('Your action has no effect'),true);
	$database->close ();
	$skin->redirect ($link);
}
switch ($action) {
	case 'vote':
		try {
			$return = $poll->vote ($_POST,$user->getconfig ('name'));
			$database->close ();
			$skin->redirect ('index.php?note='.$lang->translate ('You have voted'));
			break;
		}
		catch (exceptionlist $e) {
			$skin->redirect ($skin->catchError ($e,'index.php?',$lang->translate ('Your vote is not saved'),$errorrep));
		}
	case 'allpolls':
		try {
			$skin->loadSkinFile ('viewpolls.html');
		}
		catch (exceptionlist $e) {
			$skin->redirect ($skin->catchError ($e,'index.php?',$lang->translate ('Your action is not accepted'),$errorrep));
		}
		break;
	default: 
		$skin->redirect ('index.php');
}
?>
