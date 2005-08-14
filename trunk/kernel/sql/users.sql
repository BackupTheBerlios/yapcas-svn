CREATE TABLE %prefix%ipblocks (
  ip varchar(30) NOT NULL,
  date int NOT NULL,
  reason text NOT NULL,
  PRIMARY KEY (ip)
);

CREATE TABLE %prefix%user_profile (
  name varchar (50) NOT NULL,
  icq varchar (125) default NULL,
  aim varchar (125) default NULL,
  msn varchar (125) default NULL,
  yahoo varchar (125) default NULL,
  jabber varchar (125) default NULL,
  website varchar (255) default NULL,
  adress varchar (255) default NULL,
  job varchar (255) default NULL,
  intrests varchar (255) default NULL,
  threaded varchar (3) default NULL,
  postsonpage int default NULL,
  timezone int,
  timeformat varchar (25) default NULL,
  headlines int default NULL,
  uilanguage varchar (50) default NULL,
  contentlanguage varchar (5) default NULL,
  theme varchar (50) default NULL,
  PRIMARY KEY (name)
);

CREATE TABLE %prefix%users (
  name varchar (50) NOT NULL,
  password varchar (50) NOT NULL,
  email varchar (255) NOT NULL,
  type varchar (50) NOT NULL default 'users',
  ip text default NULL,
  public_user varchar (3) NOT NULL default 'Yes',
  public_profile varchar (3) NOT NULL default 'Yes',
  public_contact_info varchar (3) NOT NULL default 'No',
  activated varchar (3) NOT NULL,
  blocked varchar (3) NOT NULL,
  PRIMARY KEY (name)
);

CREATE TABLE %prefix%activate_queue (
  username varchar (50) NOT NULL,
  id varchar (32) NOT NULL,
  start int NOT NULL,
  PRIMARY KEY (username)
);
