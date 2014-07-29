<?php
/**
 * Configuration class for Syndrome
 * @author Man Hoang
 */
class SyndromeConfig {
	const SYNDROME_VERSION = '0.10';
	const SYNDROME_NAMESPACE = 'syndrome';

	const WEB_ROOT = '';

	const PRODUCTION_SUPER_ROOT = '/Users/mikeheavers/Sites/freelance/scout/site/'; //MH
	const PRODUCTION_HOST_NAME = 'scout.local:8887/'; //MH
	const PRODUCTION_APP_ROOT = 'scout.local:8887/'; //MH

	public static $platform;
	public static $uri;
	public static $base;
	public static $app_base;
	public static $syndrome_base;
	public static $syndrome_library;

	public static $syndrome_controller;
	public static $syndrome_base_controller;
	public static $syndrome_views;
	public static $syndrome_vendors;
	public static $host_name;

	public static $developer_uids = array();

	public static $office_ips = array(
		'75.103.2.2',
		'192.168.0.10', //MH
		'192.168.15.45'
	);

	public static $tracking_code = array();

	private static $is_dev = true;

	const DEVICE_FACEBOOK = 0;
	const DEVICE_WEB = 1;
	const DEVICE_IPHONE = 2;

	private static $device_map = array(
		self::DEVICE_FACEBOOK => 'facebook',
		self::DEVICE_WEB => 'web',
		self::DEVICE_IPHONE => 'iphone',
	);

	public static $platforms = array(
		'FACEBOOK' => self::DEVICE_FACEBOOK,
		'WEB' => self::DEVICE_WEB,
		'IPHONE' => self::DEVICE_IPHONE,
	);

	const IS_DEV = true;

	public function __construct() {
		// Syndrome Config
		self::$is_dev = isset($_SERVER['DEV']);

		self::$platform = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'fb.') !== false ? self::DEVICE_FACEBOOK : self::DEVICE_WEB ;
		self::$uri = isset($_SERVER['REQUEST_URI']) ? str_replace(self::WEB_ROOT, '', $_SERVER['REQUEST_URI']) : '' ;

		self::$base = self::PRODUCTION_SUPER_ROOT.self::PRODUCTION_APP_ROOT.'/';
		self::$host_name = self::PRODUCTION_HOST_NAME ;
		self::$syndrome_base = self::$base.self::SYNDROME_NAMESPACE;
		self::$syndrome_library = self::$syndrome_base.'/library/';
		self::$syndrome_controller = self::$syndrome_library.'Controller';
		self::$syndrome_base_controller = self::$syndrome_library.'BaseController';
		self::$syndrome_views = self::$syndrome_base.'/views/';
		self::$syndrome_vendors = self::$syndrome_base.'/vendors/';

	}

	public static function forceDev() {
		self::$is_dev = true;
	}

	public static function isDev() {
		return self::$is_dev;
	}

	public static function getDeviceName() {
		return self::$device_map[Config::$platform];
	}

	public static function getPlatform() {
		return isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'fb.') !== false ? self::DEVICE_FACEBOOK : self::DEVICE_WEB ;
	}
}
