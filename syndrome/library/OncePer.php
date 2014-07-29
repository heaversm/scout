<?php
class OncePer {
	
	public static function set($key, $value, $expire) {
		return SynMemcache::getInstance()->set($key, $value, $expire);
	}
	
	public static function exists($key) {
		return SynMemcache::getInstance()->get($key);
	}
	
	public static function delete($key) {
		return SynMemcache::getInstance()->delete($key);
	}
}