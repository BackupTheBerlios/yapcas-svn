CREATE TABLE %prefix%pages (
  name varchar (50) NOT NULL,
  language varchar (50) NOT NULL,
  content text,
  shown_name varchar (50) NOT NULL,
  show_in_nav varchar (50) NOT NULL default 'Yes',
  show_in_user_nav varchar (50) NOT NULL default 'No',
  PRIMARY KEY  (name,language)
);
