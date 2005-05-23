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
//include_once ('kernel/error.class.php');
define ('TYPE_STRING',1);
define ('TYPE_BOOL',2);
define ('TYPE_INT',3);
define ('TYPE_FLOAT',4);
define ('TYPE_UKNOWN',-1); // I do not care type

define ('YES','Y');
define ('NO','N');

function checkType ($config,$type) {
	if (is_bool ($config)) {
		$utype = TYPE_BOOL;
	} else if (is_int ($config)) {
		$utype = TYPE_INT;
	} else if (is_float ($config)) {
		$utype = TYPE_FLOAT;
	} else if (is_string ($config)) {
		$utype = TYPE_STRING;
	} else {
		$utype = TYPE_UKNOWN;
	}

	if (($utype == $type) or ($type == TYPE_UKNOWN)){
		return true;
	} else {
		return false;
	}
} /* function checkType ($config,$type) */

function parseConfigName ($name) {
	if (preg_match ('#(.+?)\/(.+?)#',$name)) {
		$tmparray = explode ('/',$name);
		$tmparray['section'] = $tmparray[0];
		$tmparray['namevar'] = $tmparray[1];
		return $tmparray;
	} else {
		echo 'no slash';
		return false;
	}
} /* function parseConfigName ($name) */

class config {
	function config () {
		$this->configtree = array ();
	} /* function config () */

	function addToConfigTree ($content,$section,$namevar,$type) {
		// add this config to the config tree
		//if ((isset($this->configtree[$section]) and (is_a)) {
			if (!isset ($this->configtree[$section][$namevar])) {
				if (checkType ($content,$type) == true) {
					$this->configtree[$section][$namevar] = $content;
					return true;
				} else {
					return errorSDK ("Type not correct");
				}
			} else {
				return errorSDK ("Config already set");
			}
		/*} else {
			return errorSDK ("Section not correct");
		}*/
	} /* function addToConfigTree ($content,$section,$namevar,$type) */

	/*
		a configoption that came from an ini file (and only form an ini file) must be registred here
		a name looks like this
		'sectionname / nameofvar'
	*/
	function addConfigByFileName ($file,$type,$name,$standard) {
		$configOption = parseConfigName ($name);
		if (is_array($configOption)) {
			// $name parsed correctly 
			// make some vars shorter
			$section = $configOption['section'];
			$namevar = $configOption['namevar'];
			if (file_exists ($file)) {
				include ($file);
				if (isset($config[$section][$namevar])) {
					$result = $this->addToConfigTree ($config[$section][$namevar],$section,$namevar,$type);
					unset ($config);
					return $result;
				}
			} else {
				return errorSDK ("File: " . $file . " Does not exists, can not parse ini");
			}
		} else {
			// $name is not parsed correctly
			//return errorSDK ("name is not correct, can not parse name");
			echo "name is not correct, can not parse";
		}
	} /* function addConfigByFileName ($file,$type,name,$standard) */

	function addConfigByList ($list,$vars,$name,$type) {
		$list = explode (';',$list);
		$i = 0;
		$pname = parseConfigName ($name);
		if (is_array($pname)) {
			$section = $pname['section'];
			$varname = $pname['namevar'];
			if (count($list) == count ($vars)) {
				foreach ($list as $item) {
					switch ($item) {
						case 'YAPCAS_USER':
							if ($vars[$i]->loggedin ()) {
								$this->addToConfigTree ($vars[$i]->getconfig ($varname),$section,$varname,$type);
								return;
							} else {
								if (count ($list) == $i+1) {
									// it is the last one and it is empty
									$this->addToConfigTree ('',$section,$varname,$type);
									return;
								}
							}
							break;
						case 'GET':
							if (isset($_GET[$vars[$i]])) {
								$this->addToConfigTree ($_GET[$vars[$i]],$section,$varname,$type);
								return;
							}
							break;
						case 'COOKIE':
							if (isset($_COOKIE[$vars[$i]])) {
								$this->addToConfigTree ($_COOKIE[$vars[$i]],$section,$varname,$type);
								return;
							}
							break;
						case 'FILE':
							$this->addConfigByFileName ($vars[$i],$type,$name,0);
							return;
							break;
						default:
							// FIXME
							return false;
					}
					$i++;
				}
			}
		} else {
			// FIXME
			return false;
		}
	} // function addConfigByList ($list,$vars,$name)

	function deleteConfig ($name) {
		$pname = parseConfigName ($name);
		if (is_array ($pname)) {
			$section = $pname['section'];
			$varname = $pname['namevar'];
			if (isset ($this->configtree[$section][$varname])) {
				unset ($this->configtree[$section][$varname]);
			}
		} else {
			return false;
		}
	} /* function deleteConfig ($name) */

	function getConfigByNameType ($name,$type) {
		$configOption = parseConfigName ($name);
		if (is_array($configOption)) {
			// $name parsed correctly 
			// make some vars shorter
			$section = $configOption['section'];
			$namevar = $configOption['namevar'];
			if (isset($this->configtree[$section][$namevar])) {
				if (checkType($this->configtree[$section][$namevar],$type)) {
					return $this->configtree[$section][$namevar];
				} else {
					echo 'wrong type';
					echo $name;
					return errorSDK ("Wrong type");
				}
			} else {
				echo "!isset";
				echo $name;
				return errorSDK ("Config not found");
			}
		} else {
			// $name is not parsed correctly
			echo "name is not correct";//return errorSDK ("name is not correct, can not parse name");
		}
	} /* function getConfigByNameType ($name,$type) */

	function writeOut () {
	} /* function writeOut () */
}
?>
