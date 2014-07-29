<?php
/**
 * @author Man Hoang
 * @name Helper_Browser
 */
class Helper_Browser {
	/**
	 * Static array containing browser type and version
	 * @var array
	 */
	public static $browser_info = array();
	
	/**
	 * Gets the current user's browser and version
	 *
	 * @return array
	 */
	public static function getBrowser() {
		if(self::$browser_info) {
			return self::$browser_info;
		}
		$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$browser = 'other';
		$version = 0;

		$browser_types = array(
			'chrome' => '/chrome\/(\d+)/',
			'safari' => '/version\/(\d+)/',
			'opera' => '/version\/(\d+)/',
			'firefox' => '/firefox\/(\d+)/',
			'msie' => '/msie (\d+)/'
		);
		if($ua) {
			$ua = strtolower($ua);
			$browser_matches = array();
			if(preg_match('/' . implode('|', array_keys($browser_types)) . '/', $ua, $browser_matches) > 0) {
				$browser = $browser_matches[0];
				$version_matches = array();
				if(preg_match($browser_types[$browser], $ua, $version_matches) > 0) {
					$version = intval($version_matches[1]);
				}
			}
		}
		self::$browser_info = array(
			'browser' => $browser,
			'version' => $version
		);
		return self::$browser_info;
	}

    public static function isIphone() {
        if(!isset($user_agent)) {
            $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        }
        return (strpos($user_agent, 'iPhone') !== FALSE);
    }

    public static function isIpad() {
        if(!isset($user_agent)) {
            $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        }
        return (strpos($user_agent, 'iPad') !== FALSE);
    }
}