<?php defined('EMLOG_ROOT') or die('access deined!');

function callback_init() {
    $DB = Database::getInstance();
    if ($DB->num_rows($DB->query("show columns from " . DB_PREFIX . "user like 'qq_login_openid'")) == 0) {
        $sql = "ALTER TABLE " . DB_PREFIX . "user ADD qq_login_openid VARCHAR(256) NOT NULL default ''";
        $DB->query($sql);
    }
}

function callback_rm() {
    $DB = Database::getInstance();
    $sql = "ALTER TABLE " . DB_PREFIX . "user DROP COLUMN qq_login_openid";
    $DB->query($sql);
}