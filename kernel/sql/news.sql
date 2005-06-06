CREATE TABLE %prefix%categories (
  name varchar NOT NULL default '',
  language varchar NOT NULL default '',
  description text,
  image varchar default NULL,
  alternate varchar default NULL,
  PRIMARY KEY  (name,language)
);

CREATE TABLE %prefix%comments (
  id serial NOT NULL,
  message text NOT NULL,
  subject varchar NOT NULL default '',
  author varchar NOT NULL default '',
  date int NOT NULL default '0',
  id_news int NOT NULL default '0',
  comment_on_news varchar NOT NULL default 'No',
  id_on_comment int NOT NULL default '0',
  PRIMARY KEY (id)
);

CREATE TABLE %prefix%news (
  id serial NOT NULL,
  subject varchar NOT NULL default '',
  message text NOT NULL,
  language varchar NOT NULL default '',
  comments int default '0',
  author varchar NOT NULL default '',
  date int NOT NULL default '0',
  category varchar NOT NULL default '',
  PRIMARY KEY  (id)
);
