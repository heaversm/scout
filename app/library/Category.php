<?php
class Category {
	const COLLECTION_NAME = 'categories';
	private $data_store;
	private $categories = array();
	private static $instance;
	private $category_names = array();
	const MEMCACHE_KEY = 'categories';
	
	const CATEGORY_REMOVED = 0;
	const CATEGORY_ACTIVE = 1;

	private function __construct() {
		$this->data_store = DataStore::getInstance();
	}
	
	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function getCategories($include_slug = false) {
		$categories = $this->getFromMongo();
		ksort($categories);
		if($include_slug) {
			foreach($categories as $order => $category) {
				$categories[$order]['slug'] = Helper_Format::slug($category['name']);
				$categories[$order]['on'] = '';
			}
		}
		$this->categories = $categories;
		return $categories;
	}
	
	public function getName($id) {
		if(empty($this->category_names)) {
			$this->getCategories();
			foreach($this->categories as $order => $category) {
				$this->category_names[$category['id']] = $category['name'];
			}
		}
		return isset($this->category_names[$id]) ? $this->category_names[$id] : '';
	}
	
	public static function getCategoriesFormData() {
		$form_categories = array();
		$categories = self::getInstance()->getFromMongo();
		$form_categories[0] = '';
		foreach($categories as $category) {
			$form_categories[$category['id']] = $category['name'];
		}
		return $form_categories;
	}
	
	public function create($name) {
		$id = $this->getNextId();
		$order = $this->getNextOrder();
		$this->data_store
			->setCollection(self::COLLECTION_NAME)
			->createDocument(array(
				'id' => $id,
				'name' => $name,
				'order' => $order,
				'created_time' => time(),
				'removed' => self::CATEGORY_ACTIVE
			), $id);
		$this->addToMemcache($id, $name, $order);
	}
	
	public function edit($id, $name) {
		$this->data_store
			->setCollection(self::COLLECTION_NAME)
			->updateDocument(array(
				'name' => $name,
			), $id);
		$this->updateMemcache($id, $name);
	}
	
	public function remove($id) {
		$this->data_store
			->setCollection(self::COLLECTION_NAME)
			->removeDocument($id);
		$this->removeFromMemcache($id);
	}
	
	public function saveOrder(array $order) {
		foreach($order as $index => $id) {
			$index++;
			$this->data_store
				->setCollection(self::COLLECTION_NAME)
				->updateDocument(array(
					'order' => $index,
				), $id);
		}
		$categories = $this->getFromMongo();
		if(is_array($categories)) {
			SynMemcache::getInstance()->set(self::MEMCACHE_KEY, $categories);
		}
	}
	
	private function getFromMemcache() {
		$categories = SynMemcache::getInstance()->get(self::MEMCACHE_KEY);
		if($categories === false) {
			$categories = $this->getFromMongo();
			if(is_array($categories)) {
				SynMemcache::getInstance()->set(self::MEMCACHE_KEY, $categories);
			}
		}
		return is_array($categories) ? $categories : array();
	}
	
	private function getFromMongo() {
		$categories = array();
		$results = Mongo_Query::create(self::COLLECTION_NAME)
			->columns(array('id', 'order', 'name'))
			->orderBy('order', 'ASC')
			->find();
		foreach($results as $row) {
			$categories[$row['order']] = array('id' => $row['id'], 'name' => $row['name']);
		}
		return $categories;
	}
	
	private function addToMemcache($id, $name, $order) {
		$categories = $this->getFromMemcache();
		$categories[$order] = array('id' => $id, 'name' => $name);
		ksort($categories);
		SynMemcache::getInstance()->set(self::MEMCACHE_KEY, $categories);
	}
	
	private function updateMemcache($id, $name) {
		$categories = $this->getFromMemcache();
		foreach($categories as $order => $category) {
			if($category['id'] == $id) {
				$categories[$order]['name'] = $name;
			}
		}
		ksort($categories);
		SynMemcache::getInstance()->set(self::MEMCACHE_KEY, $categories);
	}
	
	private function removeFromMemcache($id, $name) {
		$categories = $this->getFromMemcache();
		foreach($categories as $order => $category) {
			if($category['id'] == $id) {
				unset($categories[$order]);
			}
		}
		ksort($categories);
		SynMemcache::getInstance()->set(self::MEMCACHE_KEY, $categories);
	}
	
	private function getNextOrder() {
		$result = Mongo_Query::create(self::COLLECTION_NAME)
			->columns(array('order'))
			->orderBy('order', 'DESC')
			->limit(1)
			->find();
		$first = current($result);
		return !empty($first) ? $first['order'] + 1 : 1;
	}
	
	private function getNextId() {
		$result = Mongo_Query::create(self::COLLECTION_NAME)
			->columns(array('id'))
			->orderBy('id', 'DESC')
			->limit(1)
			->find();
		$first = current($result);
		return !empty($first) ? $first['id'] + 1 : 1;
	}
}