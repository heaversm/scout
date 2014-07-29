<?php
class Hash {
	private static $instance;
	private $id;
	private $hash;
	
	private function __construct() {
	}
	
	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function setId($id) {
		$this->id = $id;
		$this->generateHash();
		return $this;
	}
	
	public function getHash() {
		return $this->hash;
	}
	
	public function verifyHash($hash) {
		return $this->hash === $hash;
	}
	
	private function generateHash() {
		$this->hash = md5($this->id.Config::HASH_SALT);
	}
}