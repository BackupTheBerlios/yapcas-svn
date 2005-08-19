<?php
/* This program is free software; you can redistribute it and/or modify 
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
	$this->name = 'ModernGray';
	$this->version = '0.3+';
	$this->version_cms = '0.3+';
	$this->css[1] = 'standard.css';
	$this->alternativecss[1] = 'alt.css';
	
	$this->errormessage = '<p class="error">%error</p>';
	$this->db_errormessage = '<p class="error">%db_error</p>';
	$this->notemessage = '<p class="note">%note</p>';
	$this->warningmessage = '<p class="warning">%warning</p>';
	$this->db_warning = '<p class="warning">%db_warning</p>';
	
	$this->post_news_link = '<a href="%postnews.link">%postnews.lang</a>';
	$this->openthread = '<div class="thread">';
	$this->closethread = '</div>';
	
	$this->copyright = 'ModernGray Copyright Nathan Samson 2004-2005';
	$this->themelink = './index2.php';
	
	$this->database_option['open'] = '<select name="%database.name">';
	$this->database_option['close'] = '</select>';
	$this->database_option['syntax_curval'] = '<option>%option</option>';
	$this->database_option['syntax'] = '<option>%option</option>';
	
	$this->theme_option['open'] = '<select SELECTED name="%theme.name">';
	$this->theme_option['close'] = '</select>';
	$this->theme_option['syntax_curval'] = '<option>%option</option>';
	$this->theme_option['syntax'] = '<option>%option</option>';
	
	$this->threaded_option['open'] = NULL;
	$this->threaded_option['close'] = NULL;
	$this->threaded_option['syntax_curval'] = '%option <input type="radio" name="%threaded.name" checked="checked" value="%option">';
	$this->threaded_option['syntax'] = '%option <input type="radio" name="%threaded.name" value="%option">';
	
	$this->language_option['open'] = '<select name="%language.name">';
	$this->language_option['close'] = '</select>';
	$this->language_option['syntax_curval'] = '<option SELECTED>%option</option>';
	$this->language_option['syntax'] = '<option>%option</option>';
	$this->newstheme->editbutton = '<a href="%link"><img src="%button.image" /></a>';
	$this->titleformat = ' S  ::  P ';
	$this->pollanswertovote = '<input type="radio" name="%choices.name" value="%answer.id">%answer.text<br />';
	$this->pollresults = '%answer: %votes.percent% <br />';
	$this->polllink = '<a href="%poll.link" >%poll.question</a><br />';
	$this->userlink = '<a href="%link.url" class="user">%link.shown_name</a> <br />';
	$this->touserinfolink = '<a href="%user.url" class="user">%user.name</a> <br />';
	$smiley['text'] = ':)';
	$smiley['output'] = '<img src="%image.smiley_lachen.png" />';
	$this->smilies[] = $smiley;
	$smiley['text'] = ';)';
	$smiley['output'] = '<img src="%image.smiley_knipoog.png" />';
	$this->smilies[] = $smiley;
	
	$this->quote['open'] = '<div class="quote">';
	$this->quote['close'] = '</div>';
	$this->i['open'] = '<span class="i">';
	$this->i['close'] = '</span>';
	$this->u['open'] = '<span class="u">';
	$this->u['close'] = '</span>';
	$this->b['open'] = '<span class="b">';
	$this->b['close'] = '</span>';

	$this->helpindex = array ();
	$this->helpcontent = array ();
	$this->helpindexquestion = '<li><a href="#%itemid%">%question%</a></li>';
	$this->helpcontentquestion = '<li id="%itemid%">%question%<br />%answer%</li>';
	$this->titleFormat = ' S :: P';

	/*--------------------------------NEW VARS--------------------------------*/
	$this->items = array ();
	$this->items['theme.shortviewpoll'] = '{include shortviewpoll.html}';
	$this->items['moderngray.standardcss'] = $this->convertFile ('standard.css');
	$this->items['news.index'] = '{newsitems}&news.navigator;';
	$this->items['news.item'] = '{include newsitem.html}';
	$this->items['news.fullitem'] = '{include newsfullitem.html}';
	$this->items['news.headlines'] = '{include headlines.html}';
	$this->items['news.headline'] = '<a href="{headline link}">{headline subject}</a><br />';
	$this->items['comments.threaded'] = '{comments}';
	$this->items['comments.nonthreaded'] = '{comments}';
	$this->items['newscomment.item'] = '{include newscomment.html}';
	$this->items['thread.open'] = '<div class="thread">';
	$this->items['thread.close'] = '</div>';
	$this->items['message.error'] = '<p class="note">{message error}</p>';
	$this->items['message.warning'] = '<p class="note">{message warning}</p>';
	$this->items['message.note'] = '<p class="note">{message note}</p>';
	$this->items['navigation.item'] = '<a href="{navigation link}">{navigation name}</a> ';
	$this->items['user.userform'] = '{include userform.html}';
	$this->items['poll.choice'] = '{choice text}: <input name="voted_on" type="radio" value="{choice number}" /><br />';
	$this->items['poll.result'] = '{choice text}: {choice resultprocent}% <br />' ;
	$this->items['news.categoryoption'] = '<option>&category.name;</option>';
	$this->items['comments.navigator'] = '{include commentnavigator.html}';
	$this->items['news.navigator'] = '{include newsnavigator.html}';
	$this->items['button'] = '<a href="{button action}">{button text}</a>';
	$this->items['editcomment.button'] = '{button &lang.editcomment; {comment linkeditcommentform} }';
	$this->items['editnews.button'] = '{button &lang.editnews; {news linkeditnewsform} }';
	$this->items['bbc.button'] = '<input value="{bbc tag}" accesskey="{bbc key}" name="addbbcode{bbc code}" onclick="bbstyle ({bbc code})" onmouseover="helpline(\'{bbc tag}\')" type="button" />';
	$this->items['emoticon.button'] = '<a href="javascript:addSmiley (\'{emot text}\')"><img class="emoticon" src="{emot image}"/></a>';
	$this->items['emoticon'] = '<img class="emoticon" src="{emot img}" name="{emot name}" alt="{emot name}" />';
	$this->items['userlist.item'] = '<a href="{item link}">{item name}</a><br />';
	$this->childsOfSideBar = '&poll.viewcurrentpoll;,&news.headlines;';
	$this->childsOfNavigation = '&site.navigation;,&user.userform;';
	$this->pages = array ();
	$this->pages['index.php'] = '&poll.viewcurrentpoll;,&news.headlines;,&site.navigation;,&user.userform;';
	$this->pages['news.php?action=viewcomments'] = '&poll.viewcurrentpoll;,&news.headlines;,&site.navigation;,&user.userform;';
	$this->pages['news.php?action=postcommentform'] = '&poll.viewcurrentpoll;,&news.headlines;,&site.navigation;,&user.userform;';
	$this->pages['news.php?action=postnewsform'] = '&poll.viewcurrentpoll;,&news.headlines;,&site.navigation;,&user.userform;';
	$this->pages['news.php?action=editcommentform'] = '&poll.viewcurrentpoll;,&news.headlines;,&site.navigation;,&user.userform;';
	$this->pages['news.php?action=editnewsform'] = '&poll.viewcurrentpoll;,&news.headlines;,&site.navigation;,&user.userform;';
	$this->pages['news.php?action=moresmilies'] = '';
	$this->pages['users.php?action=registerform'] = '&poll.viewcurrentpoll;,&news.headlines;,&site.navigation;,&user.userform;';
	$this->pages['users.php?action=changeoptionsform'] = '&poll.viewcurrentpoll;,&news.headlines;,&site.navigation;,&user.userform;';
	$this->pages['users.php?action=sendpasswordform'] = '&poll.viewcurrentpoll;,&news.headlines;,&site.navigation;,&user.userform;';
	$this->pages['users.php?action=viewuserlist'] = '&poll.viewcurrentpoll;,&news.headlines;,&site.navigation;,&user.userform;';
	$this->pages['users.php?action=viewuser'] = '&poll.viewcurrentpoll;,&news.headlines;,&site.navigation;,&user.userform;';
	$this->BBC['b']['open'] = '<span class="b">';
	$this->BBC['b']['close'] = '</span>';
	$this->BBC['u']['open'] = '<span class="u">';
	$this->BBC['u']['close'] = '</span>';
	$this->BBC['i']['open'] = '<span class="i">';
	$this->BBC['i']['close'] = '</span>';
	$this->BBC['quote']['open'] = '<div class="quote">&lang.quote;<br />';
	$this->BBC['quote']['close'] = '</div>';
	$this->items['moresmilies.link'] = '<a href="news.php?action=moresmilies" target="moresmilies" onclick="window.open (\'news.php?action=moresmilies\',\'moresmilies\',\'HEIGHT=300,resizable=yes,scrollbars=yes,WIDTH=250\');return false;">&lang.more_smilies;</a><br />';
	$this->options['string']['open'] = '';
	$this->options['string']['close'] = '';
	$this->options['string']['option'] = '<input name="{option name}" type="text" value="{option curval}" />';

	$this->options['integer']['open'] = '';
	$this->options['integer']['close'] = '';
	$this->options['integer']['option'] = '<input name="{option name}" type="integer" value="{option curval}" />';

	$this->options['textfield']['open'] = '';
	$this->options['textfield']['close'] = '';
	$this->options['textfield']['option'] = '<input name="{option name}" type="text" value="{option curval}"/>';

	$this->options['password']['open'] = '';
	$this->options['password']['close'] = '';
	$this->options['password']['option'] = '<input name="{option name}" type="password" value="{option curval}"/>';

	$this->options['select']['open'] = '<select name="{option name}">';
	$this->options['select']['close'] = '</select>';
	$this->options['select']['option'] = '<option>{option item}</option>';
	$this->options['select']['selectedoption'] = '<option selected>{option item}</option>';

	$this->options['bool']['open'] = '';
	$this->options['bool']['close'] = '';
	$this->options['bool']['yes'] = '<input checked type="checkbox" name="{option name}"/>';
	$this->options['bool']['no'] = '<input type="checkbox" name="{option name}" />';

	$this->options['email'] = $this->options['string'];
	$this->options['name'] = $this->options['string'];
	$this->options['msn'] = $this->options['string'];
	$this->options['yahoo'] = $this->options['string'];
	$this->options['jabber'] = $this->options['string'];
	$this->options['job'] = $this->options['string'];
	$this->options['adress'] = $this->options['string'];
	$this->options['website'] = $this->options['string'];
	$this->options['aim'] = $this->options['string'];
	$this->options['timeformat'] = $this->options['string'];
	$this->options['language'] = $this->options['select'];
	$this->options['theme'] = $this->options['select'];
	$this->options['timezone'] = $this->options['select'];
	$this->options['postsonpage'] = $this->options['integer'];
	$this->options['headlines'] = $this->options['integer'];
	$this->options['threaded'] = $this->options['bool'];
	$this->options['password1'] = $this->options['password'];
	$this->options['password2'] = $this->options['password'];
	$this->options['icq'] = $this->options['integer'];
	$this->options['intrests'] = $this->options['textfield'];
?>
