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
	class news {	
		function news ( $database,$user ) {
			include ( 'kernel/news.constants.php' );
			$this->database = $database;
			$this->user = $user;
		} // function news
		
		function headlines ( $output ) {
			$limit = $this->user->getheadlines ();
			$language = $this->user->getlanguage ();
			if ( ! empty ( $_GET['category'] ) ) {
				$category = $_GET['category'];
				$sql = "SELECT id,subject, message, author, date, category FROM news WHERE category='$category' AND language='$language' ORDER by date desc LIMIT $limit";
			} else {
				$category = NULL;
				$sql = "SELECT id,subject, message, author, date, category FROM news WHERE language='$language' ORDER by date desc LIMIT $limit";
			}
			$query = $this->database->query ( $sql );
			if ( ! errorSDK::is_error ( $query ) ) {
				$return = array ();
				while ( $headline =  $this->database->fetch_array ( $query ) ) {
					switch ( $output ) {
						case 'rss':
							$this->rss ();
							break;
						case 'show': 
							array_push ( $return,$headline);
							break;
					}
				}
				return $return;
			} else {
				return $query;
			}
		} // function headlines
		
		function getlimit ( $where ) {
			if ( empty ( $_GET['offset'] ) ) {
				$limit['offset'] = 0;
			} else {
				$limit['offset'] = $_GET['offset'];
			}
			switch ( $where ) {
				case 'allnews': 
					$language = $this->user->getlanguage ();
					$limit['limit'] = $this->user->getpostsonpage ();
					if ( ! empty ( $_GET['category'] ) ) {
						$sql = "SELECT id FROM news WHERE language='$language' AND category='$_GET[category]'";
					} else {
						$sql = "SELECT id FROM news WHERE language='$language'";
					}
					$query = $this->database->query ( $sql );
					if ( errorSDK::is_error ( $query ) ) {
						return $query; // it is an error
					} else {
						$limit['total'] = $this->database->countresults ( $query );
						$userpostsonpage = $this->user->getpostsonpage ();
						$limit['previous'] = $limit['offset'] - $userpostsonpage;
						$limit['next'] = $limit['offset'] + $userpostsonpage;
						return $limit;
					}
					break;
				case 'comments': 
					$language = $this->user->getlanguage ();
					$limit_limit = $this->user->getpostsonpage ();
					$limit['limit'] = $limit_limit;
					$sql = "SELECT id FROM comments WHERE id_news='$_GET[id]'";
					$query = $this->database->query ( $sql );
					if ( errorSDK::is_error ( $query ) ) {
						return $query;
					} else {
						$limit['total'] = $this->database->num_rows ( $query );
						$userpostsonpage = $this->user->getpostsonpage ();
						$limit['previous'] = $limit['offset'] - $userpostsonpage;
						$limit['next'] = $limit['offset'] + $userpostsonpage;
						return $limit;
					}
					break;
			} // switch
		} // function getlimit
		
		function showallnews () {
			$limit = $this->getlimit ( 'allnews' );
			if ( errorSDK::is_error ( $limit ) ) {
				echo 'LIMIT' . $limit;
				return $limit;
			} else {
				$language = $this->user->getlanguage ();
				if ( ! empty ( $_GET['category'] ) ) {
					$sql = "SELECT * FROM news INNER JOIN categories ON ( news.category = categories.name ) WHERE news.language='$language' AND category='$_GET[category]' ORDER by date desc  LIMIT $limit[limit] OFFSET $limit[offset]";
				} else {
					$sql = "SELECT * FROM news INNER JOIN categories ON ( news.category = categories.name ) WHERE news.language='$language' ORDER by date desc LIMIT $limit[limit] OFFSET $limit[offset]";
				}
				$query = $this->database->query ( $sql );
				$i = 0;
				if ( errorSDK::is_error ( $query ) ) {
					return $query;
				} else {
					$output = array ();
					while ( $news = $this->database->fetch_array ( $query ) ) {
						array_push ( $output, $news );
					}   
					return $output;
				}
			}		
		} // function allnews
		
		
		function getthreadfollows ( $comment ) {
			$sql = "SELECT * FROM comments WHERE id_news='$_GET[id]' AND id_on_comment='$comment[id]' AND comment_on_news='0' ORDER by date asc";
			$query = $this->database->query ( $sql );
			if ( errorSDK::is_error ( $query ) ) {
				return $query;
			} else {
				$comments = array ();
				while ( $comment_on_comment = $this->database->fetch_array ( $query ) ) {
					array_push ( $comments, $comment_on_comment );
				}
				return $comments;
			}
		}
		
		function startthreads () {
			$language = $this->user->getlanguage ();
			$sql = "SELECT * FROM comments WHERE id_news='$_GET[id]' AND comment_on_news='1' ORDER by date asc";
			$query = $this->database->query ( $sql );
			if ( errorSDK::is_error ( $query ) ) {
				return $query;
			} else {
				$comments = array ();
				while ( $comment = $this->database->fetch_array ( $query) ) {
					array_push ( $comments, $comment );
				}
				return $comments;
			}
		}
		
		function getcomment ( $id ) {
			$sql = "SELECT * FROM comments WHERE id='" . $id ."'";
			$query = $this->database->query ( $sql );
			if ( errorSDK::is_error ( $query ) ) {
				return $error;
			} else {
				$comment = $this->database->fetch_array ( $query );
				return $comment;
			}
		}
		
		function getallcomments () {
			$language = $this->user->getlanguage ();
			$limit = $this->getlimit ( 'comments' );
			$sql = "SELECT * FROM comments WHERE id_news='$_GET[id]' ORDER by date asc LIMIT $limit[limit] OFFSET $limit[offset]";
			$query = $this->database->query ( $sql );
			if ( ! errorSDK::is_error ( $query ) ) {
				$comments = array ();
				while ( $comment = $this->database->fetch_array ( $query) ) {
					array_push ( $comments, $comment );
				}
				return $comments;
			} else {
				return $query;
			}
		}
		
		function getnews () {
			$sql = "SELECT * FROM news WHERE id='$_GET[id]'";
			$query = $this->database->query ( $sql );
			if ( errorSDK::is_error ( $query ) ) {
				return $query;
			} else {
				return $this->database->fetch_array ( $query );
			}
		}
			
		function postnews ( $inputuser ) {
			if ( $this->user->loggedin () ) {
				if ( ! ( ( empty ( $inputuser['subject'] ) ) or ( empty ( $inputuser['message'] ) ) or ( empty ( $_POST['category'] ) ) ) ) {
					$date = getUTCtime ();
					$language = $GLOBALS['user']->getlanguage ();
					$username = $GLOBALS['user']->getname ();
					$message = nl2br ( $_POST['message'] );
					$sql = "INSERT into news (id,subject,message,language,comments,author,date,category) values (DEFAULT,'$_POST[subject]','$message','$language','0','$username','$date','$_POST[category]') ";
					$query = $GLOBALS['database']->query ( $sql );
					if ( errorSDK::is_error ( $query ) ) {
						return $query;
					} else {
						return true;
					}
				} else {
					$error = new errorSDK ();
					$error->succeed = false;
					$error->error = $GLOBALS['lang']->users->form_not_filled_in;
					return $error;
				}
			} else {
				$error = new errorSDK ();
				$error->succeed = false;
				$error->error = $GLOBALS['lang']->users->not_logged_in;
				return $error;
			}
		}
			
		function editcomment ( $newvalues ) {
			$sql = "UPDATE " . TBL_COMMENTS . " SET ".  FIELD_NEWS_MESSAGE 
				. "='" . nl2br ( $newvalues['message'] ) . "' , " . FIELD_NEWS_SUBJECT . "='" 
				. $newvalues['subject'] . "' WHERE id='" . $_GET['id'] . "'";
			$query = $GLOBALS['database']->query ( $sql );
			if ( errorSDK::is_error ( $query ) ) {
				return $query;
			} else {
				return true;
			}
		}	
			
		function postcomment ( $input ) {
			if ( $GLOBALS['user']->loggedin () ) {
				if ( ! ( empty ( $input['subject'] ) ) or ( empty ( $input['message'] ) ) ) {
					// preparing the message and all of its data
					$date = getUTCtime ();
					$user = $_SESSION['name'];
					$message = nl2br ( $input['message'] ); // switch \n to <br />
					
					// this is bad code // make me better PLEASE
					// FIXME
					if ( $_POST['on_comment'] == 'NULL' ) {
						$comment_on_news = 1;
						$id_on_comment = 0;
					} else {
						$comment_on_news = 0;
						$id_on_comment = $_POST['on_comment'];
					}
					
					$sql = "INSERT into comments (id,subject, message, author, date, id_news
						, comment_on_news, id_on_comment) values (DEFAULT,'$input[subject]'
						,'$message','$user','$date','$input[on_news]','$comment_on_news'
						,'$id_on_comment') ";
					$query = $GLOBALS['database']->query ( $sql );
					if ( ! errorSDK::is_error ( $query ) ) {
						// comment is posted now updating the news #comments
						// select the news
						$sql = 'SELECT ' . FIELD_NEWS_COMMENTS . ' FROM ' . TBL_NEWS 
							. ' WHERE ' .  FIELD_NEWS_ID . '=' . $input['on_news'];
						$query = $GLOBALS['database']->query ( $sql,false ); // Not fatal
						if ( ! errorSDK::is_error ( $query ) ) {
							$news = $GLOBALS['database']->fetch_array ( $query );
							$curcomments = $news[FIELD_NEWS_COMMENTS];
							$newcomments = $curcomments + 1;
							// now put the new value in the newsdb;
							$sql = 'UPDATE ' . TBL_NEWS . ' set ' . FIELD_NEWS_COMMENTS 
								. "='" . $newcomments . "' WHERE " . FIELD_NEWS_ID 
								. "='"  . $input['on_news'] . "' LIMIT 1";
							$query = $GLOBALS['database']->query ( $sql,false ); 
								// not fatal
							if ( ! errorSDK::is_error ( $query ) ) {
								return true;
							} else {
								return $query;
							}
						} else {
							return $query;
						}
					} else {
						return $query;
					}
				} else {
					$error = new errorSDK ();
					$error->error = $GLOBALS['lang']->user->form_not_filled_in;
					return $error;
				}
			} else {
				$error = new errorSDK ();
				$error->error = $GLOBALS['lang']->user->not_logged_in;
				return $error;
			}
		}
	} // Class news		
?>
