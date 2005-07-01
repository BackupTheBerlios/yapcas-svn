CREATE TABLE %prefix%helpcategory (
	id int,
	parent int default '0',
	PRIMARY KEY (id)
);

CREATE TABLE %prefix%transcategory (
	id int,
	langcode varchar,
	name varchar,
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
