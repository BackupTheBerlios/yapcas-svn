<?php 
if (!defined ('EXCEPTION_CLASS')) {
	define ('EXCEPTION_CLASS',true);
}

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
} /* function apperror ($1,$2 ='',$3 = '') */

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
		return $next;
	} /* public function getNext () */
} /* class exceptionlist */
?>
