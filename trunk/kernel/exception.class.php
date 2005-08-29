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
* File that take care of the exception SubSystem
*
* @package exception
* @author Nathan Samson
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
function apperror ($str,$config = '') {
	if ($config == '') {
		echo $str;
	}
	if (defined ('USE_YAPCASCONFIG')) {
		if ($config->getConfigByNameType ('general/errorreporting',TYPE_INT)) {
			return $str;
		} else {
			return;
		}
	}
} /* function apperror ($str,$config = '') */

/**
* Class that take care off the exception SubSystem
*
* @version 0.4cvs
* @author Nathan Samson
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class exceptionlist extends Exception {
	public function __construct ($message,$debuginfo = NULL,$code = -1,$fatal = true,$db = false) {
		$this->next = NULL;
		$this->fatal = $fatal;
		$this->db = $db;
		$this->debuginfo = $debuginfo;
		// make sure everything is assigned properly
		parent::__construct ($message, $code);
	} /* public function __construct($message,$code = -1) */

	public function setNext ($next) {
		$this->next = $next;
	} /* public function setNext ($next) */

	public function getNext () {
		return $this->next;
	} /* public function getNext () */
} /* class exceptionlist */
?>
