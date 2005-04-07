<?php
	$this->safety->error_reporting = E_ALL;
	/* options are	
		E_NONE // use this for live-applications
		E_ALL // use this ONLY for testing purpose and NOT on live-applications
	*/
			
	$this->database->type = MySQL4;
	/* options are
		MySQL4
		PostgreSQL // Only 7.4 & 7.x supported
		MySQL3 // Unsupported!!!
		XML // NYI (Not Yet Implemented)
	*/
	$this->database->host = 'localhost';
	$this->database->user = 'nathan';
	$this->database->password = 'TuxPHP';
	$this->database->name = 'yapcas';
			
	$this->news->enable_threaded = true;
	$this->news->standard->view = threaded;
	$this->news->standard->postsonpage = 20;
	
	
	$this->site->timezone = +2;	
	$this->site->timeformat = 'H:i:s d/m/Y';	
	$this->site->name = 'YaPC(a)S';
	$this->site->description = 'Yet another PHP Content (admin) System';
	$this->site->copyright = '&copy; 2004-2005 Nathan Samson';
	$this->site->language = 'dutch';
	$this->site->theme = 'moderngray';
	$this->news->postsonpage = 10;
	$this->news->headlines = 20;
	$this->users->activate = false;
	/*
		if set true, the user must activate his account by regestring and change email
		only false is implemented
	*/
?>