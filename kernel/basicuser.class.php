<?php
	class basicuser {
	
		function getlanguage () {
			return STANDARD_LANGUAGE;
		}
	
		function gettimezone () {
			return 0;
		}
		
		function gettimeformat () {
			return 'HIS';
		}
		
		function getheadlines () {
			return 10;
		}
		
		function getpostsonpage () {
			return 10;
		}
	
		function getthreaded () {
			return true;
		}
	
		function gettheme () {
			return STANDARD_THEME;
		}
	
		function getemail () {
			return 'email';
		}
		
		function getname () {
			return 'name';
		}
	
		function loggedin () {
			return false;
		}
	
	}
?>