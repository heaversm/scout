<?php
class Portfolio_Asset {
	const COLLECTION_NAME = 'assets';
	private static $instance;
	
	const ASSET_REMOVED = 0;
	const ASSET_ACTIVE = 1;
	
	const MAX_ASSETS = 30;

	private function __construct() {
		$this->data_store = DataStore::getInstance();
	}
	
	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function getAssets($project_id, $asset_type) {
		return $this->getFromMongo($project_id, $asset_type);
	}
	
	private function getAssetCount($project_id, $asset_type) {
		$results = Mongo_Query::create(self::COLLECTION_NAME)
			->columns(array('asset_id', 'project_id', 'large_fileid'))
			->where('project_id', $project_id)
            ->where('asset_type', $asset_type)
			->orderBy('order', 'ASC')
			->find();
		$assets = array();
		$i = 0;
		foreach($results as $result) {
			$assets[$i] = array(
				'project_id' => $result['project_id'],
				'asset_image' => Config::$assets_url.$result['large_fileid'],
				'asset_id' => $result['asset_id']
			);
			$i++;
		}
		return $assets;
	}
	
	public function calculateAssetsFields($project_id = 0, $asset_type = 'assets') {
		$fields = array(
			'current' => array(),
			'new' => array()
		);
		$count = 1;
		if($project_id > 0) {
			$assets = $this->getAssetCount($project_id, $asset_type);
			$count = count($assets) + 1;
			$fields['current'] = $assets;
		}
		for($i = $count; $i <= self::MAX_ASSETS; $i++) {
			$fields['new'][] = array('n' => $i, 'asset_type' => $asset_type);
		}
		return $fields;
	}
	
	public function processAssets($project_id, $assets, $asset_type) {
		foreach($assets['tmp_name'] as $index => $tmp_file) {
			$asset = array(
				'name' => $assets['name'][$index],
				'type' => $assets['type'][$index],
				'tmp_name' => $assets['tmp_name'][$index],
				'size' => $assets['size'][$index],
			);
			$file_name = Asset::move_file($asset, Config::$assets_path);
			if(is_string($file_name)) {
				$asset_id = $this->getNextId();
				$order = $this->getNextOrder();
				$this->data_store
					->setCollection(self::COLLECTION_NAME)
					->createDocument(array(
						'asset_id' => $asset_id,
						'project_id' => $project_id,
                        'asset_type' => $asset_type,
						'large_fileid' => $file_name,
						'order' => $order,
						'created_time' => time(),
						'removed' => self::ASSET_ACTIVE
					), $asset_id);
			}
		}
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
	}
	
	public function remove($id) {
		$this->data_store
			->setCollection(self::COLLECTION_NAME)
			->removeDocument($id);
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
			->columns(array('asset_id'))
			->orderBy('asset_id', 'DESC')
			->limit(1)
			->find();
		$first = current($result);
		return !empty($first) ? $first['asset_id'] + 1 : 1;
	}
	
	private function getFromMongo($project_id, $asset_type) {
		$projects = array();
		$results = Mongo_Query::create(self::COLLECTION_NAME)
			->columns(array('large_fileid'))
			->where('project_id', $project_id)
            ->where('asset_type', $asset_type)
			->orderBy('order', 'ASC')
			->find();
		foreach($results as $row) {
			$projects[] = array(
				'asset_image' => Config::$assets_url.$row['large_fileid'],
				'asset_type' => $asset_type
			);
		}
		return $projects;
	}
}