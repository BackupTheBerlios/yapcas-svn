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
	include ('kernel/functions.php');
	loadall ();
	
	if ( ! empty ( $_GET['action'] ) ) {
		$action = $_GET['action'];
	} else {
		$theme->redirect ( 'index.php' );
	}
	
	switch ( $action ) {
		case 'vote':
			$return = $GLOBALS['poll']->vote ( $_POST );
			$GLOBALS['database']->close ();
			$GLOBALS['theme']->redirect ( error_handling ( $return,'index.php?','index.php?',$lang->polls->voted,$lang->polls->not_voted ) );
			break;
			
		case 'allpolls':
			$GLOBALS['theme']->themefile ( 'viewpolls.html' );
			break;	
		
		default: 
			$theme->redirect ( 'index.php' );
	}
?>