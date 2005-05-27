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
if (defined ('USE_YAPCASCONFIG')) {
	define ('USE_YAPCASCONFIG',true);
}

if (! defined ('EXCEPTION_CLASS')) {
	include ('kernel/exception.class.php');
}

define ('TYPE_STRING',1);
define ('TYPE_BOOL',2);
define ('TYPE_INT',3);
define ('TYPE_FLOAT',4);
define ('TYPE_UKNOWN',-1); // I do not care type

define ('YES','Y');
define ('NO','N');

function checkType ($content,$type,$config) {
	// stupid hack, if an integer value == 0, it is send as a bool
	if ((is_bool($content)) and ($type == TYPE_INT)) {	
		$utype = TYPE_INT;
	} else if (is_bool ($content)) {
		$utype = TYPE_BOOL;
	} else if ((is_int ($content)) or (is_numeric ($content))) {
		$utype = TYPE_INT;
	} else if (is_float ($content)) {
		$utype = TYPE_FLOAT;
	} else if (is_string ($content)) {
		$utype = TYPE_STRING;
	} else if (is_bool ($content)) {
		$utype = TYPE_BOOL;
	} else {
		$utype = TYPE_UKNOWN;
	}

	if (($utype == $type) or ($type == TYPE_UKNOWN)){
		return true;
	} else {
		echo '<br>' . $content . '<br />';
		echo $utype . '::' . $type;
		throw new exceptionlist ("Type is wrong", 
			__FILE__ . ': ' . __FUNCTION__ . ': ' . __LINE__);
	}
} /* function checkType ($content,$type,$config) */

function parseConfigName ($name,$config) {
	if (preg_match ('#(.+?)\/(.+?)#',$name)) {
		$tmparray = explode ('/',$name);
		$tmparray['section'] = $tmparray[0];
		$tmparray['namevar'] = $tmparray[1];
		return $tmparray;
	} else {
		throw new exceptionlist ("Configname could not be parsed", 
			': ' . $name. ':' . __FILE__.': ' . __FUNCTION__ . ': ' . 
			__LINE__);
	}
} /* function parseConfigName ($name,$config) */

function convertToStandard (&$content) {
	// Y and N are database-values
	if ($content == YES) {
		settype ($content,'bool');
		return true;
	} else if ($content == NO) {
		settype ($content,'bool');
		return false;
	} else {
		// noting needs to be changed
		return $content;
	}
} /* function convertToStandard (&$content) */

class config {
	public function __construct () {
		$this->configtree = array ();
	} /* function config () */

	/*
		add this config to the config tree
	*/
	private function addToConfigTree ($content,$section,$namevar,$type) {
		if (isset ($this->configtree[$section])) {
			if (!is_array ($this->configtree[$section])) {
				throw new exceptionlist ("Section is not an array, but already set",
					': '.$section .':'.__FILE__.': '.__FUNCTION__.': '.__LINE__);
			}
		}
		if (!isset ($this->configtree[$section][$namevar])) {
			try {
				checkType (convertToStandard ($content),$type,$this);
				$this->configtree[$section][$namevar] = convertToStandard ($content);
				return true;
			} 
			catch (exceptionlist $e) {
				throw $e;
			}
		} else {
			throw new exceptionlist ("Config already set",
					': ' . $section . '/' . $namevar .':' .__FILE__ . ': ' .
					__FUNCTION__ . ': ' . __LINE__);
		}
	} /* function addToConfigTree ($content,$section,$namevar,$type) */

	/*
		a configoption that came from an ini file (and only from an ini file) 
		must be registred here
		a name looks like this
		'sectionname/nameofvar'
	*/
	public function addConfigByFileName ($file,$type,$name,$standard = 0) {
		try {
			$configOption = parseConfigName ($name,$this);
			// $name parsed correctly 
			// make some vars shorter
			$section = $configOption['section'];
			$namevar = $configOption['namevar'];
			if (file_exists ($file)) {
				include ($file);
				if (isset($config[$section][$namevar])) {
					$result = $this->addToConfigTree ($config[$section][$namevar],
						$section,$namevar,$type);
					unset ($config);
					return $result;
				}
			} else {
				throw new exceptionlist ("File does not exists",
					': ' . $file .':' .__FILE__ . ': ' . __FUNCTION__ . ': ' .
					__LINE__);
			}
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* function addConfigByFileName ($file,$type,name,$standard) */

	public function addConfigByList ($list,$vars,$name,$type) {
		$list = explode (';',$list);
		$i = 0;
		try {
			$pname = parseConfigName ($name,$this);
		}
		catch (exceptionlist $e) {
			throw $e;
		}
		$section = $pname['section'];
		$varname = $pname['namevar'];
		if (count($list) == count ($vars)) {
			foreach ($list as $item) {
				switch ($item) {
					case 'YAPCAS_USER':
						if ($vars[$i]->loggedin ()) {
							try {
								$this->addToConfigTree (
									$vars[$i]->getconfig ($varname),$section,
									$varname,$type);
								return true;
							} 
							catch (exceptionlist $e) {
								throw $e;
							}
						} else {
							if (count ($list) == $i+1) {
								// it is the last one and it we can not retrieve 
								// it, but it must be set
								try {
									$this->addToConfigTree ('',$section,$varname,
										$type);
									return true;
								}
								catch (exceptionlist $e) {
									throw $e;
								}
							}
						}
						break;
					case 'GET':
						if (isset($_GET[$vars[$i]])) {
							try {
								$this->addToConfigTree ($_GET[$vars[$i]],$section,
									$varname,$type);
								return true;
							}
							catch (exceptionlist $e) {
								throw $e;
							}
						}
						break;
					case 'COOKIE':
						if (isset($_COOKIE[$vars[$i]])) {
							try {
								$this->addToConfigTree ($_COOKIE[$vars[$i]],
									$section,$varname,$type);
								return true;
							}
							catch (exceptionlist $e) {
								throw $e;
							}
						}
						break;
					case 'FILE':
						try {
							$this->addConfigByFileName ($vars[$i],$type,$name,0);
							return true;
						}
						catch (exceptionlist $e) {
								throw $e;
						}
						break;
					default:
						throw new exceptionlist ("Item does not exists",
							': '.$item.':'.__FILE__.': '.__FUNCTION__.': '.__LINE__);
						}
				$i++;
			}
		}
	} // function addConfigByList ($list,$vars,$name)

	public function deleteConfig ($name) {
		try {
			$pname = parseConfigName ($name,$this);
			$section = $pname['section'];
			$varname = $pname['namevar'];
			if (isset ($this->configtree[$section][$varname])) {
				unset ($this->configtree[$section][$varname]);
			}
		} 
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* function deleteConfig ($name) */

	public function getConfigByNameType ($name,$type) {
		try {
			$configOption = parseConfigName ($name,$this);
			// $name parsed correctly 
			// make some vars shorter
			$section = $configOption['section'];
			$namevar = $configOption['namevar'];
			if (isset($this->configtree[$section][$namevar])) {
				checkType($this->configtree[$section][$namevar],$type,$this);
				return $this->configtree[$section][$namevar];
			} else {
				throw new exceptionlist ("Config does not exists".
					apperror (': ' . $name .':' .
					__FILE__ . ': ' . __FUNCTION__ . ': ' . __LINE__,$this));
			}
		}
		catch (exceptionlist $e) {
			throw $e;
		}
	} /* function getConfigByNameType ($name,$type) */

	public function writeOut () {
	} /* function writeOut () */
}
?>
