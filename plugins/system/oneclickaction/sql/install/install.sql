CREATE TABLE IF NOT EXISTS `#__oneclickaction_actions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) unsigned NOT NULL,
  `actionurl` varchar(4000) NOT NULL,
  `otp` char(64) NOT NULL,
  `expiry` datetime NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
