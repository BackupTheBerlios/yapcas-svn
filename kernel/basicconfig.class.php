<?php
	class basicconfig {
		function basicconfig () {
			$this->database->type = STANDARD_DATABASE_TYPE;
			$this->database->user = '';
			$this->database->password = '';
			$this->database->host = '';
			$this->database->name = '';
			$this->site->name = '';
			$this->safety->error_reporting = E_NONE;
		}
	}
?>