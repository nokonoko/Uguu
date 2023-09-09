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
CREATE TABLE `accounts` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `email` varchar(255) default NULL
,  `pass` varchar(255) default NULL
,  `level` integer  default NULL
);
CREATE TABLE `blacklist` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `hash` char(40) default NULL
,  `originalname` varchar(255) default NULL
,  `time` integer default NULL
);
CREATE TABLE `ratelimit` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `iphash` char(40) default NULL
,  `time` integer default NULL
,  `files` integer default NULL
);
END TRANSACTION;
