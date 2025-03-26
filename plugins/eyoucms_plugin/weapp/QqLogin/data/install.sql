SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for #@__weapp_qqlogin
-- ----------------------------
CREATE TABLE IF NOT EXISTS `#@__weapp_qqlogin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `openid` varchar(100) NOT NULL COMMENT '第三方登录UID',
  `nickname` varchar(255) NOT NULL DEFAULT '' COMMENT '昵称',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `sta` tinyint(1) NOT NULL DEFAULT '0' COMMENT '修改用户名或者绑定已有账号标识',
  PRIMARY KEY (`id`),
  KEY `users_id` (`users_id`),
  KEY `openid` (`openid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
