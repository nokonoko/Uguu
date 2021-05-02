PRAGMA synchronous = OFF;
PRAGMA journal_mode = MEMORY;
BEGIN TRANSACTION;
CREATE TABLE `files` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `hash` char(40) default NULL
,  `originalname` varchar(255) default NULL
,  `filename` varchar(30) default NULL
,  `size` integer  default NULL
,  `date` integer default NULL
,  `ip` char(15) default NULL
);
END TRANSACTION;
