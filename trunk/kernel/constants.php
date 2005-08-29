<?php
/**
* File that take care of the skin SubSystem constants
*
* @package skin
* @author Nathan Samson
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
// some version constants
define ('majorversion',0);
define ('minorversion',4);
define ('patchlevel',0);
define ('version', majorversion . '.' . minorversion . '.' . patchlevel);
define ('threaded',1);
define ('nonthreaded',0);
define ('mysql4','mysql4');
define ('mysql3','mysql3');
define ('MySQL4','mysql4');
define ('MySQL3','mysql3');
define ('PostgreSQL','postgresql');
define ('postgresql','postgresql');
define ('E_NONE',0);
define ('STANDARD_LANGUAGE','english');
define ('STANDARD_LANGCODE','en'); // remove this
define ('DEFAULT_CONTENT_LANG','en');
define ('STANDARD_THEME','moderngray');
define ('STANDARD_DATABASE_TYPE',MySQL4);
define ('COPYRIGHT','&copy; 2005 Nathan Samson. YaPCaS is Licensed under the GPL');
define ('NL',chr (10)); // NewLine
define ('TAB',chr (9)); // TAB
//define ('TBL_PAGES',TBL_PREFIX . 'pages');
define ('FIELD_PAGES_NAME','name');
define ('FIELD_PAGES_LANGUAGE','language');
define ('FIELD_PAGES_IN_NAVIGATION','show_in_nav');
define ('FIELD_PAGES_IN_USER_NAVIGATION','show_in_user_nav');
define ('FIELD_PAGES_LINK','name');
define ('FIELD_PAGES_SHOWN_NAME','shown_name');


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

define ('POST_NAME','name' );
define ('POST_PASSWORD','password');
define ('POST_PASSWORD1','password1');
define ('POST_PASSWORD2','password2');
define ('POST_EMAIL','email');
define ('POST_POSTSONPAGE','postsonpage');
define ('POST_HEADLINES','headlines');
define ('POST_THEME','theme');
define ('POST_THREADED','threaded');
define ('POST_TIMEZONE','timezone');
define ('POST_TIMEFORMAT','timeformat');
define ('POST_UILANGUAGE','uilanguage');
define ('POST_CONTENTLANGUAGE','contentlanguage');
define ('POST_NEW_PASSWORD1','new_password1');
define ('POST_NEW_PASSWORD2','new_password12');
define ('POST_NEW_AIM','newaim');
define ('POST_NEW_MSN','newmsn');
define ('POST_NEW_ICQ','newicq');
define ('POST_NEW_JABBER','newjabber');
define ('POST_NEW_YAHOO','newyahoo');
define ('POST_NEW_WEBSITE','newwebsite');
define ('POST_NEW_ADRESS','newadress');
define ('POST_NEW_JOB','newjob');
define ('POST_NEW_INTRESTS','newintrests');

define ('POST_VOTED_ON','voted_on');

define ('COOKIE_POLL','poll');
?>
