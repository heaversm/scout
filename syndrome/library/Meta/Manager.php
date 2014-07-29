<?php
class Meta_Manager {
	private static $instance;
	private $data;
	private $page = 'default';
	
	private function __construct() {
		$this->data = Data_Meta::$data;
	}
	
	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function setPage($page) {
		$this->page = $page;
	}
	
	public function getTitle() {
		return $this->data['title'][$this->page]; 
	}
	
	public function getKeywords() {
		return $this->data['keywords'][$this->page]; 
	}
	
	public function getDescription() {
		return $this->data['description'][$this->page]; 
	}
	
}