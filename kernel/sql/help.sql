CREATE TABLE %prefix%helpcategory (
	id int,
	parent int default '0',
	PRIMARY KEY (id)
);

CREATE TABLE %prefix%transcategory (
	id int,
	langcode varchar (5),
	name varchar (50),
	PRIMARY KEY (id,langcode)
);

CREATE TABLE %prefix%helpquestions (
	id int,
	question text,
	answer text,
	langcode text,
	category int,
	PRIMARY KEY (id)
);
