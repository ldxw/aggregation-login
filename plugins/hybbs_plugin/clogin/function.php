<?php
//插件安装时 执行的安装函数
function plugin_install(){
    $sql = S("Plugin");
    if(!$sql->query("
CREATE TABLE IF NOT EXISTS `hy_connect` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL,
  `type` varchar(20) NOT NULL,
  `openid` varchar(100) NOT NULL,
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `openid` (`openid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
"))
        return false;
    return true;
}
//插件卸载时 执行的安装函数
function plugin_uninstall(){
    $sql = S("Plugin");
    if(!$sql->query("DROP TABLE IF EXISTS `hy_connect`"))
        return false;

    return true;
}
