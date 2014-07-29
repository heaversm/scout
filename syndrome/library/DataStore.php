<?php
class DataStore {
	/**
	 * A singleton instance of IphoneViral
	 * @var DataStore
	 */
	private static $instance;
	/**
	 * name of Mongo collection
	 * @var string
	 */
	private $collection;
	/**
	 * name of memcache key
	 * @var string
	 */
	private $memcache_key;
	
	/**
	 * Memoized data
	 * @var array
	 */
	private $data = array();
	
	private function __construct() {
	}
	
	public function setCollection($collection_name) {
		if($this->collection !== $collection_name) {
			$this->data = array();
			if($this->isMemcached($collection_name)) {
				$this->memcache_key = strtoupper($collection_name.'_DOC');
			}
			$this->collection = $collection_name;
		}
		return $this;
	}

	/**
	 * Returns a singleton instance of IphoneViral
	 *
	 * @return DataStore
	 */
	public static function getInstance(){
		if(self::$instance == null){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function isMemcached($collection_name) {
		return Mongo_Schema::isMemcachedCollection($collection_name);
	}
	
	private function getMemcacheKey($id) {
		return $this->memcache_key.'_'.$id;
	}
	
	private function getIdKey() {
		return Mongo_Schema::getInstance()->getPrimaryKey($this->collection);
	}
	
	public function createDocument(array $new_document, &$id = null) {
		$mongo = Mongo_Query::create($this->collection);
		$result = $mongo
			->values($new_document)
			->insert();
		if($result > 0 && $id === null) {
			$id = $mongo->getInsertId();
		}
		
		if($id !== null && $this->isMemcached($this->collection)) {
			SynMemcache::getInstance()
				->set($this->getMemcacheKey($id), $new_document);
		}
		
		return $result;
	}
	
	public function updateDocument(array $updated_document, $id) {
		$doc = $this->getById($id);
		
		if(empty($doc)) {
			return ;
		}
		
		$doc = array_merge($doc, $updated_document);

		if($this->getIdKey() == '_id') {
			$id = new MongoId($id);
		}
		
		$result = Mongo_Query::create($this->collection)
			->where($this->getIdKey(), $id)
			->values($updated_document)
			->update();
			
		SynMemcache::getInstance()
			->set($this->getMemcacheKey($id), $doc);
	}
	
	public function removeDocument($id) {
		if($this->isMemcached($this->collection)) {
			SynMemcache::getInstance()
				->delete($this->getMemcacheKey($id));
		}
	
		if($this->getIdKey() == '_id') {
			$id = new MongoId($id);
		}
		
		Mongo_Query::create($this->collection)
				->where($this->getIdKey(), $id)
				->remove();
	}
	
	public function getById($id) {
		if(isset($this->data[$id])) {
			return $this->data[$id];
		}
		
		$this->data[$id] = array();
		if($this->isMemcached($this->collection)) {
			$memcache_row = SynMemcache::getInstance()
				->get($this->getMemcacheKey($id));
			$this->data[$id] = $memcache_row;

			if($memcache_row === false || (is_array($memcache_row) && empty($memcache_row))) {
				$where_id = ($this->getIdKey() == '_id') ? new MongoId($id) : $id ;

				$row = Mongo_Query::create($this->collection)
					->where($this->getIdKey(), $where_id)
					->findOne();
					
				SynMemcache::getInstance()
					->set($this->getMemcacheKey($id), $row);
				$this->data[$id] = $row;
			}
		} else {
			$where_id = ($this->getIdKey() == '_id') ? new MongoId($id) : $id ;
			$row = Mongo_Query::create($this->collection)
				->where($this->getIdKey(), $where_id)
				->findOne();
			$this->data[$id] = $row;
		}
		
		 return $this->data[$id];
	}
}