CREATE TABLE %prefix%helpindex (
  id serial NOT NULL,
  title varchar NOT NULL,
  lang varchar NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE %prefix%helpquestion (
  id int NOT NULL,
  question varchar NOT NULL,
  answer text NOT NULL,
  lang varchar NOT NULL,
  helpindex varchar NOT NULL,
  PRIMARY KEY (id)
)
