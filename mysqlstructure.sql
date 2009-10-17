CREATE TABLE IF NOT EXISTS `media` (
  `videoid` varchar(255) NOT NULL,
  `title` varchar(255) default NULL,
  `length` int(10) unsigned default NULL,
  `thumbnail` varchar(255) default NULL,
  `added` timestamp NOT NULL default '0000-00-00 00:00:00',
  `state` enum('processing','download queue','downloading','idle','playing') NOT NULL default 'processing',
  `changed` timestamp NULL default NULL on update CURRENT_TIMESTAMP,
  `size` int(10) unsigned default NULL,
  PRIMARY KEY  (`videoid`)
);

CREATE TABLE IF NOT EXISTS `vote` (
  `videoid` varchar(255) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `vote` tinyint(1) NOT NULL,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`videoid`,`ip`)
);

