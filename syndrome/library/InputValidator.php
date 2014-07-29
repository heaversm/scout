<?php
class InputValidator {
	private $collection;
	private $fields = array();
	private $required_fields = array();
	private $valid_date = array();
	
	public function __construct($collection) {
		$this->collection = $collection;
		$this->fields = MongoSchema::$schema[$collection];
	}
	
	public function setRequiredFields($fields) {
		if(!MongoSchema::validateFields($this->collection, $fields)) {
			return false;
		}
		
		$this->required_fields = $fields;
		return true;
	}
	
	public function validate() {
		if(isset($_POST['module']) && isset($_POST['module'][$this->collection])) {
			if(!isset($_POST['session_key'][$this->collection]) || (FormModel::getSessionKey($this->collection) != $_POST['session_key'][$this->collection])) {
				Helper_Request::respond('/', Helper_Request::RESPONSE_REDIR);
			}
			
			
			
		} else {
			return false;	
		}
		
		return true;
	}
}