<?php
class Memory {
	private $store = array();
	private static $instance;
	
	private function __construct() {
	}
	
	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function getStore($key = null) {
		if($key !== null && isset($this->store[$key])) {
			return $this->store[$key];
		}
		return $this->store;
	}
	
	public function __isset($key) {
		return isset($this->store[$key]);
	}
	
	public function __set($key, $value) {
		$this->store[$key] = $value;
	}
	
	public function __get($key) {
		if(isset($this->store[$key])) {
			return $this->store[$key];
		}

		return null;
	}
	
}