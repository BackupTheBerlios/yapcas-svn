CREATE TABLE %prefix%pages (
  name varchar NOT NULL default '',
  language varchar NOT NULL default '',
  content text,
  shown_name varchar NOT NULL default '',
  show_in_nav varchar NOT NULL default 'Yes',
  show_in_user_nav varchar NOT NULL default 'No',
  PRIMARY KEY  (name,language)
);
