CREATE TABLE %prefix%polls (
  id serial NOT NULL,
  active varchar (3) NOT NULL default 'No',
  question text NOT NULL,
  answers text NOT NULL,
  votes text NOT NULL,
  votedips text NOT NULL,
  votedusers text NOT NULL,
  language varchar (50) NOT NULL,
  PRIMARY KEY (id)
);
