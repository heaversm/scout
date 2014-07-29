<?php
class Portfolio_Project {
	const COLLECTION_NAME = 'projects';
	const ASSET_COLLECTION_NAME = 'assets';
	private static $instance;
	
	const PROJECT_REMOVED = 0;
	const PROJECT_ACTIVE = 1;

	private function __construct() {
		$this->data_store = DataStore::getInstance();
	}
	
	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function getProjectInfo($project_id) {
		$row = Mongo_Query::create(self::COLLECTION_NAME)
			->columns(array('name', 'client_name', 'description', 'hero_fileid', 'vimeo_url', 'video_embed1', 'video_embed2', 'video_embed3', 'video_embed4', 'video_embed5', 'video_embed6', 'video_embed7', 'video_embed8', 'video_embed9', 'video_embed10', 'order', 'category_id', 'second_category_id'))
			->where('project_id', $project_id)
			->findOne();
		$project_navigation = $this->getProjectNavigation($row['category_id'], $row['order']);
		return array(
            'category_id' => $row['category_id'],
			'name' => $row['name'],
            'client_name' => $row['client_name'],
			'description' => $row['description'],
            'vimeo_url' => isset($row['vimeo_url']) ? $row['vimeo_url'] : '',
            'description' => $row['description'],
            'hero' => isset($row['hero_fileid']) ? $row['hero_fileid'] : '',
            'video_embed1' => $row['video_embed1'],
            'video_embed2' => $row['video_embed2'],
            'video_embed3' => $row['video_embed3'],
            'video_embed4' => $row['video_embed4'],
            'video_embed5' => $row['video_embed5'],
            'video_embed6' => isset($row['video_embed6']) ? $row['video_embed6'] : '',
            'video_embed7' => isset($row['video_embed7']) ? $row['video_embed7'] : '',
            'video_embed8' => isset($row['video_embed8']) ? $row['video_embed8'] : '',
            'video_embed9' => isset($row['video_embed9']) ? $row['video_embed9'] : '',
            'video_embed10' => isset($row['video_embed10']) ? $row['video_embed10'] : '',
			'next_project' => $project_navigation['next'],
			'previous_project' => $project_navigation['previous']
		);
	}
	
	private function getProjectNavigation($category_id, $order) {
		$navigation = array();
		$next_project = Mongo_Query::create(self::COLLECTION_NAME)
			->columns(array('project_id', 'name'))
            ->where('category_id', $category_id)
			->where('order', $order, '>')
			->orderBy('order', 'ASC')
			->limit(1)
			->find();
		if(!empty($next_project)) {
			$next = current($next_project);
			$navigation['next'] = Config::$url.'/portfolio/'.Helper_Clean::cleanUrl($next['name']).'/'.$next['project_id'];
		} else {
			$first_project = Mongo_Query::create(self::COLLECTION_NAME)
				->columns(array('project_id', 'name'))
                ->where('category_id', $category_id)
				->orderBy('order', 'ASC')
				->limit(1)
				->find();
			$next = current($first_project);
			$navigation['next'] = Config::$url.'/portfolio/'.Helper_Clean::cleanUrl($next['name']).'/'.$next['project_id'];
		}
	
		$previous_project = Mongo_Query::create(self::COLLECTION_NAME)
			->columns(array('project_id', 'name'))
            ->where('category_id', $category_id)
			->where('order', $order, '<')
			->orderBy('order', 'DESC')
			->limit(1)
			->find();
		if(!empty($previous_project)) {
			$previous = current($previous_project);
			$navigation['previous'] = Config::$url.'/portfolio/'.Helper_Clean::cleanUrl($previous['name']).'/'.$previous['project_id'];
		} else {
			$last_project = Mongo_Query::create(self::COLLECTION_NAME)
				->columns(array('project_id', 'name'))
                ->where('category_id', $category_id)
				->orderBy('order', 'DESC')
				->limit(1)
				->find();
			$previous = current($last_project);
			$navigation['previous'] = Config::$url.'/portfolio/'.Helper_Clean::cleanUrl($previous['name']).'/'.$previous['project_id'];
		}
		
		return $navigation;
	}
	
	public function getProjects($category_id, $include_frontend_data = false) {
		$projects = $this->getFromMongo($category_id);
		ksort($projects);
		if($include_frontend_data) {
			$i = 0;
			foreach($projects as $order => $project) {
				$i++;
				$projects[$order]['slug'] = Helper_Clean::cleanUrl($project['name']);
			}
		}
		return $projects;
	}
	
	public function create($name, $client_name, $category_id, $cover_fileid, $hero_fileid, $credits, $stills, $process, $video_embed1, $video_embed2, $video_embed3, $video_embed4, $video_embed5, $video_embed6, $video_embed7, $video_embed8, $video_embed9, $video_embed10, $description, $vimeo_url) {
		$id = $this->getNextId();
		$order = $this->getNextOrder();
		$file_name = Asset::move_file($cover_fileid, Config::$assets_path);
        $hero_file = Asset::move_file($hero_fileid, Config::$assets_path);
        Portfolio_Credit::getInstance()->processCredits($id, $credits);
        Portfolio_Asset::getInstance()->processAssets($id, $stills, 'still');
        Portfolio_Asset::getInstance()->processAssets($id, $process, 'process');
		$this->data_store
			->setCollection(self::COLLECTION_NAME)
			->createDocument(array(
				'project_id' => $id,
				'name' => $name,
				'category_id' => $category_id,
				'client_name' => $client_name,
				'cover_fileid' => $file_name,
                'hero_fileid' => $hero_file,
                'vimeo_url' => $vimeo_url,
                'video_embed1' => $video_embed1,
                'video_embed2' => $video_embed2,
                'video_embed3' => $video_embed3,
                'video_embed4' => $video_embed4,
                'video_embed5' => $video_embed5,
                'video_embed6' => $video_embed6,
                'video_embed7' => $video_embed7,
                'video_embed8' => $video_embed8,
                'video_embed9' => $video_embed9,
                'video_embed10' => $video_embed10,
				'description' => $description,
				'order' => $order,
				'created_time' => time(),
				'removed' => self::PROJECT_ACTIVE
			), $id);
	}
	
	public function edit($id, $name, $client_name, $category_id, $cover_fileid, $hero_fileid, $credits, $current_credits, $stills, $process, $video_embed1, $video_embed2, $video_embed3, $video_embed4, $video_embed5, $video_embed6, $video_embed7, $video_embed8, $video_embed9, $video_embed10, $description, $vimeo_url) {
		$values = array(
			'name' => $name,
			'client_name' => $client_name,
			'category_id' => $category_id,
            'vimeo_url' => $vimeo_url,
            'video_embed1' => $video_embed1,
            'video_embed2' => $video_embed2,
            'video_embed3' => $video_embed3,
            'video_embed4' => $video_embed4,
            'video_embed5' => $video_embed5,
            'video_embed6' => $video_embed6,
            'video_embed7' => $video_embed7,
            'video_embed8' => $video_embed8,
            'video_embed9' => $video_embed9,
            'video_embed10' => $video_embed10,
			'description' => $description,
		);
		$file_name = Asset::move_file($cover_fileid, Config::$assets_path);
		if(is_string($file_name)) {
			$values['cover_fileid'] = $file_name;
		}
        $hero_file = Asset::move_file($hero_fileid, Config::$assets_path);
        if(is_string($hero_file)) {
            $values['hero_fileid'] = $hero_file;
        }
        Portfolio_Credit::getInstance()->processCredits($id, $credits);
        Portfolio_Credit::getInstance()->processCurrentCredits($current_credits);
        Portfolio_Asset::getInstance()->processAssets($id, $stills, 'still');
        Portfolio_Asset::getInstance()->processAssets($id, $process, 'process');
		$this->data_store
			->setCollection(self::COLLECTION_NAME)
			->updateDocument($values, $id);
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

    public function removeHero($id) {
        $this->data_store
            ->setCollection(self::COLLECTION_NAME)
            ->updateDocument(array('hero_fileid' => ''), $id);
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
			->columns(array('project_id'))
			->orderBy('project_id', 'DESC')
			->limit(1)
			->find();
		$first = current($result);
		return !empty($first) ? $first['project_id'] + 1 : 1;
	}
	
	private function getFromMongo($category_id) {
		$projects = array();
		$results = Mongo_Query::create(self::COLLECTION_NAME)
			->columns(array('project_id', 'client_name', 'cover_fileid', 'name', 'category_id', 'second_category_id'))
            ->where('category_id', $category_id)
			->orderBy('order', 'ASC')
			->find();
		foreach($results as $row) {
			$values = array(
				'project_id' => $row['project_id'],
				'client_name' => $row['client_name'],
				'name' => $row['name'],
				'cover' => Config::$assets_url.$row['cover_fileid'],
                'edit_path' => $row['category_id'] == 2 ? 'portfolio/edit-almost' : 'portfolio/edit'
			);
			$projects[] = $values;
		}
		return $projects;
	}
}