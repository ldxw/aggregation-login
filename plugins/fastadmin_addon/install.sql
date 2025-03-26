
CREATE TABLE IF NOT EXISTS `__PREFIX__clogin_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '会员ID',
  `type` varchar(10) DEFAULT '' COMMENT '登录类型',
  `openid` varchar(150) DEFAULT '' COMMENT '第三方账号ID',
  `openname` varchar(100) DEFAULT '' COMMENT '第三方会员昵称',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `openid` (`openid`,`type`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
