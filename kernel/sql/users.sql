CREATE TABLE %prefix%ipblocks (
  ip varchar NOT NULL default '',
  date int NOT NULL default '0',
  reason text NOT NULL,
  PRIMARY KEY  (ip)
);

CREATE TABLE %prefix%user_profile (
  name varchar NOT NULL default '',
  icq int default NULL,
  aim varchar default NULL,
  msn varchar default NULL,
  yahoo varchar default NULL,
  jabber varchar default NULL,
  website varchar default NULL,
  adress varchar default NULL,
  job varchar default NULL,
  intrests varchar default NULL,
  PRIMARY KEY  (name)
);

CREATE TABLE %prefix%users (
  name varchar NOT NULL default '',
  password varchar NOT NULL default '',
  email varchar NOT NULL default '',
  type varchar NOT NULL default 'users',
  language varchar default NULL,
  theme varchar default NULL,
  ip text NOT NULL,
  threaded varchar default NULL,
  postsonpage int default NULL,
  timezone int default NULL,
  timeformat varchar default NULL,
  headlines int default NULL,
  public_user varchar NOT NULL default 'Yes',
  public_profile varchar NOT NULL default 'Yes',
  public_contact_info varchar NOT NULL default 'No',
  activated varchar NOT NULL default 'Yes',
  blocked varchar NOT NULL default 'No',
  PRIMARY KEY (name)
);
