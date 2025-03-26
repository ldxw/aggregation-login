<?php defined('EMLOG_ROOT') or die('本页面禁止直接访问!');
/*
Plugin Name: 彩虹聚合登录
Version: 1.0
Plugin URL: https://www.clogin.cc/
Description: 通过彩虹聚合登录实现QQ登录
Author: 彩虹
*/

const CLOGIN_ROOT = EMLOG_ROOT . '/content/plugins/Clogin';
const CLOGIN_URL = BLOG_URL . 'content/plugins/Clogin';

function Clogin_menu() {
    echo '<a class="collapse-item" id="Clogin" href="plugin.php?plugin=Clogin">QQ登录</a>';
}

addAction('adm_menu_ext', 'Clogin_menu');

function Clogin_logining() {
    if (ISLOGIN) {
        header('location:/admin/index.php');
    } else {
        header('location:/?plugin=Clogin');
    }
}

function Clogin_qq_login() {
    ?>
    <div class="text-center">
        <a href="<?= CLOGIN_URL; ?>/Clogin_ajax.php?a=qq_login" class="ajax_qq_login am-icon-qq" title="QQ登录"></a>
    </div>
    <style>
        .am-icon-qq {
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            display: inline-block;
            width: 30px;
            height: 30px;
            font-size: 18px;
            line-height: 30px;
            border-radius: 50%;
            color: #fff;
            background-color: #26CEFE;
            text-align: center;
            background: url('<?= CLOGIN_URL; ?>/images/qq_login.png');
            background-size: cover;
            margin-top: 12px;
        }
    </style>
    <?
}

function Clogin_qq_bind() {
    ?>
    <hr>
    <h4 class="mt-4 mb-3" id="qq_connect">绑定 QQ</h4>
    <div>
        <?php
        $r = Database::getInstance();
        $row = $r->once_fetch_array("SELECT * FROM `" . DB_PREFIX . "user` WHERE `uid` =  '" . UID . "' ");
        $qq_login_openid = isset($row['qq_login_openid']) ? $row['qq_login_openid'] : '';
        if (empty($qq_login_openid)) : ?>
            <a id="qq_login" class="am-icon-qq" href="#qq_connect"></a>
            <a id="qq_login_unbind" class="text-danger" href="#qq_connect" style="display: none">解除QQ绑定</a>
        <?php else: ?>
            已绑定(<?=$qq_login_openid?>)<br/>
            <a id="qq_login" class="am-icon-qq" href="#qq_connect" style="display: none"></a>
            <a id="qq_login_unbind" class="text-danger" href="#qq_connect" onclick="return confirm('确定解除QQ绑定？解除后将无法使用QQ一键登录到本站')">解除QQ绑定</a>
            <hr>
        <?php endif; ?>
    </div>
    <style>
        .am-icon-qq {
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            display: inline-block;
            width: 30px;
            height: 30px;
            font-size: 18px;
            line-height: 30px;
            border-radius: 50%;
            color: #fff;
            background-color: #26CEFE;
            text-align: center;
            background: url('<?= CLOGIN_URL; ?>/images/qq_login.png');
            background-size: cover;
        }
    </style>
    <script>
        function bangdingok() {
            document.getElementById('qq_login').style.display = 'block';
            document.getElementById('qq_login_unbind').style.display = 'none';
        }

        function handleQQLoginClick() {
            window.open("<?php echo CLOGIN_URL; ?>/Clogin_ajax.php?a=qq_bind", "qq_bangding", "top=200,left=200,height=600, width=800, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, status=no");
        }

        function handleQQLoginJiebangClick() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo CLOGIN_URL; ?>/Clogin_ajax.php?a=qq_unbind');
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    if (data.code === '200') {
                        bangdingok();
                    }
                }
            };
            xhr.send(JSON.stringify({}));
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('qq_login').addEventListener('click', handleQQLoginClick);
            document.getElementById('qq_login_unbind').addEventListener('click', handleQQLoginJiebangClick);
        });
    </script>
    <?php
}

addAction('login_ext', 'Clogin_qq_login');
addAction('blogger_ext', 'Clogin_qq_bind');
