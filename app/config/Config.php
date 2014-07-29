<?php
/**
 * Configuration class for app
 * @author Man Hoang
 * @name Config
 */
class Config extends SyndromeConfig {
	const APP_VERSION = 0.001;
	public static $tracking_code = array(
		'google' => '',
	);

	const WEB_NAME = 'scout.local';
	const NAME = 'Scout Studios';

	const DEFAULT_CONTROLLER = 'Reel';

	const ERROR_PAGE_CONTROLLER = 'Reel';

	const DEVELOPER = '';

	const ALWAYS_REQUIRE_AUTHORIZATION = false;
	const NO_AUTHORIZATION_CONTROLLER = 'Splash';

	public static $js_group = array(
		'//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js',
		'//ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js',
		'//connect.facebook.net/en_US/all.js',
		'jquery.cycle.js',
		'jquery.markitup.js',
        		'jquery.fitvids.js',
        		'jquery.fancybox.js',
		'functions.js',
	);

	public static $no_authorization_uris = array(
	);

	public static $authorized_users = array(
		'mhoang@ravalandknight.com',
		'whitney@scoutstudios.tv',
		'mheavers@gmail.com'
	);

	const FAVICON_FILE = '';

	const USE_CDN = false;
	public static $cdn_host = 'http://';

	private static $app_data = array();

	public static $friends = array(
	);

	public static $library;
	public static $controller;
	public static $base_controller;
	public static $views;
	public static $vendors;
	public static $config;
	public static $subdomain;
	public static $protocol;
	public static $url;
	public static $standalone_url;
	public static $secure_url;
	public static $cookie_domain;
	public static $static_host;
	public static $js_url;
	public static $images_url;
	public static $assets_url;
	public static $assets_path;
	public static $css_url;
	public static $favicon;

	public static $third_party = array(
		'amazon' => array(
			'access_key' => '',
			'secret_key' => '',
			'distribution_id' => '',
		),
		'facebook' => array(
			'app_id' => '231004687066158',
			'app_secret' => '7905a14a19be0c6f39faa5382541eed3',
			'app_access_token' => '456342141134634|bh_uxhaJPjWtez6DH7lUfrPnBlA',
		),
	);

	public static function getAppData() {
		return self::$app_data;
	}

	public static $developer_uids = array();

	public function __construct() {
		parent::__construct();

		// App Config
		self::$library = self::$base.'app/library/';
		self::$controller = self::$library.'Controller';
		self::$base_controller = self::$library.'BaseController';
		self::$vendors = self::$base.'app/vendors/';
		self::$views = self::$base.'app/views/';
		self::$config = self::$base.'app/config/';
		self::$subdomain = (self::$platform == self::DEVICE_FACEBOOK) ? 'fb.' : 'www.' ;
		self::$protocol = 'http';
		self::$url = self::$protocol.'://'.self::$subdomain.self::$host_name.self::WEB_ROOT;
		self::$standalone_url = self::$protocol.'://www.'.self::$host_name.self::WEB_ROOT;
		self::$secure_url = self::$protocol.'://'.self::$subdomain.self::$host_name.self::WEB_ROOT;
		self::$cookie_domain = SyndromeConfig::isDev() ? '.'.self::DEVELOPMENT_HOST_NAME : '.'.self::PRODUCTION_HOST_NAME ;
		self::$static_host = (!SyndromeConfig::isDev() && self::USE_CDN) ? self::$cdn_host : self::$url ;
		self::$js_url = self::$static_host.'/static/'.self::APP_VERSION.'/js/';
		self::$css_url = self::$static_host.'/static/'.self::APP_VERSION.'/css/';
		self::$images_url = self::$static_host.'/static/'.self::APP_VERSION.'/images/';
		self::$assets_url = self::$static_host.'/static/'.self::APP_VERSION.'/assets/';
		self::$assets_path = self::$base.'/static/assets/';
		self::$favicon = self::$images_url.self::FAVICON_FILE;

		self::$app_data = array(
			'App' => array(
				'url' => self::$url,
				'static_url' => self::$static_host,
				'static_images_url' => self::$images_url,
				'static_js_url' => self::$js_url,
				'static_css_url' => self::$css_url,
				'app_id' => self::$third_party['facebook']['app_id'],
			)
		);
	}
}
