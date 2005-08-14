<?php
/**
* File that take care of the News SubSystem
*
* @package news
*/
define ('TBL_NEWS',TBL_PREFIX . 'news');
define ('TBL_COMMENTS',TBL_PREFIX . 'comments');
define ('TBL_CATEGORIES',TBL_PREFIX . 'categories');

define ('FIELD_NEWS_ID','id' );
define ('FIELD_NEWS_COMMENTS','comments');
define ('FIELD_NEWS_MESSAGE','message');
define ('FIELD_NEWS_SUBJECT','subject');
define ('FIELD_NEWS_AUTHOR','author');
define ('FIELD_NEWS_DATE','date');
define ('FIELD_NEWS_LANGUAGE','language');
define ('FIELD_NEWS_CATEGORY','category');

define ('FIELD_COMMENTS_ID','id');
define ('FIELD_COMMENTS_ID_NEWS','id_news');
define ('FIELD_COMMENTS_DATE','date');
define ('FIELD_COMMENTS_ID_ON_COMMENT','id_on_comment');
define ('FIELD_COMMENTS_ON_NEWS','comment_on_news');
define ('FIELD_COMMENTS_SUBJECT','subject');
define ('FIELD_COMMENTS_MESSAGE','message');
define ('FIELD_COMMENTS_AUTHOR','author');

define ('FIELD_CATEGORIES_NAME','name');
define ('FIELD_CATEGORIES_LANGUAGE','language');

define ('GET_OFFSET','offset');
define ('GET_CATEGORY','category');
define ('GET_NEWSID','id');
define ('GET_ID','id');

define ('POST_MESSAGE','message');
define ('POST_SUBJECT','subject');
define ('POST_DATE','date');
define ('POST_CATEGORY','category');
define ('POST_ID','id');
define ('POST_ID_COMMENT','on_comment_id');
define ('POST_ID_NEWS','on_news_id');
?>
