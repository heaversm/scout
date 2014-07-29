<?php
class Feature {
    const COLLECTION_NAME = 'features';
    private $data_store;
    private static $instance;

    private function __construct() {
        $this->data_store = DataStore::getInstance();
    }

    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getText($id) {
        $result = Mongo_Query::create(self::COLLECTION_NAME)
            ->columns(array(
                'title',
                'embed_code',
            ))
            ->where('feature_id', $id)
            ->findOne();
        return $result;
    }

    public function edit($id, $title, $embed_code) {
        $this->data_store
            ->setCollection(self::COLLECTION_NAME)
            ->updateDocument(array(
                'title' => $title,
                'embed_code' => $embed_code,
            ), $id);
    }
}