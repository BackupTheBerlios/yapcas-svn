<?php
	define ( 'TBL_POLLS',TBL_PREFIX . 'polls' );
	define ( 'FIELD_POLL_ID','id' );
	define ( 'FIELD_POLL_ACTIVE','active' );
	define ( 'FIELD_POLL_LANGUAGE','language' );
	define ( 'FIELD_POLL_QUESTION','question' );
	define ( 'FIELD_POLL_CHOICES','answers' );
	define ( 'FIELD_POLL_RESULTS','votes' );
	define ( 'FIELD_POLL_VOTED_IPS','votedips' );
	define ( 'FIELD_POLL_VOTED_USERS','votedusers' );
	
	define ( 'POLLS_VOTE_ACTION','polls.php?action=vote' );
	define ( 'POST_VOTED_ON','voted_on' );
	
	define ( 'COOKIE_POLL','poll' );
?>