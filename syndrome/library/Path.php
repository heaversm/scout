<?php
class Path {
	private $memory;
	private static $instance;
	private $path_keys = array();
	private $path_values = array();
	
	private function __construct() {
		$this->memory = Memory::getInstance();
	}
	
	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function setPath(array $path = array()) {
		$this->path_keys = $path;
		return $this;
	}
	
	public function getPathValues() {
		$path = Helper_Request::getRequest('path', '', 'STR');
		$path_parts = explode('/', $path);
		
		array_shift($path_parts);
		
		if(!empty($this->path_keys)) {
			foreach($this->path_keys as $index => $key) {
				if(isset($path_parts[$index])) {
					$this->memory->$key = $path_parts[$index];
				}
			}
		}
	}
}