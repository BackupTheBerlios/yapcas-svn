<?php

	define ( 'TBL_USERS',TBL_PREFIX . 'users' );
	define ( 'TBL_USERS_PROFILE',TBL_PREFIX . 'user_profile' );
	define ( 'FIELD_USERS_NAME','name' );
	define ( 'FIELD_USERS_PASSWORD','password' );
	define ( 'FIELD_USERS_TYPE','type' );
	define ( 'FIELD_USERS_EMAIL','email' );
	define ( 'FIELD_USERS_THEME','theme' );
	define ( 'FIELD_USERS_ACTIVATE','activated' );
	define ( 'FIELD_USERS_BLOCKED','blocked' );
	define ( 'FIELD_USERS_TIMEZONE','timezone' );
	define ( 'FIELD_USERS_TIMEFORMAT','timeformat' );
	define ( 'FIELD_USERS_THREADED','threaded' );
	define ( 'FIELD_USERS_LANGUAGE','language' );
	define ( 'FIELD_USERS_IP','ip' );
	define ( 'FIELD_USERS_PUBLIC_USER','public_user' );
	define ( 'FIELD_USERS_PUBLIC_PROFILE','public_profile' );
	define ( 'FIELD_USERS_PUBLIC_CONTACT_INFO','public_contact_info' );
	define ( 'FIELD_USERS_POSTSONPAGE','postsonpage' );
	define ( 'FIELD_USERS_HEADLINES','headlines' );
	
	define ( 'FIELD_USERS_PROFILE_NAME','name' );
	define ( 'FIELD_USERS_PROFILE_JOB','job' );
	define ( 'FIELD_USERS_PROFILE_AIM','aim' );
	define ( 'FIELD_USERS_PROFILE_ICQ','icq' );
	define ( 'FIELD_USERS_PROFILE_MSN','msn' );
	define ( 'FIELD_USERS_PROFILE_YAHOO','yahoo' );
	define ( 'FIELD_USERS_PROFILE_JABBER','jabber' );
	define ( 'FIELD_USERS_PROFILE_INTRESTS','intrests' );
	define ( 'FIELD_USERS_PROFILE_WEBSITE','website' );
	define ( 'FIELD_USERS_PROFILE_ADRESS','adress' );
	
	define ( 'TBL_IPBLOCKS',TBL_PREFIX .'ipblocks' );
	define ( 'FIELD_IPBLOCKS_IP','ip' );
	
	define ( 'PASSWORD_LENGTH',8 );
	define ( 'IP_USER',$_SERVER['REMOTE_ADDR'] );

	define ( 'SESSION_NAME','name' );
	define ( 'SESSION_PASSWORD','password' );
	define ( 'SESSION_TYPE','type' );	
	// some userforms
	define ( 'POST_NAME','name' );
	define ( 'POST_PASSWORD','password' );
	define ( 'POST_PASSWORD1','password1' );
	define ( 'POST_PASSWORD2','password2' );
	define ( 'POST_EMAIL','email' );
	define ( 'POST_POSTSONPAGE','postsonpage' );
	define ( 'POST_HEADLINES','headlines' );
	define ( 'POST_THEME','theme' );
	define ( 'POST_THREADED','threaded' );
	define ( 'POST_TIMEZONE','timezone' );
	define ( 'POST_TIMEFORMAT','timeformat' );
	define ( 'POST_LANGUAGE','language' );
	define ( 'POST_NEW_PASSWORD1','new_password1' );
	define ( 'POST_NEW_PASSWORD2','new_password12' );
	
	define ( 'get_registererror','register_error' ); // will be removed soon
	define ( 'get_loginerror','login_error' ); // will be removed soon
	define ( 'get_sendpassworderror','sendpassword_error' ); // will be removed soon
	define ( 'get_error','error' );
	define ( 'get_theme','theme' );
	define ( 'get_timezone','timezone' );
	define ( 'get_language','language' );
	define ( 'get_timeformat','timeformat' );
	define ( 'get_headlines','headlines' );
	define ( 'get_postsonpage','postsonpage' );
	define ( 'get_threaded','threaded' );
	define ( 'cookie_theme','theme' );
	define ( 'cookie_timezone','timezone' );
	define ( 'cookie_timeformat','timeformat' );
	define ( 'cookie_language','language' );
	define ( 'cookie_headlines','headlines' );
	define ( 'cookie_postsonpage','postsonpage' );
	define ( 'cookie_threaded','threaded' );
	
	define ( 'form_method','post' );
	define ( 'sendpassword_action','users.php?action=sendpassword' );
	define ( 'sendpasswordform_action','users.php?action=sendpasswordform' );
	define ( 'registerform_action', 'users.php?action=register' );
	define ( 'loginform_action', 'users.php?action=login' );
	define ( 'toregisterform_action', 'users.php?action=registerform' );
	define ( 'changeoptions_action', 'users.php?action=changeoptions' );
	define ( 'tochangeoptionsform','users.php?action=changeoptionsform' );
	define ( 'logout','users.php?action=logout' );

	define ( 'CONFIG_THREADED','threaded' );
?>