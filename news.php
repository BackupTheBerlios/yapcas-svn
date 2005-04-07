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
	loadall ( );
	
	if ( ! empty ( $_GET['action'] ) ) {
		$action = $_GET['action'];
	} else {
		$theme->redirect ( 'index.php' );
	}
	
	switch ( $action ) {
		case 'viewcomments': 
			$theme->themefile ( 'comments.html',false );
			break;
		
		case 'postcommentform':
			$theme->themefile ( 'postcommentform.html',true );
			break;
			
		case 'postcomment':
			$error = $news->postcomment ( $_POST );
			$theme->redirect ( error_handling ( $error,'index.php?','index.php?',$lang->news->comment_is_posted,$lang->news->comment_is_not_posted ) );
			break;
			
		case 'postnews':
			$error = $news->postnews ( $_POST );
			$theme->redirect ( error_handling ( $error,'index.php?','index.php?',$lang->news->news_is_posted,$lang->news->news_is_not_posted ) );
			break;
			
		case 'postnewsform':
			$theme->themefile ( 'postnewsform.html',true );
			break;
			
		case 'editcomment';		
			if ( ! empty ( $_POST ) ) {
				$error = $news->editcomment ( $_POST );
			} else {
				$error = new error ();
				$error->succeed = false;
				$error->error = $GLOBALS['lang']->users->form_not_filled_in;
			}
			$theme->redirect ( error_handling ( $error,'index.php?','index.php?',$lang->news->comment_is_edited,$lang->news->comment_is_not_edited ) );
			break;
			
		case 'editcommentform':
			$theme->themefile ( 'editcomment.html',true );
			break;
			
		default: 
			$theme->redirect ( 'index.php' );
	}
?>