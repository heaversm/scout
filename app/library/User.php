<?php
class User {
	private $user_info = array();
	private $new_user_info = array();
	private $schema;
	private $uid;
	private $collection;
	
	private $data_store;
	
	private $preloaded = false;
	
	private $fb_collected_schema = array(
		'full_name',
		'username',
		'email'
	);
	
	const COLLECTION_PREPEND = 'user_profile';
	
	public function __construct($fb_uid, array $fields = array()) {
		$this->uid = $fb_uid;
		$this->collection = $this->getCollectionName();
		$this->schema = Mongo_Schema::getInstance()->getSchema($this->collection);
		$this->data_store = DataStore::getInstance();
		if(!empty($fields)) {
			$this->validatePreFields($fields);
		}
	}
	
	public function __get($field) {
		switch($field){
			case 'uid':
			case 'fb_uid':
				return $this->uid;
				break;
			default :
				if(isset($this->user_info[$field])) {
					return $this->user_info[$field];
				}
				
				$this->fetchUserInfo();
				
				if(isset($this->user_info[$field])) {
					return $this->user_info[$field];
				} else {
					return '';
				}
				
				break;
		}
	}
	
	public function __set($key, $value) {
		if(in_array($key, $this->schema)) {
			$this->new_user_info[$key] = $value;
		}
	}
	
	public function isReal() {
		return $this->uid > 0;
	}
	
	public function saveInfo() {
		if(!empty($this->new_user_info)) {
			$this->new_user_info['modified_time'] = time();
			
			$this->data_store
				->setCollection($this->collection)
				->updateDocument($this->new_user_info, $this->uid);
		}
	}
	
	public function createUserProfile() {
		$insert_data = array(
			'fb_uid' => $this->uid,
			'modified_time' => time(),
			'created_time' => time(),
			'role' => Authentication::ROLE_USER,
			'removed' => Authentication::STATE_INSTALLED,
		);

		$this->data_store
			->setCollection($this->collection)
			->createDocument($insert_data, $this->uid);
		
		return true;
	}
	
	private function fetchUserInfo() {
		$info = $this->data_store
				->setCollection($this->collection)
				->getById($this->uid);
				
		if(!$this->preloaded) {
			$this->user_info = $info;
		} else {
			foreach($info as $key => $value) {
				if(!isset($this->user_info[$key])) {
					$this->user_info[$key] = $value;
				}
			}
		}
	}
	
	private function validatePreFields($fields) {
		foreach($fields as $field => $value) {
			if(in_array($field, $this->schema)) {
				$this->user_info[$field] = $value;
			}
		}
		
		if(!empty($this->user_info)) {
			$this->preloaded = true;
		}
	}
	
	public function appendUidTo($value) {
		return $value.'_'.$this->uid;
	}
	
	public function getCollectionName() {
		return self::COLLECTION_PREPEND;
	}
	
	public function isInstalled() {
		return $this->removed == Authentication::STATE_INSTALLED;
	}
	
	public function isUninstalled() {
		return $this->removed == Authentication::STATE_UNINSTALLED;
	}
	
	public function uninstall() {
		$this->removed = Authentication::STATE_UNINSTALLED;
		$this->saveInfo();
	}
	
	public function reinstall() {
		$this->removed = Authentication::STATE_INSTALLED;
		$this->saveInfo();
	}
}