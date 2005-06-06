CREATE TABLE %prefix%polls (
  id serial NOT NULL,
  active char NOT NULL default 'No',
  question text NOT NULL,
  answers text NOT NULL,
  votes text NOT NULL,
  votedips text NOT NULL,
  votedusers text NOT NULL,
  language varchar NOT NULL default 'english',
  PRIMARY KEY  (id)
);
