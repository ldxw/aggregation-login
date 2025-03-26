<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

$sql = <<<EOF

CREATE TABLE IF NOT EXISTS `pre_clogin_member_qqconnect` (
  `uid` mediumint(8) unsigned NOT NULL,
  `openid` varchar(100) NOT NULL,
  `token` varchar(100) NOT NULL,
  `nickname` varchar(150) DEFAULT NULL,
  `faceimg` varchar(250) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  `isreset` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `isregister` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `openid` (`openid`)
) ENGINE=MyISAM;

EOF;
runquery($sql);

$finish = TRUE;
?>