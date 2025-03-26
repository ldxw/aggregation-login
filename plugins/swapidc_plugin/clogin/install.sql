DROP TABLE IF EXISTS `第三方登录账号`;
CREATE TABLE `第三方登录账号` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `openid` varchar(150) NOT NULL,
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  UNIQUE KEY `account` (`openid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;