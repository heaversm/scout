<?php
class SynMemcache extends SynMemcache_Config {
	private static $memcache;
	private static $instance;
	
	private function __construct() {
		self::$memcache = new Memcache();
		$server = self::$servers[SyndromeConfig::isDev() ? 'development' : 'production'];
		@self::$memcache->addServer($server, 11211, false);
	}
	
	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function get($key = '') {
		if($key != '') {
			return self::$memcache->get($key, 0);
		} 
		return false;
	}
	
	public function set($key, $value, $expire = 0) {
		if($key != '') {
			return self::$memcache->set($key, $value, false, $expire);
		} 
		return false;
	}
	
	public function delete($key) {
		if($key != '') {
			return self::$memcache->delete($key);
		} 
		return false;
	}
}