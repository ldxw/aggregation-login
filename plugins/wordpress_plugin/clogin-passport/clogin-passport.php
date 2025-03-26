<?php
/**
 * Plugin Name: Clogin Passport
 * Plugin URI: https://wordpress.org/plugins/clogin-passport/
 * Description: Clogin Passport for Wordpress, Many Oauth 2.0 log in methods.
 * Version: 1.1
 * Author: cccyun
 * Author URI: https://u.cccyun.cc/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: clogin-passport
 */
namespace clogin_passport;

use clogin_passport\lib\Oauth;

define('CLOGIN_PASSPORT_DIR', __DIR__ . DIRECTORY_SEPARATOR);
define('CLOGIN_PASSPORT_URL', plugin_dir_url(__FILE__));

include_once CLOGIN_PASSPORT_DIR . 'lib/Oauth.php';

class core {
	var $text_domain = 'clogin-passport';
	var $options;
	var $modules_name = [
		'qq' => 'QQ',
		'wx' => 'Wechat',
		'alipay' => 'Alipay',
		'sina' => 'Weibo',
		'baidu' => 'Baidu',
		'huawei' => 'Huawei',
		'google' => 'Google',
		'microsoft' => 'Microsoft',
		'facebook' => 'Facebook',
		'twitter' => 'Twitter',
		'dingtalk' => 'Dingtalk',
		'github' => 'GitHub',
		'gitee' => 'Gitee',
	];

	public function __construct() {
		$this->options['types'] = get_option('clogin-passport-types', array());
		$configs = get_option('clogin-passport-configs', array());
		$this->options['appurl'] = $configs['appurl'];
		$this->options['appid'] = $configs['appid'];
		$this->options['appkey'] = $configs['appkey'];
		$this->options['avatar_priority'] = get_option('clogin-passport-avatar-priority', 9999);
		$this->options['automatic_register'] = get_option('clogin-passport-automatic-register', 0);
	}

	public function init() {
		add_action( 'plugins_loaded', array($this, 'load_language') );
		add_action( 'admin_menu', array($this, 'admin_menu') );
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_init', array($this, 'register_settings') );

		if (!empty($this->options['types'])) {
			add_action( 'um_after_form', array($this, 'call_passport') ); // for Ultimate Member
			add_action( 'login_form', array($this, 'call_passport') ); // for wp-login.php
			add_action( 'woocommerce_login_form_end', array($this, 'call_passport') ); // for woocommerce form-login.php
			add_filter( 'login_form_middle', array($this, 'login_form_middle'), 10, 2 ); // for function wp_login_form()
			add_action( 'rest_api_init', array($this, 'register_restapi') );
			add_filter( 'get_avatar', array($this, 'get_avatar'), $this->options['avatar_priority'], 6 );

			add_action( 'show_user_profile', array($this, 'call_binding_social_media_account') );
		}

		add_filter( 'manage_users_columns', array($this, 'manage_users_columns') );
		add_filter( 'manage_users_custom_column', array($this, 'manage_users_custom_column'), 10, 3 );

		add_action( 'clogin-passport', array($this, 'passport') );
		add_action( 'binding_social_media_account', array($this, 'binding_social_media_account') );

		// 必须执行登录post操作才会显示error
		add_filter( 'wp_login_errors', array($this, 'wp_login_errors'), 0, 2 );
	}

	// 显示登录错误
	public function wp_login_errors($errors, $redirect_to) {
		@session_start();
		if (isset($_SESSION['clogin-passport-login-error']) && $_SESSION['clogin-passport-login-error'] == 1) {
			$errors->add( 'cannot-auto-register', __( 'Your have to register a new account and binded this social media account, then you can login via this social media account.', $this->text_domain ), 'message' );
			unset($_SESSION['clogin-passport-login-error']);
		}
		@session_write_close();
		return $errors;
	}

	public function manage_users_columns($column_headers){
		$column_headers['openid'] = __("Open ID", $this->text_domain);
		return $column_headers;
	}

	public function manage_users_custom_column($value, $column_name, $id) {
		if( $column_name == 'openid' ) {
			$types = $this->options['types'];
			foreach($types as $type){
				$value .= '<p><strong>'.__($this->modules_name[$type], $this->text_domain).':</strong> <em>'. get_the_author_meta( 'clogin_passport_'.$type, $id ) . '</em></p>';
			}
			return $value;
		}
	}

	public function call_passport() {
		do_action( 'clogin-passport' );
	}

	public function call_binding_social_media_account($profileuser) {
		do_action( 'binding_social_media_account', $profileuser );
	}

	public function sanitize_callback($value) {
		return $value;
	}

	public function binding_social_media_account($profileuser) {
		@session_start();
		$_SESSION['redirect_uri'] = admin_url('/profile.php');
		@session_write_close();
?>
	<h3><?php _e( 'Social Media Accounts', $this->text_domain ); ?></h3>
	<table id="binding_social_media_account" class="form-table">
		<tbody>
			<?php $this->profile_form($profileuser); ?>
		</tbody>
	</table>
<?php
	}

	private function profile_form($profileuser) {
		$types = $this->options['types'];
		foreach($types as $type){
			$bind_url = home_url('index.php/wp-json/clogin-passport/login/'.$type);
?>
		<tr>
			<th><label><?php _e($this->modules_name[$type], $this->text_domain); ?></label></th>
			<td>
			<?php
				$openid = get_user_meta( $profileuser->ID, 'clogin_passport_'.$type, true );
				if (empty($openid)) {
?>
					<a class="button button-primary" href="<?php echo $bind_url; ?>"><?php _e('Bind Now', $this->text_domain); ?></a>
<?php
				} else {
?>
					<a class="button" href="<?php echo $bind_url; ?>" title="<?php echo $openid; ?>"><?php _e('Rebind', $this->text_domain); ?></a>
<?php
				}
?>
			</td>
		</tr>
<?php
		}
	}

	public function get_avatar($avatar, $id_or_email, $size, $default, $alt, $args) {
		$user_id = '';
		if ( filter_var($id_or_email, FILTER_VALIDATE_EMAIL) ) {
			$user = get_user_by( 'email', $id_or_email );
			$user_id = $user ? $user->ID : null;
		} else {
			$user_id = $id_or_email;
		}
		if (!empty($user_id)) {
			$url = get_user_meta($user_id ,'clogin_passport_avatar' ,true);
			if ($url) {
				$class = implode(' ', array( 'avatar', 'avatar-' . $size, 'photo' ));
				if ($url) $avatar = sprintf(
					"<img alt='%s' src='%s' srcset='%s' class='%s' height='%d' width='%d' %s/>",
					esc_attr( $alt ),
					esc_url( $url ),
					esc_attr( "$url 2x" ),
					esc_attr( $class ),
					(int) $args['height'],
					(int) $args['width'],
					$args['extra_attr']
				);
			}
		}
		return $avatar;
	}

	private function delete_user_meta($user_id, $meta_key, $meta_value) {
		global $wpdb;
		if ($meta_value) {
			$user_metas = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'usermeta WHERE `meta_key` = "'.$meta_key.'" AND `meta_value` = "'.$meta_value.'"', OBJECT );
			if (!empty($user_metas)) foreach ($user_metas as $user_meta) {
				// 只有和当前用户id不一样，且metakey一样的才会删掉
				if ($user_meta->user_id != $user_id) delete_user_meta($user_meta->user_id, $user_meta->meta_key);
			}
		}
	}

	// quick login by login name
	private function login($user_login, $redirect=true) {
		@session_start();
		$user = get_user_by('login', $user_login);
		$user_id = $user->ID;
		wp_set_current_user($user_id, $user_login);
		wp_set_auth_cookie($user_id, true);
		do_action( 'wp_login', $user_login, $user );
		if ( isset($_SESSION['redirect_uri']) && !empty($_SESSION['redirect_uri']) ) {
			$redirect_uri = $_SESSION['redirect_uri'];
			unset($_SESSION['redirect_uri']);
		} else $redirect_uri = home_url();
		if (preg_match('/\.css$/i', $redirect_uri)) $redirect_uri = home_url();
		if ($redirect) {
			wp_safe_redirect( $redirect_uri );
			exit;
		}
	}

	private function get_current_user_id() {
		if (!empty($_COOKIE)) foreach ($_COOKIE as $key => $value) {
			if (preg_match('/^wordpress_logged_in_.*?$/i', $key, $match)) {
				$value = explode('|', $value);
				if (count($value)) {
					$user = get_user_by( 'login', $value[0] );
					return $user->ID;
				}
				break;
			}
		}
		return false;
	}

	/**
	 * check is user logged in from cookie
	**/
	private function is_user_logged_in() {
		if (!empty($_COOKIE)) foreach ($_COOKIE as $key => $value) {
			if (preg_match('/^wordpress_logged_in_.*?$/i', $key, $match)) {
				$value = explode('|', $value);
				return count($value);
			}
		}
		return false;
	}

	public function register_restapi() {
		$namespace = 'clogin-passport';
		// index.php/wp-json/clogin-passport/login/qq
		register_rest_route( $namespace, 'login/(?P<slug>\w+)', array(
			'methods' => 'GET',
			'callback' => array($this, 'oauth_login'),
			'update_callback' => null,
			'schema' => null
		) );
		// index.php/wp-json/clogin-passport/callback
		register_rest_route( $namespace, 'callback', array(
			'methods' => 'GET',
			'callback' => array($this, 'oauth_callback'),
			'update_callback' => null,
			'schema' => null
		) );
	}

	public function oauth_login($data) {
		@session_start();
		$slug = $data['slug'];
		if(!$this->is_activated($slug))exit(__('This login method is not enabled!', $this->text_domain));
		if(isset($_GET['rurl']) && $_GET['rurl']){
			$_SESSION['redirect_uri'] = $_GET['rurl'];
		}
		$this->options['callback'] = home_url('index.php/wp-json/clogin-passport/callback/');
		$Oauth = new Oauth($this->options);
		$arr = $Oauth->login($slug);
		if(isset($arr['code']) && $arr['code']==0){
			header("Location: ".$arr['url']);
		}elseif(isset($arr['code'])){
			exit(__('Failed to get the login url!', $this->text_domain) . $arr['msg']);
		}else{
			exit(__('Failed to get the login url!', $this->text_domain));
		}
	}

	public function oauth_callback($data) {
		@session_start();
		if (!isset($_GET['state']) || !isset($_GET['code'])) wp_redirect( wp_login_url() );
		if($_GET['state'] != $_SESSION['Oauth_state'] ){
			exit(__('The state does not match. You may be a victim of CSRF.', $this->text_domain));
		}

		$type = isset($_GET['type'])?$_GET['type']:exit('no type');
		
		$Oauth = new Oauth($this->options);
		$arr = $Oauth->callback();
		if(isset($arr['code']) && $arr['code']==0){
			unset($_SESSION['Oauth_state']);

			$openid = $arr['social_uid'];
			$nickname = $arr['nickname'];
			$avatar = $arr['faceimg'];

			$user_id = $this->get_current_user_id();

			if($user_id){
				$this->delete_user_meta($user_id, 'clogin_passport_'.$type, $openid);
				update_user_meta( $user_id, 'clogin_passport_'.$type, $openid );
				if(!empty($avatar)) update_user_meta( $user_id, 'clogin_passport_avatar', set_url_scheme($avatar) );
				if (isset($_SESSION['redirect_uri'])) {
					$redirect_uri = $_SESSION['redirect_uri'];
				} else {
					$redirect_uri = home_url();
				}
				wp_safe_redirect( $redirect_uri );
			}else{
				$user = $this->is_openid_exists($type, $openid);
				if ($user) {
					$user_login = $user->user_login;
					$this->login($user_login);
				}else{
					if (get_option( 'users_can_register' ) && $this->options['automatic_register']) {
						$user_login = current_time('timestamp');
						$random_password = wp_generate_password();
						$user_id = wp_create_user($user_login, $random_password);
						$userdata = array(
							'ID' => $user_id,
							'first_name' => $nickname,
							'user_nicename' => $user_login,
							'nickname' => $nickname,
							'display_name' => $nickname
						);
						wp_update_user( $userdata );
						update_user_meta( $user_id, 'clogin_passport_'.$type, $openid );
						if(!empty($avatar)) update_user_meta( $user_id, 'clogin_passport_avatar', set_url_scheme($avatar) );
						$this->login($user_login);
					}else{
						$_SESSION['clogin-passport-login-error'] = 1;
						wp_redirect( wp_login_url() );
					}
				}
			}
		}elseif(isset($arr['code'])){
			exit(__('Failed to get the callback!', $this->text_domain) . $arr['msg']);
		}else{
			exit(__('Failed to get the login data!', $this->text_domain));
		}
	}

	/*
	 * 用于多站点模式检测该用户是否是当前
	*/
	private static function is_user_member_of_blog($user_data) {
		if (is_multisite()) {
			// 如果找到有用户曾经登陆过，则检测该用户是否属于当前子站点
			$user_id = $user_data->ID;
			$blog_id = $GLOBALS['blog_id'];
			if (!is_user_member_of_blog($user_id, $blog_id)) {
				//如果不是当前子站点用，则添加
				$role = in_array('administrator', $user_data->roles) ? 'administrator' : 'subscriber';
				add_user_to_blog( $blog_id, $user_id, $role );
			}
		}
	}

	private function is_openid_exists($type, $openid) {
		$args = array(
			'meta_key'     => 'clogin_passport_'.$type,
			'meta_value'   => $openid,
			'meta_compare' => '='
		);
		if (is_multisite()) {
			$sites = get_sites();
			$blog_ids = array();
			foreach ($sites as $site) {
				$blog_ids[] = $site->blog_id;
			}
			$args['blog_id'] = $blog_ids;
		}
		$users = get_users($args);
		if (!empty($users)) {
			$user_data = $users[0]->data;
			self::is_user_member_of_blog($user_data);
			return $user_data;
		} else return false;
	}

	public function login_form_middle($content, $args) {
		ob_start();
		$this->passport();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	public function passport() {
		// for Ultimate Member checking template mode
		if (func_num_args() && func_get_arg(0)) {
			$args = func_get_arg(0);
			if (!in_array($args['mode'], array('login', 'register'))) return;
		}

		global $pagenow;
		@session_start();
		if ( $pagenow != 'wp-login.php' ) $_SESSION['redirect_uri'] = isset($_SERVER['HTTP_REFERER']) ? set_url_scheme($_SERVER['HTTP_REFERER']) : set_url_scheme("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
		@session_write_close();
?>
	<style>
	#clogin-passport-container {
		padding: 0 0 10px;
	}
	#clogin-passport-container .third-party-login-label {
		margin-bottom: 5px;
	}
	#clogin-passport-container a {
		display: inline-block;
		padding: 3px;
		background: #f7f7f7;
		border-radius: 50%;
		width: 32px;
		height: 32px;
		position: relative;
		box-sizing: initial;
		margin: 3px;
		left: 0;
		top: 0;
	}
	#clogin-passport-container a:hover {
		background: #fff;
	}
	#clogin-passport-container img {
		width: 32px;
		height: 32px;
		margin: 0;
		padding: 0;
	}
	</style>
	<div id="clogin-passport-container">
		<p class="third-party-login-label"><label><?php _e('Third-Party Login', $this->text_domain); ?></label></p>
		<p><?php $this->login_form_buttons() ?></p>
	</div>
<?php
	}

	private function login_form_buttons(){
		$types = $this->options['types'];
		foreach($types as $type){
			$login_url = home_url('index.php/wp-json/clogin-passport/login/'.$type);
			echo '<a class="loginbtn" href="'.$login_url.'" title="'.__($this->modules_name[$type], $this->text_domain).__('Login', $this->text_domain).'"><img src="'.CLOGIN_PASSPORT_URL.'icon/'.$type.'.png"/></a>';
		}
	}

	//add link to plugin action links
	public function plugin_action_links( $links, $file ) {
		if ( dirname(plugin_basename( __FILE__ )) . '/clogin-passport.php' === $file ) {
			$settings_link = '<a href="' . menu_page_url( 'clogin-passport', 0 ) . '">' . __( 'Settings' ) . '</a>';
			array_unshift( $links, $settings_link ); // before other links
		}
		return $links;
	}

	public function register_settings() {
		register_setting($this->text_domain, 'clogin-passport-types');
		register_setting($this->text_domain, 'clogin-passport-configs');
		register_setting($this->text_domain, 'clogin-passport-avatar-priority');
		register_setting($this->text_domain, 'clogin-passport-automatic-register');
	}

	public function load_language() {
		load_plugin_textdomain( $this->text_domain, false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		/*__('QQ', $this->text_domain);
		__('Wechat', $this->text_domain);
		__('Alipay', $this->text_domain);
		__('Weibo', $this->text_domain);
		__('Baidu', $this->text_domain);
		__('Huawei', $this->text_domain);
		__('Google', $this->text_domain);
		__('Microsoft', $this->text_domain);
		__('Facebook', $this->text_domain);
		__('Twitter', $this->text_domain);
		__('Dingtalk', $this->text_domain);*/
	}

	public function admin_menu() {
		$page_title = __('Clogin Passport', $this->text_domain);
		$menu_title = __('Clogin Passport', $this->text_domain);
		$capability = 'administrator';
		$menu_slug = $this->text_domain;
		$function = array($this, 'admin_page');
		$icon_url = 'none';
		$settings_page = add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url);
	}

	public function admin_enqueue_scripts() {
		wp_enqueue_style( $this->text_domain, CLOGIN_PASSPORT_URL . 'css/style.css' );
	}

	private function is_activated($type) {
		return is_array($this->options['types']) && in_array($type, $this->options['types']);
	}

	public function admin_page() {
?>
<div class="wrap" id="clogin-passport-container">
	<h2><?php _e('Clogin Passport', $this->text_domain); ?></h2>
	<p><?php _e("Clogin Passport for Wordpress, Many Oauth 2.0 log in methods.", $this->text_domain); ?></p>
	
	<div class="tab-content">
		<form action="options.php" method="post" id="update-form">
			<?php settings_fields($this->text_domain); ?>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="clogin-passport-configs-appurl"><?php _e('API URL', $this->text_domain); ?></label></th>
						<td><fieldset>
							<legend class="screen-reader-text"><span><?php _e('Enabled', $this->text_domain); ?></span></legend>
							<label>
								<input name="clogin-passport-configs[appurl]" type="text" id="clogin-passport-configs-appurl" value="<?php echo $this->options['appurl']; ?>" />
								<p><?php _e('Must start with http:// or https:// and end with /', $this->text_domain); ?></p>
							</label>
						</fieldset></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="clogin-passport-configs-appid"><?php _e('APPID', $this->text_domain); ?></label></th>
						<td><fieldset>
							<legend class="screen-reader-text"><span><?php _e('Enabled', $this->text_domain); ?></span></legend>
							<label>
								<input name="clogin-passport-configs[appid]" type="text" id="clogin-passport-configs-appid" value="<?php echo $this->options['appid']; ?>" />
							</label>
						</fieldset></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="clogin-passport-configs-appkey"><?php _e('APPKEY', $this->text_domain); ?></label></th>
						<td><fieldset>
							<legend class="screen-reader-text"><span><?php _e('Enabled', $this->text_domain); ?></span></legend>
							<label>
								<input name="clogin-passport-configs[appkey]" type="text" id="clogin-passport-configs-appkey" value="<?php echo $this->options['appkey']; ?>" />
							</label>
						</fieldset></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Open Login Method', $this->text_domain); ?></label></th>
						<td><fieldset>
							<legend class="screen-reader-text"><span><?php _e('Enabled', $this->text_domain); ?></span></legend>
							<?php $this->admin_login_types();?>
						</fieldset></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="clogin-passport-avatar-priority"><?php _e('Avatar Priority', $this->text_domain); ?></label></th>
						<td><fieldset>
							<legend class="screen-reader-text"><span><?php _e('Enabled', $this->text_domain); ?></span></legend>
							<label>
								<input name="clogin-passport-avatar-priority" type="number" id="clogin-passport-avatar-priority" value="<?php echo $this->options['avatar_priority']; ?>" />
								<p><?php _e('Default is 9999, if you want Clogin Passport to fully take over the avatar display address from the other plugins, please set a larger number.', $this->text_domain); ?></p>
							</label>
						</fieldset></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="clogin-passport-automatic-register"><?php _e('Automatic Register', $this->text_domain); ?></label></th>
						<td><fieldset>
							<legend class="screen-reader-text"><span><?php _e('Enabled', $this->text_domain); ?></span></legend>
							<label>
								<input name="clogin-passport-automatic-register" type="checkbox" id="clogin-passport-automatic-register" value="1" <?php checked($this->options['automatic_register'], 1); ?>" />
								<p><?php printf(__("If hasn't binded social media account, then create new account automatically. and do not forget allowed anyone can register in <a href=\"%s\" target=\"_blank\">General Settings</a>", $this->text_domain), admin_url('options-general.php')); ?></p>
							</label>
						</fieldset></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="avatar-desc"><?php _e('Documents', $this->text_domain); ?></label></th>
						<td><fieldset>
							<legend class="screen-reader-text"><span><?php _e('Documents', $this->text_domain); ?></span></legend>
							<label>
								<dl>
									<dt><strong><?php _e('How to print avatar image?', $this->text_domain); ?></strong></dt>
									<dd>&lt;?php get_avatar($id_or_email, $size, $default, $alt, $args); ?&gt;</dd>
									<dt><strong><?php _e('How to get avatar image URL?', $this->text_domain); ?></strong></dt>
									<dd>&lt;?php get_user_meta($user->ID, 'clogin_passport_avatar', true); ?&gt;</dd>
									<dt><strong><?php _e('How to update avatar image URL?', $this->text_domain); ?></strong></dt>
									<dd>&lt;?php update_user_meta($user->ID, 'clogin_passport_avatar', $image_url); ?&gt;</dd>
									<dt><strong><?php _e('How to print third-part login list?', $this->text_domain); ?></strong></dt>
									<dd>&lt;?php do_action('clogin-passport'); ?&gt;</dd>
									<dd><pre>&lt;?php 
ob_start();
do_action('clogin-passport'); 
$codes = ob_get_contents();
ob_end_clean();
?&gt;</pre></dd>
									<dt><strong><?php _e('How to print buttons of binding social media account?', $this->text_domain); ?></strong></dt>
									<dd>&lt;?php<br />if (!function_exists('get_user_to_edit')) include(ABSPATH . '/wp-admin/includes/user.php');<br />do_action( 'binding_social_media_account', get_user_to_edit(get_current_user_id()) );<br />?&gt;</dd>
								<dl>
							</label>
						</fieldset></td>
					</tr>
				</tbody>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
</div>
<?php
	}

	private function admin_login_types(){
		foreach($this->modules_name as $type=>$typename){
			echo '<label><input name="clogin-passport-types[]" type="checkbox" id="clogin-passport-types-'.$type.'" value="'.$type.'" '.checked($this->is_activated($type),true,false).'/> '.__($typename, $this->text_domain).'</label>&nbsp;&nbsp;';
		}
	}
}
$core = new core;
$core->init();