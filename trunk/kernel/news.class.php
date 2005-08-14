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
/**
* File that take care of the News SubSystem
*
* @package news
*/
/**
* Class that take care off the news SubSystem
*
* @version 0.4cvs
*/
class CNews {
	/**
	 * constructor
	 *
	 * configures the class
	 * @param object $database the database class
	 * @param object $langUI the langUI class
	 * @param string $contentLang the language the user wants to see the news
	*/
	public function __construct ($database,$langUI,$contentLang) {
		include ('kernel/news.constants.php');
		$this->database = $database;
		$this->lang = $langUI;
		$this->contentLang = $contentLang;
	} /* public function __construct ($database,$langUI,$contentLang) */

	/**
	 * get Headlines fromt the database
	 *
	 * @param int $maxHeadlines the max number of headlines you want
	 * @param string $category the categoy of the headlines you want, if not all categories will be searched
	 * @todo multiple categories you want to search in
	 * @return array
	*/
	public function getHeadlines ($maxHeadlines,$category = NULL) {
		$fields = array (FIELD_NEWS_ID,FIELD_NEWS_SUBJECT,FIELD_NEWS_MESSAGE,
			FIELD_NEWS_AUTHOR,FIELD_NEWS_DATE,FIELD_NEWS_CATEGORY);
		$strfields = implode (',',$fields);
		$sql = 'SELECT ' . $strfields . ' FROM ' . TBL_NEWS;
		$sql .= ' WHERE ' . FIELD_NEWS_LANGUAGE . '=\'' . $this->contentLang . '\'';
		if (! empty ($category)) {
			$sql .= ' AND ' . FIELD_NEWS_CATEGORY . '=\'' . $category . '\'';
		}
		$sql .= ' ORDER by ' . FIELD_NEWS_DATE . ' desc LIMIT ' . $maxHeadlines;
		$query = $this->database->query ($sql);
		$return = array ();
		while ($headline = $this->database->fetch_array ($query)) {
			array_push ($return,$headline);
		}
		return $return;
	} /* public function getHeadlines ($maxHeadlines,$category = NULL) */

	/**
	 * post a Newsitem in the database
	 *
	 * @param string $message the message of the newsitem
	 * @param string $subject the subject of the newsitem
	 * @param string $category the category of the newsitem
	 * @todo a Newsitem can have more than one category
	 * @param int $date the date in Unix epoch
	 * @param string $contentLang the contentLang
	 * @param mixed $authorID the ID of the author
	 * @return bool
	*/
	public function postNews ($message,$subject,$category,$date,$contentLang,$authorID) {
		$sql = 'INSERT into ' . TBL_NEWS;
		$fields = array (FIELD_NEWS_MESSAGE,FIELD_NEWS_SUBJECT
			,FIELD_NEWS_LANGUAGE,FIELD_NEWS_COMMENTS,FIELD_NEWS_AUTHOR,
			FIELD_NEWS_DATE,FIELD_NEWS_CATEGORY);
		$strfields = implode (',',$fields);
		$sql .= '(' . $strfields . ')';
		$content = array ('\''.$message.'\'','\''.$subject.'\'',
			'\''.$contentLang.'\'','\'0\'','\''.$authorID.'\'','\''.$date.'\'',
			'\''.$category.'\'');
		$strcontent = implode (',',$content);
		$sql .= ' values (' . $strcontent . ')';
		$query = $this->database->query ($sql);
		return true;
	} /* public function postNews ($message,$subject,$category,$date,$contentLang,$authorID) */

	/**
	 * edit a news in the database
	 *
	 * @param string $newMessage the new message 
	 * @param string $newSubject the new subject
	 * @param int $IDNews the ID of the news wich is edited
	 * @todo edit the categories
	 * @return bool
	*/
	public function editNews ($newMessage,$newSubject,$IDNews) {
		$sql = 'UPDATE ' . TBL_NEWS; 
		$sql .= ' SET ' . FIELD_NEWS_MESSAGE . '=\'' . $newMessage . '\'';
		$sql .= ',' . FIELD_NEWS_SUBJECT . '=\'' . $newSubject . '\'';
		$sql .= ' WHERE id=\'' . $IDNews . '\'';
		$query = $this->database->query ($sql);
		return true;
	} /* public function editNews ($newMessage,$newSubject,$IDNews) */

	/**
	 * get a newsitem with a defined ID
	 *
	 * @param int $ID the ID of the newsitem
	 * @return array
	*/
	public function getNewsByID ($ID) {
		$sql = 'SELECT * FROM ' . TBL_NEWS;
		$sql .= ' WHERE ' . FIELD_NEWS_ID . '=\''. $ID .'\'';
		$query = $this->database->query ($sql);
		return $this->database->fetch_array ($query);
	} /* public function getNewsByID ($ID) */

	/**
	 * get all newsitems
	 *
	 * @param int $postsOnPage the max number of posts on one page
	 * @param int $offset the offset of the newsitems
	 * @param string $category the categoy of the headlines you want, if not all categories will be searched
	 * @todo multiple categories you want to search in
	 * @return array
	*/
	public function getAllNews ($postsOnPage,$offset = NULL,$category = NULL) {
		$limit = $this->getLimitNews ($postsOnPage,$offset,$category);
		$sql = 'SELECT * FROM ' . TBL_NEWS . ' INNER JOIN ' . TBL_CATEGORIES;
		$sql .= ' ON (' . TBL_NEWS . '.' . FIELD_NEWS_CATEGORY . '=';
		$sql .= TBL_CATEGORIES . '.' . FIELD_CATEGORIES_NAME . ')';
		$sql .= 'WHERE ' . TBL_NEWS . '.' . FIELD_NEWS_LANGUAGE . '=\'' . $this->contentLang . '\'';
		if (! empty ($category)) {
			$sql .= ' AND ' . FIELD_NEWS_CATEGORY . '=\'' . $category . '\' ';
		}
		$sql .= ' ORDER by ' . FIELD_NEWS_DATE . ' desc ';
		$sql .= 'LIMIT ' . $limit['limit'] . ' OFFSET ' . $limit['offset'];
		$query = $this->database->query ($sql);
		$allNewsItems = array ();
		while ($newsItem = $this->database->fetch_array ($query)) {
			$allNewsItems[] = $newsItem;
		}
		return $allNewsItems;
	} /* public function getAllNews ($postsOnPage,$offset = NULL,$category = NULL) */

	/**
	 * Some function that needs on other name
	 *
	 * @param int $postsOnPage the max number of posts on one page
	 * @param int $offset the offset of the LimitNews, if not false, if false than 0
	 * @param string $category the categoy of the headlines you want, if not all categories will be searched
	 * @todo multiple categories you want to search in
	 * @return array
	*/
	public function getLimitNews ($postsOnPage,$offset = false,$category = NULL) {
		if ($offset === false) {
			$limit['offset'] = 0;
		} else {
			$limit['offset'] = $offset;
		}
		$limit['limit'] = $postsOnPage;
		$sql = 'SELECT ' . FIELD_NEWS_ID . ' FROM ' . TBL_NEWS;
		$sql .= ' WHERE ' . FIELD_NEWS_LANGUAGE . '=\'' . $this->contentLang . '\'';
		if (! empty ($category)) {
			$sql .= ' AND category=\'' . $category . '\'';
		}
		$query = $this->database->query ($sql);
		$limit['total'] = $this->database->num_rows ($query);
		$limit['previous'] = $limit['offset'] - $limit['limit'];
		$limit['next'] = $limit['offset'] + $limit['limit'];
		return $limit;
	} /* public function getLimitNews ($postsOnPage,$offset = false,$category = NULL) */

	/**
	 * post a comment in the database
	 *
	 * @param string $message the message of the comment
	 * @param string $subject the subject of the comment
	 * @param int $date the date in Unix epoch
	 * @param string $contentLang the contentLang
	 * @param mixed $authorID the ID of the author
	 * @param int $newsID the ID of the news where this is a comment on
	 * @param int $parentCommentID the ID of the parentComment (0 if this comment is directly on the news)
	 * @return bool
	*/
	public function postComment ($message,$subject,$contentLang,$date,$authorID,
								$newsID,$parentCommentID = 0) {
		if ($parentCommentID == 0) {
			$isCommentOnNews = YES;
			// stupid postgre hack
			$parentCommentID = '0';
		} else {
			$isCommentOnNews = NO;
		}
		$sql = 'INSERT into ' . TBL_COMMENTS;
		$fields = array (FIELD_COMMENTS_MESSAGE,
			FIELD_COMMENTS_SUBJECT,FIELD_COMMENTS_AUTHOR,FIELD_COMMENTS_DATE,
			FIELD_COMMENTS_ID_NEWS,FIELD_COMMENTS_ON_NEWS,
			FIELD_COMMENTS_ID_ON_COMMENT);
		$strfields = implode (',',$fields);
		$sql .= ' (' . $strfields . ')';
		$content = array ('\''.$message.'\'','\''.$subject.'\''
			,'\''.$authorID.'\'','\''.$date.'\'','\''.$newsID.'\'',
			'\''.$isCommentOnNews.'\'','\''.$parentCommentID.'\'');
		$strcontent = implode (',',$content);
		$sql .= 'values (' . $strcontent . ')';
		$query = $this->database->query ($sql);
		// comment is posted now updating the news #comments
		// select the news
		$sql = 'SELECT ' . FIELD_NEWS_COMMENTS . ' FROM ' . TBL_NEWS;
		$sql .= ' WHERE ' .  FIELD_NEWS_ID . '=\'' . $newsID . '\'';
		$query = $this->database->query ($sql,false); // Not fatal
		$news = $this->database->fetch_array ($query);
		$curComments = $news[FIELD_NEWS_COMMENTS];
		// now put the new value in the newsdb;
		$sql = 'UPDATE ' . TBL_NEWS;
		$sql .= ' set ' . FIELD_NEWS_COMMENTS . '=\'' . ++$curComments . '\'';
		$sql .= ' WHERE ' . FIELD_NEWS_ID . '=\''  . $newsID . '\'';
		$query = $this->database->query ($sql,false); // not fatal
	} /* public function postComment ($message,$subject,$contentLang,$date,
			$authorID,$newsID,$parentCommentID = 0*/

	/**
	 * edit a comment in the database
	 *
	 * @param string $newMessage the new message 
	 * @param string $newSubject the new subject
	 * @param int $IDComment the ID of the comment wich is edited
	 * @return bool
	*/
	public function editComment ($newMessage,$newSubject,$IDComment) {
		$sql = 'UPDATE ' . TBL_COMMENTS; 
		$sql .= ' SET ' . FIELD_COMMENTS_MESSAGE . '=\'' . $newMessage . '\'';
		$sql .= ',' . FIELD_COMMENTS_SUBJECT . '=\'' . $newSubject . '\'';
		$sql .= ' WHERE id=\'' . $IDComment . '\'';
		$query = $this->database->query ($sql);
		return true;
	} /* public function editComment ($newMessage,$newSubject,$iIDCmment) */

	/**
	 * get a Comment with a defined ID
	 *
	 * @param int $ID the ID of the Comment
	 * @return array
	*/
	public function getCommentByID ($ID) {
		$sql = 'SELECT * FROM ' . TBL_COMMENTS;
		$sql .= ' WHERE ' . FIELD_COMMENTS_ID . '=\'' . $ID .'\'';
		$query = $this->database->query ($sql);
		return $this->database->fetch_array ($query);
	} /* public function getCommentByID ($ID) */

	/**
	 * get All the comments on a specified newsID
	 *
	 * @param int $IDNews the ID of the newsitems
	 * @param int $postsOnPage the max number of posts on one page
	 * @param int $offset the offset from where to start
	 * @return array
	*/
	public function getAllComments ($IDNews,$postsOnPage,$offset = false) {
		$limit = $this->getLimitComments ($postsOnPage,$IDNews,$offset);
		$sql = 'SELECT * FROM ' . TBL_COMMENTS;
		$sql .= ' WHERE ' . FIELD_COMMENTS_ID_NEWS . '=\'' . $IDNews .'\'';
		$sql .= ' ORDER by ' . FIELD_COMMENTS_DATE . ' asc ';
		$sql .= 'LIMIT ' . $limit['limit'] . ' OFFSET ' . $limit['offset'];
		$query = $this->database->query ($sql);
		$comments = array ();
		while ($comment = $this->database->fetch_array ($query)) {
			$comments[] = $comment;
		}
		return $comments;
	} /* public function getAllComments ($IDNews,$postsOnPage,$offset = NULL) */

	/**
	 * get the childs of comment
	 *
	 * @param object $comment the comment where you want the children of
	 * @return array
	*/
	public function getThreadChildren ($comment) {
		$sql = 'SELECT * FROM ' . TBL_COMMENTS;
		$sql .= ' WHERE id_news=\'' . $IDNews .'\'';
		$sql .= ' AND ' . FIELD_COMMENTS_ID_ON_COMMENT . '=\'' . $comment[FIELD_COMMENTS_ID] . '\'';
		$sql .= ' AND ' . FIELD_COMMENTS_ID_NEWS . '=\'' . $comment[FIELD_COMMENTS_ID_NEWS] .'\'';
		$sql .= ' ORDER by ' . FIELD_COMMENTS_DATE . ' asc';
		$query = $this->database->query ($sql);
		$comments = array ();
		while ($child = $this->database->fetch_array ($query)) {
			$comments[] = $child;
		}
		return $comments;
	} /* public function getThreadChildren ($comment) */

	/**
	 * get the direct comments from a newsitem
	 *
	 * @param int $IDNews the ID of the parent ID
	 * @return array
	*/
	public function startThreads ($IDNews) {
		$sql = 'SELECT * FROM ' . TBL_COMMENTS;
		$sql .= ' WHERE ' . FIELD_COMMENTS_ID_NEWS . '=\'' . $IDNews . '\'';
		$sql .= ' AND ' . FIELD_COMMENTS_ON_NEWS . '=\''. YES . '\'';
		$sql .= 'ORDER by ' . FIELD_COMMENTS_DATE . ' asc';
		$query = $this->database->query ($sql);
		$comments = array ();
		while ($child = $this->database->fetch_array ($query)) {
			$comments[] = $child;
		}
		return $comments;
	} /* public function startThreads ($IDNews) */

	/**
	 * Some function that needs on other name
	 *
	 * @param int $postsOnPage the max number of posts on one page 
	 * @param int $newsID the newsID
	 * @param int $offset the offset of the LimitNews, if not false, if false than 0
	 * @return array
	*/
	public function getLimitComments ($postsOnPage,$newsID,$offset = false) {
		if ($offset === false) {
			$limit['offset'] = 0;
		} else {
			$limit['offset'] = $offset;
		}
		// not a good hack
		if ($newsID === false) {
			return;
		}
		$limit['limit'] = $postsOnPage;
		$sql = 'SELECT ' . FIELD_NEWS_ID . ' FROM  ' . TBL_COMMENTS;
		$sql .= ' WHERE ' . FIELD_COMMENTS_ID_NEWS . '=\'' . $newsID . '\'';
		$query = $this->database->query ($sql);
		$limit['total'] = $this->database->num_rows ($query);
		$limit['previous'] = $limit['offset'] - $limit['limit'];
		$limit['next'] = $limit['offset'] + $limit['limit'];
		return $limit;
	} /* public function getLimitComments ($postsOnPage,$newsID,$offset = false) */

	/**
	 * get all categories
	 * @return array
	*/
	public function getAllCategoriesByLanguage () {
		$sql = 'SELECT * FROM ' . TBL_CATEGORIES;
		$sql .= ' WHERE ' . FIELD_CATEGORIES_LANGUAGE . '=\'' . $this->contentLang . '\'';
		$query = $this->database->query ($sql);
		$categories = array ();
		while ($cat = $this->database->fetch_array ($query)) {
			$categories[] = $cat;
		}
		return $categories;
	}

	/**
	 * shows the feed
	 *
	 * @param array $meta some metatags
	 * @param string $category the categoy of the headlines you want, if not all categories will be searched
	 * @todo multiple categories you want to search in
	 * @param string $method the method you want to view it
	 * @todo add an Atom 1.0 feed
	 * @return string
	*/
	public function showFeed ($meta,$category = NULL,$method = 'RSS2') {
		if (! empty ($category)) {
			$meta['category'] = $category;
		}
		$headlines = $this->getHeadlines (NULL,$category);
		switch ($method) {
			case 'RSS2':
				$output = $this->viewRSS ($meta,$headlines);
				break;
			default:
				$output = $this->viewRSS ($meta,$headlines);
		}
		return $output;
	} /* public function showFeed ($meta,$category = NULL,$method = 'RSS2') */

/*-----------------------------Private functions-----------------------------*/

	/**
	 * generate a RSS feed
	 *
	 * @param array $meta some meta vars
	 * @param array $headlines the headlines in the feed
	 * @return string
	*/
	private function generateRSS ($meta,$headlines) {
		header ('Content-type: application/xml');
		$output = '<?xml version="1.0"?>';
		$output .= '<rss version="2.0">';
		$output .= '<channel>';

		$output .= '<title>'.$meta['title'].'</title>';
		$output .= '<link>'.$meta['link'].'</link>';
		$output .= '<description>'.$meta['description'].'</description>';
		//$output .= '<language>'.$meta['language'].'</language>';
		if (isset ($meta['category'])) {
			$output .= '<category>'.$meta['category'].'</category>';
		}

		$configtimezone =
			$this->config->getConfigByNameType ('general/servertimezone',TYPE_NUMERIC);
		if ($configtimezone < 0) {
			$timezone = '-';
		} else {
			$timezone = '+';
		}
		if (abs($configtimezone) < 10) {
			$timezone .= '0';
		}
		$timezone .= floor(abs($configtimezone));
		$minutes = ceil (abs($configtimezone))-abs($configtimezone);
		$minutes *= 60;
		if ($minutes < 10) {
			$timezone .= '0';
		}
		$timezone .= $minutes;

		foreach ($headlines as $headline) {
			$output .= '<item>';
			$output .= '<title>';
				$output .= $headline[FIELD_NEWS_SUBJECT];
			$output .= '</title>';
			$output .= '<link>';
				$output .= $meta['link'] . '/index.php#news' . $headline[FIELD_NEWS_ID];
			$output .= '</link>';
			$output .= '<description>';
				$output .= $headline[FIELD_NEWS_MESSAGE];
			$output .= '</description>';
			$output .= '<author>';
				$output .= $headline[FIELD_NEWS_AUTHOR];
			$output .= '</author>';
			$output .= '<category>';
				$output .= $headline[FIELD_NEWS_CATEGORY];
			$output .= '</category>';
			$date = date ('D, d M Y H:i:s '.$timezone,
				$headline[FIELD_NEWS_DATE]+$configtimezone*3600);
			// RFC 2822 formatted date "Thu, 21 Dec 2000 16:01:07 +0100"
			$output .= '<pubDate>';
				$output .= $date;
			$output .= '</pubDate>';
			$output .= '<comments>';
				$output .= $meta['link'] . '/news.php?action=viewcomments';
				$output .= '&amp;id=' . $headline[FIELD_NEWS_ID];
			$output .= '</comments>';
			$output .= '</item>';
		}

		$output .= '</channel>';
		$output .= '</rss>';
		return $output;
	} /* private function generateRSS ($meta,$headlines) */
} // Class CNews
?>
