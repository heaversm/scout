<?php

class Homepage {
    const COLLECTION_NAME = 'homepages';
    private static $instance;

    const HOMEPAGE_INACTIVE = 0;
    const HOMEPAGE_ACTIVE = 1;

    private function __construct() {
        $this->data_store = DataStore::getInstance();
    }

    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getHomepage() { //MH - random image selected here
        $projects = $this->getFromMongo();
        $rand_keys = array_rand($projects);
        return $projects[$rand_keys];
    }

    public function getHomepages() {
        return $this->getFromMongo();
    }

    public function create($large_fileid) {
        $id = $this->getNextId();
        $file_name = Asset::move_file($large_fileid, Config::$assets_path);
        $this->data_store
            ->setCollection(self::COLLECTION_NAME)
            ->createDocument(array(
                'homepage_id' => $id,
                'large_fileid' => $file_name,
                'created_time' => time(),
                'removed' => self::HOMEPAGE_ACTIVE
            ), $id);
    }

    public function remove($id) {
        $this->data_store
            ->setCollection(self::COLLECTION_NAME)
            ->removeDocument($id);
    }

    private function getNextId() {
        $result = Mongo_Query::create(self::COLLECTION_NAME)
            ->columns(array('homepage_id'))
            ->orderBy('homepage_id', 'DESC')
            ->limit(1)
            ->find();
        $first = current($result);
        return !empty($first) ? $first['homepage_id'] + 1 : 1;
    }

    private function getFromMongo() {
        $homepages = array();
        $results = Mongo_Query::create(self::COLLECTION_NAME)
            ->columns(array('large_fileid', 'homepage_id'))
            ->find();
        foreach($results as $row) {
            $homepages[] = array(
                'asset_image' => Config::$assets_url.$row['large_fileid'],
                'homepage_id' => $row['homepage_id'],
            );
        }
        return $homepages;
    }
}
