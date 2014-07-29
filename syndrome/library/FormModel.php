<?php
class FormModel {
	private $collection;
	private $fields;
	private $values;
	private $id;
	private $form_xml = array();
	private static $instance;
	
	
	private function __construct() {
		$this->form_xml['response'] = '';
		$this->form_xml['keys'] = '';
	}
	
	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function setParams($collection, $id, $fields) {
		$this->collection = $collection;
		$this->fields = $fields;
		$this->values = DataStore::getInstance()
			->setCollection($collection)
			->getById($id);
		$this->id = $id;
		return $this;
	}
	
	public function __set($name, $value) {
		$this->form_xml[$name] = $value;
	}
	
	public function __get($name) {
		if(isset($this->form_xml[$name])) {
			return $this->form_xml[$name];
		}
		
		return false;
	}
	
	public function getFormXml() {
		return $this->form_xml;
	}
	
	public function setResponse(array $response) {
		$this->form_xml['response'] = Helper_FormModel::writeResponse($response);
		
		return $this;
	}
	
	public function createForm() {
		foreach($this->fields as $field) {
			$field_type = FormSchema::getSchemaType($this->collection, $field);
			$value = isset($this->values[$field]) ? $this->values[$field] : '' ;
			if(isset($_POST[$field])) {
				$value = $_POST[$field];
			}
			switch($field_type) {
				case 'select_fields' :
					$this->$field = Helper_FormModel::writeSelect($this->collection, $field, $value);
					break;
				case 'textarea_fields' :
					$this->$field = Helper_FormModel::writeTextarea($this->collection, $field, $value);
					break;
				case 'file_fields' :
					$this->$field = Helper_FormModel::writeFileInput($this->collection, $field, $value);
					break;
				case 'input_fields' :
				default;
					$this->$field = Helper_FormModel::writeInput($this->collection, $field, $value);
					break;
			}
		}
		
		if($this->id) {
			$this->form_xml['keys'] = Helper_FormModel::writeHiddenInput($this->collection, 'id', $this->id);
		}
		
		return $this;
	}
}