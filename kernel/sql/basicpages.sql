;
INSERT INTO %prefix%pages
	(name,language,content,shown_name,show_in_nav,show_in_user_nav)
	VALUES ('index.php','%language%','','%shown_index%','%show_index_in_nav%','%show_index_in_user_nav%');
INSERT INTO %prefix%pages
	(name,language,content,shown_name,show_in_nav,show_in_user_nav)
	VALUES ('users.php?action=logout','%language%','','%shown_logout%','%show_logout_in_nav%','%show_logout_in_user_nav%');
