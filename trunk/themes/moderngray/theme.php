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
$this->minVersion = '0.4';
$this->maxVersion = '0.4';

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
$this->items['viewpoll.item'] = '{include viewpollresults.html}';
$this->items['help.indexitem'] = '<div id="{helpindex id}">{helpindex name}<br />{helpindex questions}<br />{helpindex categories}</div>';
$this->items['help.indexquestion'] = '<a href="{indexquestion link}">{indexquestion question}</a>';
$this->items['help.faqcategory'] = '<div id="{helpindex id}">{helpindex name}<br />{helpindex questions}<br />{helpindex categories}</div>';
$this->items['help.faqquestion'] = '<div id="{indexquestion id}"><b>&lang.question;: </b>{indexquestion question}<br /><b>&lang.anwser;:</b> {indexquestion answer}</div>';
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
$this->pages['polls.php?action=allpolls'] = 'ALL';
$this->pages['help.php'] = 'ALL';
$this->groups['ALL'] = '&poll.viewcurrentpoll;,&news.headlines;,&site.navigation;,&user.userform;';
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
