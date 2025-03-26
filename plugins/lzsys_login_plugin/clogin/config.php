<?php

return $plugins_config = [
    "name" => "彩虹聚合登录",
    "type" => "客户相关",
    "describe" => "彩虹聚合登录-提供客户注册、登录、找回密码等服务,同类应用只能启用一个",
    "author" => "彩虹聚合登录",
    "qq" => "",
    "url" => "https://u.cccyun.cc/",
    "shop" => "https://u.cccyun.cc/",
    "plugin_version" => "1.0.0",
    "icon" => "login.png",
    "install" => "install.php",
    "uninstall" => "uninstall.php",
    "explain" => "https://u.cccyun.cc/",
    "login_a" => [[
        "type" => "input",
        "text" => "手机号",
        "placeholder" => "请输入手机号 新用户则自动注册",
        "name" => "tel",
        "defaultvalue" => "",
        "rules" => [
            "required" => true,
            "message" => "请输入手机号",
            "trigger" => "blur"
        ]
    ], [
        "type" => "input",
        "text" => "验证码",
        "placeholder" => "输入图形验证码",
        "name" => "imgcode",
        "defaultvalue" => "",
        "rules" => [
            "required" => true,
            "message" => "请输入图形验证码",
            "trigger" => "blur"
        ]
    ], [
        "type" => "input",
        "text" => "验证码",
        "placeholder" => "输入手机验证码",
        "name" => "vcode",
        "defaultvalue" => "",
        "rules" => [
            "required" => true,
            "message" => "请输入手机验证码",
            "trigger" => "blur"
        ]
    ], [
        "type" => "pass",
        "text" => "密码",
        "placeholder" => "请设置一个登录密码",
        "name" => "pass",
        "defaultvalue" => "",
        "rules" => [
            "required" => false,
            "message" => "请输入密码",
            "trigger" => "blur"
        ]
    ], [
        "type" => "input",
        "text" => "QQ号",
        "placeholder" => "QQ号 方便业务相关联系",
        "name" => "qq",
        "defaultvalue" => "",
        "rules" => [
            "required" => false,
            "message" => "请输入QQ号",
            "trigger" => "blur"
        ]
    ], [
        "type" => "input",
        "text" => "邮箱",
        "placeholder" => "电子邮箱、用于登录、找回密码，邮件通知等",
        "name" => "email",
        "defaultvalue" => "",
        "rules" => [
            "required" => false,
            "message" => "请输入邮箱",
            "trigger" => "blur"
        ]
    ]],
    "login_b" => [[
        "type" => "input",
        "text" => "账号",
        "placeholder" => "账号，支持客户账号、手机号、邮箱",
        "name" => "tel",
        "defaultvalue" => "",
        "rules" => [
            "required" => true,
            "message" => "请输入登录账号",
            "trigger" => "blur"
        ]
    ], [
        "type" => "pass",
        "text" => "密码",
        "placeholder" => "密码，如忘记密码请使用验证码登录",
        "name" => "pass",
        "defaultvalue" => "",
        "rules" => [
            "required" => true,
            "message" => "请输入密码",
            "trigger" => "blur"
        ]
    ]],
    "user_pass" => [[
        "type" => "input",
        "text" => "手机号",
        "placeholder" => "请输入手机号",
        "name" => "tel",
        "defaultvalue" => "",
        "rules" => [
            "required" => true,
            "message" => "请输入手机号",
            "trigger" => "blur"
        ]
    ], [
        "type" => "input",
        "text" => "验证码",
        "placeholder" => "输入图形验证码",
        "name" => "imgcode",
        "defaultvalue" => "",
        "rules" => [
            "required" => true,
            "message" => "请输入图形验证码",
            "trigger" => "blur"
        ]
    ], [
        "type" => "input",
        "text" => "验证码",
        "placeholder" => "输入手机验证码",
        "name" => "vcode",
        "defaultvalue" => "",
        "rules" => [
            "required" => true,
            "message" => "请输入手机验证码",
            "trigger" => "blur"
        ]
    ], [
        "type" => "pass",
        "text" => "密码",
        "placeholder" => "设置新密码",
        "name" => "pass",
        "defaultvalue" => "",
        "rules" => [
            "required" => true,
            "message" => "请输入密码",
            "trigger" => "blur"
        ]
    ]],
    "login_c" => [[
        "type" => "input",
        "text" => "邮箱",
        "placeholder" => "请输入邮箱账号",
        "name" => "email",
        "defaultvalue" => "",
        "rules" => [
            "required" => true,
            "message" => "请输入邮箱账号",
            "trigger" => "blur"
        ]
    ], [
        "type" => "input",
        "text" => "验证码",
        "placeholder" => "输入邮箱验证码",
        "name" => "ecode",
        "defaultvalue" => "",
        "rules" => [
            "required" => true,
            "message" => "请输入邮箱验证码",
            "trigger" => "blur"
        ]
    ]],
    "login_d" => [[
        "type" => "input",
        "text" => "邮箱",
        "placeholder" => "请输入电子邮箱",
        "name" => "email",
        "defaultvalue" => "",
        "rules" => [
            "required" => true,
            "message" => "请输入邮箱",
            "trigger" => "blur"
        ]
    ], [
        "type" => "input",
        "text" => "验证码",
        "placeholder" => "输入邮箱验证码",
        "name" => "ecode",
        "defaultvalue" => "",
        "rules" => [
            "required" => true,
            "message" => "请输入邮箱验证码",
            "trigger" => "blur"
        ]
    ], [
        "type" => "input",
        "text" => "手机号",
        "placeholder" => "请输入手机号 用于发送短信通知、找回密码等",
        "name" => "tel",
        "defaultvalue" => "",
        "rules" => [
            "required" => true,
            "message" => "请输入手机号",
            "trigger" => "blur"
        ]
    ], [
        "type" => "pass",
        "text" => "密码",
        "placeholder" => "设置登录密码",
        "name" => "pass",
        "defaultvalue" => "",
        "rules" => [
            "required" => false,
            "message" => "请输入密码",
            "trigger" => "blur"
        ]
    ], [
        "type" => "input",
        "text" => "QQ号",
        "placeholder" => "QQ号 方便业务相关联系",
        "name" => "qq",
        "defaultvalue" => "",
        "rules" => [
            "required" => false,
            "message" => "请输入QQ号",
            "trigger" => "blur"
        ]
    ]]
];
