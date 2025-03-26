<?php defined('EMLOG_ROOT') or die('本页面禁止直接访问!');

function plugin_setting_view() {
    $plugin_storage = Storage::getInstance('Clogin');
    $oauth_apiurl = $plugin_storage->getValue('oauth_apiurl');
    $oauth_appid = $plugin_storage->getValue('oauth_appid');
    $oauth_appkey = $plugin_storage->getValue('oauth_appkey');
    ?>
    <?php if (isset($_GET['setting'])): ?>
        <div class="alert alert-success">插件设置完成</div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">插件设置失败</div>
    <?php endif; ?>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">彩虹聚合登录接口设置</h1>
    </div>
    <div class="card shadow mb-4 mt-2">
        <div class="card-body">
            <form action="plugin.php?plugin=Clogin&action=setting" method="post">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default card-view">
                            <div class="tab-content">
                                <div class="form-group">
                                    <label>登录接口地址</label>
                                    <input name="oauth_apiurl" type="text" class="form-control" value="<?= $oauth_apiurl ?>"/>
                                </div>
                                <div class="form-group">
                                    <label>APP ID</label>
                                    <input size="12" name="oauth_appid" type="text" class="form-control" value="<?= $oauth_appid ?>"/>
                                </div>
                                <div class="form-group">
                                    <label>APP KEY</label>
                                    <input size="32" name="oauth_appkey" type="text" class="form-control" value="<?= $oauth_appkey ?>"/>
                                </div>
                            </div>
                            <div class="table-wrap" style="padding-top:10px">
                                <div class="form-group" style="padding-top:10px">
                                    <input type="submit" value="保 存" class="submit btn btn-success"/></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        setTimeout(hideActived, 3600);
        $("#menu_category_ext").addClass('active');
        $("#menu_ext").addClass('show');
        $("#Clogin").addClass('active');
    </script>
    <?php
}

function plugin_setting() {
    $plugin_storage = Storage::getInstance('Clogin');
    $plugin_storage->setValue('oauth_apiurl', Input::postStrVar('oauth_apiurl'));
    $plugin_storage->setValue('oauth_appid', Input::postStrVar('oauth_appid'));
    $plugin_storage->setValue('oauth_appkey', Input::postStrVar('oauth_appkey'));
    $DB = Database::getInstance();
    if ($DB->num_rows($DB->query("show columns from " . DB_PREFIX . "user like 'qq_login_openid'")) == 0) {
        $sql = "ALTER TABLE " . DB_PREFIX . "user ADD qq_login_openid VARCHAR(256) NOT NULL default ''";
        $DB->query($sql);
    }
}