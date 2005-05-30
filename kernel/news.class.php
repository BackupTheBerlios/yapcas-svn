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
	public function __construct ($database,$user,&$config) {
		include ('kernel/news.constants.php');
		$this->database = $database;
		$this->user = $user;

		$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
			array('headlines',$this->user,'headlines','site.config.php'),
			'news/headlines',TYPE_INT);
		$config->addConfigByList ('GET;YAPCAS_USER;COOKIE;FILE',
			array('postsonpage',$this->user,'postsonpage','site.config.php'),
			'news/postsonpage',TYPE_INT);
		$this->config = $config;
	} /* public function __construct ($database,$user,&$config) */

	public function headlines ($outputmethod,$category = NULL) {
		try {
			$limit = $this->config->getConfigByNameType ('news/headlines',TYPE_INT);
			$language = $this->config->getConfigByNameType ('general/language',TYPE_STRING);
			$fields = array (FIELD_NEWS_ID,FIELD_NEWS_SUBJECT,FIELD_NEWS_MESSAGE,
				FIELD_NEWS_AUTHOR,FIELD_NEWS_DATE,FIELD_NEWS_CATEGORY);
			$strfields = implode (',',$fields);
			$sql = 'SELECT ' . $strfields . ' FROM ' . TBL_NEWS;
			$sql .= ' WHERE ' . FIELD_NEWS_LANGUAGE . '=\'' . $language . '\'';
			if (! empty ($category)) {
				$sql .= ' AND ' . FIELD_NEWS_CATEGORY . '=\'' . $category . '\'';
			}
			$sql .= 'ORDER by ' . FIELD_NEWS_DATE . ' desc LIMIT ' . $limit;
			$query = $this->database->query ($sql);
			$return = array ();
			while ($headline = $this->database->fetch_array ($query)) {
				switch ($outputmethod) {
					case 'rss':
						// FIXME TODO
						// $this->rss ();
						break;
					case 'show':
						array_push ($return,$headline);
						break;
				}
			}
			return $return;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function headlines ($outputmethod,$category = NULL) */

	public function getLimitNews ($offset = NULL,$category = NULL) {
		try {
			if (empty ($offset)) {
				$limit['offset'] = 0;
			} else {
				$limit['offset'] = $offset;
			}
			$language = $this->config->getConfigByNameType('general/language',TYPE_STRING);
			$limit['limit'] =
				$this->config->getConfigByNameType ('news/postsonpage',TYPE_INT);
			$postsonpage = $this->config->getConfigByNameType (
				'news/postsonpage',TYPE_INT);
			$sql = 'SELECT ' . FIELD_NEWS_ID . ' FROM ' . TBL_NEWS;
			$sql .= ' WHERE ' . FIELD_NEWS_LANGUAGE . '=\'' . $language . '\'';
			if (! empty ($category)) {
				$sql .= ' AND category=\'' . $category . '\'';
			}
			$query = $this->database->query ($sql);
			$limit['total'] = $this->database->num_rows ($query);
			$limit['previous'] = $limit['offset'] - $limit['limit'];
			$limit['next'] = $limit['offset'] + $limit['limit'];
			return $limit;
		}
		catch (exceptionlist $e) {
			echo $e->debuginfo;
			throw $e;
		}
	} /* public function getLimitNews ($offset = NULL,$category = NULL) */

	public function getLimitComments ($newsid,$offset = NULL) {
		try {
			if (empty ($offset)) {
				$limit['offset'] = 0;
			} else {
				$limit['offset'] = $offset;
			}
			$language = $this->config->getConfigByNameType('general/language',TYPE_STRING);
			$limit['limit'] =
				$this->config->getConfigByNameType ('news/postsonpage',TYPE_INT);
			$postsonpage = $this->config->getConfigByNameType (
				'news/postsonpage',TYPE_INT);
			$sql = 'SELECT ' . FIELD_NEWS_ID . ' FROM  ' . TBL_COMMENTS;
			$sql .= ' WHERE ' . FIELD_COMMENTS_ID_NEWS . '=\'' . $newsid . '\'';
			$query = $this->database->query ($sql);
			$limit['total'] = $this->database->num_rows ($query);
			$userpostsonpage = $this->config->getConfigByNameType('news/postsonpage',TYPE_INT);
			$limit['previous'] = $limit['offset'] - $userpostsonpage;
			$limit['next'] = $limit['offset'] + $userpostsonpage;
			return $limit;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function getLimitComments ($offset = NULL,$category = NULL) */

	public function showallnews ($offset = NULL,$category = NULL) {
		try {
			$limit = $this->getLimitNews ($offset,$category);
			$language = $this->config->getConfigByNameType ('general/language',TYPE_STRING);
			$sql = 'SELECT * FROM ' . TBL_NEWS . ' INNER JOIN ' . TBL_CATEGORIES;
			$sql .= ' ON (' . TBL_NEWS . '.' . FIELD_NEWS_CATEGORY . '=';
			$sql .= TBL_CATEGORIES . '.' . FIELD_CATEGORIES_NAME . ')';
			$sql .= 'WHERE ' . TBL_NEWS . '.' . FIELD_NEWS_LANGUAGE . '=\'' . $language . '\'';
			if (! empty ($category)) {
				$sql .= ' AND ' . FIELD_NEWS_CATEGORY . '=\'' . $category . '\' ';
			}
			$sql .= ' ORDER by ' . FIELD_NEWS_DATE . ' desc ';
			$sql .= 'LIMIT ' . $limit['limit'] . ' OFFSET ' . $limit['offset'];
			$query = $this->database->query ($sql);
			// $i = 0;
			$output = array ();
			while ($news = $this->database->fetch_array ($query)) {
				array_push ($output,$news);
			}
			return $output;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function showallnews ($offset = NULL,$category = NULL) */

	public function getthreadfollows ($idnews,$comment) {
		try {
			$sql = 'SELECT * FROM ' . TBL_COMMENTS;
			$sql .= ' WHERE id_news=\'' . $idnews .'\'';
			$sql .= ' AND ' . FIELD_COMMENTS_ID_ON_COMMENT . '=\'' . $comment[FIELD_COMMENTS_ID] . '\'';
			$sql .= ' AND ' . FIELD_COMMENTS_ID_NEWS . '=\'' . NO .'\'';
			$sql .= ' ORDER by ' . FIELD_COMMENTS_DATE . ' asc';
			$query = $this->database->query ($sql);
			$comments = array ();
			while ($comment_on_comment = $this->database->fetch_array ($query)) {
				array_push ($comments,$comment_on_comment);
			}
			return $comments;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function getthreadfollows ($idnews,$comment) */

	public function startthreads ($idnews) {
		try {
			$language = $this->config->getConfigByNameType('general/language',TYPE_STRING);
			$sql = 'SELECT * FROM ' . TBL_COMMENTS;
			$sql .= ' WHERE ' . FIELD_COMMENTS_ID_NEWS . '=\'' . $idnews . '\'';
			$sql .= ' AND ' . FIELD_COMMENTS_ON_NEWS . '=\''. YES . '\'';
			$sql .= 'ORDER by ' . FIELD_COMMENTS_DATE . ' asc';
			$query = $this->database->query ($sql);
			$comments = array ();
			while ($comment = $this->database->fetch_array ($query)) {
				array_push ($comments,$comment);
			}
			return $comments;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function startthreads ($idnews) */

	public function getcomment ($id) {
		try {
			$sql = 'SELECT * FROM ' . TBL_COMMENTS;
			$sql .= ' WHERE ' . FIELD_COMMENTS_ID . '=\'' . $id .'\'';
			$query = $this->database->query ($sql);
			return $this->database->fetch_array ($query);
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function getcomment ($id) */

	public function getallcomments ($idnews,$offset = NULL) {
		try {
			$language = $this->config->getConfigByNameType ('general/language',
				TYPE_STRING);
			$limit = $this->getLimitComments ($idnews,$offset);
			$sql = 'SELECT * FROM ' . TBL_COMMENTS;
			$sql .= 'WHERE ' . FIELD_COMMENTS_ID_NEWS . '=\'' . $idnews .'\'';
			$sql .= ' ORDER by ' . FIELD_COMMENTS_DATE . ' asc ';
			$sql .= 'LIMIT ' . $limit['limit'] . ' OFFSET ' . $limit['offset'];
			$query = $this->database->query ($sql);
			$comments = array ();
			while ( $comment = $this->database->fetch_array ( $query) ) {
				array_push ( $comments, $comment );
			}
			return $comments;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function getallcomments ($idnews,$offset = NULL) */

	public function getnews ($id) {
		try {
			$sql = 'SELECT * FROM ' . TBL_NEWS;
			$sql .= ' WHERE ' . FIELD_NEWS_ID . '=\''. $id .'\'';
			$query = $this->database->query ($sql);
			return $this->database->fetch_array ($query);
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function getnews ($id) */

	public function postnews ($message,$subject,$category,$date,$language,$author) {
		try {
			$sql = 'INSERT into ' . TBL_NEWS;
			$fields = array (FIELD_NEWS_ID,FIELD_NEWS_MESSAGE,FIELD_NEWS_SUBJECT
				,FIELD_NEWS_LANGUAGE,FIELD_NEWS_COMMENTS,FIELD_NEWS_AUTHOR,
				FIELD_NEWS_DATE,FIELD_NEWS_CATEGORY);
			$strfields = implode (',',$fields);
			$sql .= '(' . $strfields . ')';
			$content = array ('\'DEFAULT\'','\''.$message.'\'','\''.$subject.'\'',
				'\''.$language.'\'','\'0\'','\''.$author.'\'','\''.$date.'\'',
				'\''.$category.'\'');
			$strcontent = implode (',',$content);
			$sql .= ' values (' . $strcontent . ')';
			$query = $this->database->query ($sql);
			return true;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function postnews ($message,$subject,$category,$date,$author) */

	public function editcomment ($newmessage,$newsubject,$idcomment) {
		try {
			$sql = 'UPDATE ' . TBL_COMMENTS; 
			$sql .= ' SET ' . FIELD_NEWS_MESSAGE . '=\'' . $newmessage . '\'';
			$sql .= ',' . FIELD_NEWS_SUBJECT . '=\'' . $newsubject . '\'';
			$sql .= ' WHERE id=\'' . $idcomment . '\'';
			$query = $this->database->query ($sql);
			return true;
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function editcomment ($newmessage,$newsubject,$idcomment) */

	public function postcomment ($message,$subject,$date,$user,$onnews,$oncomment = 0) {
		try {
			if ($oncomment == 0) {
				$comment_on_news = YES;
			} else {
				$comment_on_news = NO;
			}
			$sql = 'INSERT into ' . TBL_COMMENTS;
			$fields = array (FIELD_COMMENTS_ID,FIELD_COMMENTS_MESSAGE,
				FIELD_COMMENTS_SUBJECT,FIELD_COMMENTS_AUTHOR,FIELD_COMMENTS_DATE,
				FIELD_COMMENTS_ID_NEWS,FIELD_COMMENTS_ON_NEWS,
				FIELD_COMMENTS_ID_ON_COMMENT);
			$strfields = implode (',',$fields);
			$sql .= ' (' . $strfields . ')';
			$content = array ('\'DEFAULT\'','\''.$message.'\'','\''.$subject.'\''
				,'\''.$user.'\'','\''.$date.'\'','\''.$onnews.'\'',
				'\''.$comment_on_news.'\'','\''.$oncomment.'\'');
			$strcontent = implode (',',$content);
			$sql .= 'values (' . $strcontent . ')';
			$query = $this->database->query ($sql);
			// comment is posted now updating the news #comments
			// select the news
			$sql = 'SELECT ' . FIELD_NEWS_COMMENTS . ' FROM ' . TBL_NEWS;
			$sql .= ' WHERE ' .  FIELD_NEWS_ID . '=\'' . $onnews . '\'';
			$query = $this->database->query ($sql,false); // Not fatal
			$news = $this->database->fetch_array ($query);
			$curcomments = $news[FIELD_NEWS_COMMENTS];
			// now put the new value in the newsdb;
			$sql = 'UPDATE ' . TBL_NEWS;
			$sql .= ' set ' . FIELD_NEWS_COMMENTS . '=\'' . $curcomments++ . '\'';
			$sql .= ' WHERE ' . FIELD_NEWS_ID . '=\''  . $onnews . '\'';
			$sql .= ' LIMIT 1';
			$query = $this->database->query ($sql,false); // not fatal
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* public function postcomment ($message,$subject,$date,$user,$onnews,$oncomment = 0) */
} // Class news
?>
