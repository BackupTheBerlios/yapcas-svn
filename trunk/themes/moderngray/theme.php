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
	$this->version = '0.3';
	$this->version_cms = '0.3';
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

	/*--------------------------------NEW VARS--------------------------------*/
	$this->items = array ();
	$this->items['theme.shortviewpoll'] = '{include shortviewpoll.html}';
	$this->items['moderngray.standardcss'] = $this->convertFile ('standard.css');
	$this->childsOfSideBar = '&theme.shortviewpoll;';
	$this->pages = array ();
	$this->pages['index.html'] = '&theme.shortviewpoll;';
	$this->titleFormat = ' S :: P';
?>
