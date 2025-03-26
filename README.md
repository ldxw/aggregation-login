# 彩虹聚合登录插件集成指南

## 目录
- [插件列表](#插件列表)
  - [1. 魔方财务系统插件](#idcsmart) | [进入库](https://github.com/ldxw/aggregation-login/tree/main/plugins/idcsmart)
  - [2. Discuz!X 论坛插件](#discuzx_plugins) | [进入库](https://github.com/ldxw/aggregation-login/tree/main/plugins/discuzx_plugins)
  - [3. SWAPIDC 插件](#swapidc_plugin) | [进入库](https://github.com/ldxw/aggregation-login/tree/main/plugins/swapidc_plugin)
  - [4. WHMCS 插件](#whmcs_plugin) | [进入库](https://github.com/ldxw/aggregation-login/tree/main/plugins/whmcs_plugin)
  - [5. WordPress 博客插件](#wordpress_plugin) | [进入库](https://github.com/ldxw/aggregation-login/tree/main/plugins/wordpress_plugin)
  - [6. Z-Blog 博客插件](#z-blog-博客插件)
    - [6.1 LayCenter 插件](#zblog_laycenter_plugin) | [进入库](https://github.com/ldxw/aggregation-login/tree/main/plugins/zblog_laycenter_plugin)
    - [6.2 YtUser 插件](#zblog_ytuser_plugin) | [进入库](https://github.com/ldxw/aggregation-login/tree/main/plugins/zblog_ytuser_plugin)
  - [7. Emlog 博客插件](#emlog_blog_plugin)
    - [7.1 基础版](#emlog_plugin) | [进入库](https://github.com/ldxw/aggregation-login/tree/main/plugins/emlog_plugin)
    - [7.2 Pro 版](#emlog_pro_plugin) | [进入库](https://github.com/ldxw/aggregation-login/tree/main/plugins/emlog_pro_plugin)
  - [8. Typecho 博客插件](#typecho_plugin) | [进入库](https://github.com/ldxw/aggregation-login/tree/main/plugins/typecho_plugin)
  - [9. 苹果 CMS V10 插件](#clogin_maccms10) | [进入库](https://github.com/ldxw/aggregation-login/tree/main/plugins/clogin_maccms10)
  - [10. FastAdmin 插件](#fastadmin_addon) | [进入库](https://github.com/ldxw/aggregation-login/tree/main/plugins/fastadmin_addon)
  - [11. Flarum 论坛插件](#flarum_clogin_oauth)
- [其他插件](#其他插件)
- [贡献指南](#贡献指南)
- [来源地址](#来源地址)

---

## <a name="插件列表"></a>插件列表

### <a name="idcsmart"></a>1. 魔方财务系统插件
- **插件名称**：idcsmart
- **支持登录方式**：QQ、微信、支付宝、微博、百度
- **安装路径**：`/public/plugins/oauth`
- **使用方法**：
  1. 上传并解压到指定目录
  2. 后台【系统】->【第三方登录】配置并开启

### <a name="discuzx_plugins"></a>2. Discuz!X 论坛插件
- **插件名称**：discuzx_plugins
- **支持登录方式**：QQ、微信
- **兼容版本**：DiscuzX 3.2~3.5（UTF-8/GBK/Big5）
- **安装路径**：`/source/plugin`
- **使用方法**：上传解压后后台启用

### <a name="swapidc_plugin"></a>3. SWAPIDC 插件
- **插件名称**：swapidc_plugin
- **支持登录方式**：QQ、微信、微博、支付宝
- **安装路径**：`/swap_mac/swap_plugins`
- **使用方法**：
  1. 上传解压
  2. 导入 `install.sql` 到数据库
  3. 后台插件设置开启

### <a name="whmcs_plugin"></a>4. WHMCS 插件
- **插件名称**：whmcs_plugin
- **支持登录方式**：QQ、微信、微博、支付宝
- **兼容版本**：WHMCS 8.x
- **安装路径**：根目录
- **使用方法**：
  1. 上传解压
  2. 后台开启插件模块
  3. 配置信息并修改模板变量

### <a name="wordpress_plugin"></a>5. WordPress 博客插件
- **插件名称**：wordpress_plugin
- **支持登录方式**：QQ、微信、支付宝、微博、百度、华为、钉钉、谷歌、微软、Facebook、Twitter
- **安装路径**：`/wp-content/plugins`
- **使用方法**：上传解压后后台启用
- **注意事项**：部分主题（如子比主题）已默认集成

### <a name="z-blog-博客插件"></a>6. Z-Blog 博客插件

#### <a name="zblog_laycenter_plugin"></a>6.1 LayCenter 插件
- **插件名称**：zblog_laycenter_plugin
- **依赖**：需先安装 LayCenter 插件
- **安装路径**：`/zb_users/LayCenter`
- **支持登录方式**：QQ、微信、支付宝、微博

#### <a name="zblog_ytuser_plugin"></a>6.2 YtUser 插件
- **插件名称**：zblog_ytuser_plugin
- **依赖**：需先安装 YtUser 插件
- **安装路径**：`/zb_users/plugin/YtUser`
- **使用方法**：
  1. 上传覆盖
  2. 修改 `login.php` 接口地址
  3. 后台填写 QQ 登录的 appid 和 appkey

### <a name="emlog_blog_plugin"></a>7. Emlog 博客插件

#### <a name="emlog_plugin"></a>7.1 基础版
- **插件名称**：emlog_plugin
- **兼容版本**：Emlog 5.3.1

#### <a name="emlog_pro_plugin"></a>7.2 Pro 版
- **插件名称**：emlog_pro_plugin
- **安装路径**：`/content/plugins`
- **支持登录方式**：QQ

### <a name="typecho_plugin"></a>8. Typecho 博客插件
- **插件名称**：typecho_plugin
- **支持登录方式**：QQ、微信、支付宝、微博
- **安装路径**：`/usr/plugins`
- **使用方法**：
  1. 上传解压后后台启用
  2. 修改模板代码添加登录按钮
- **注意事项**：
  - Typecho 1.2.1 需下载 [Request.zip](https://github.com/ldxw/aggregation-login/tree/main/plugins/zblog_ytuser_plugin/Request.zip) 修复保存问题

### <a name="clogin_maccms10"></a>9. 苹果 CMS V10 插件
- **插件名称**：clogin_maccms10
- **支持登录方式**：QQ、微信
- **安装路径**：根目录
- **使用方法**：
  1. 上传解压
  2. 后台【整合登录配置】设置密钥
- **配置文件**：`/extend/login/ThinkOauth.php` 可修改接口地址

### <a name="fastadmin_addon"></a>10. FastAdmin 插件
- **插件名称**：fastadmin_addon
- **安装路径**：`/vendor/karsonzhang/fastadmin-addons/src/addons/`
- **使用方法**：
  1. 打开 `/vendor/karsonzhang/fastadmin-addons/src/addons/Service.php`，注释掉 `Service::valid($params);` 这一行。
  2. 在后台插件管理中，点击本地安装。

### <a name="flarum_clogin_oauth"></a>11. Flarum 论坛插件
- **安装方式**：
  ```bash
  composer require cccyun/flarum-clogin-oauth

## <a name="来源地址"></a>来源地址
本文档内容整理自 [彩虹云官方博客](https://blog.cccyun.cn/post-430.html)。