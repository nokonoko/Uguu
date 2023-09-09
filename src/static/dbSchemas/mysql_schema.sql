CREATE TABLE `files` (
 `id` int(20) unsigned NOT NULL auto_increment,
  `hash` char(40) DEFAULT NULL,
  `originalname` varchar(255) default NULL,
  `filename` varchar(30) default NULL,
  `size` int(15) DEFAULT NULL,
  `date` int(15) DEFAULT NULL,
  `ip` char(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `accounts` (
 `id` int(20) unsigned NOT NULL auto_increment,
  `email` varchar(255) default NULL,
  `pass` varchar(255) default NULL,
  `level` int(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `blacklist` (
 `id` int(20) unsigned NOT NULL auto_increment,
  `hash` char(40) DEFAULT NULL,
  `originalname` varchar(255) default NULL,
  `time` int(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `ratelimit`
(
    `id`     int(20) unsigned NOT NULL auto_increment,
    `iphash` char(40) DEFAULT NULL,
    `files`  int(15)  default NULL,
    `time`   int(15)  DEFAULT NULL,
    PRIMARY KEY (`id`)
);