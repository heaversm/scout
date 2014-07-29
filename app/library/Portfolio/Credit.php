<?php
class Portfolio_Credit {
    const COLLECTION_NAME = 'credits';
    private static $instance;

    const CREDIT_REMOVED = 0;
    const CREDIT_ACTIVE = 1;

    const MAX_CREDITS = 15;

    private function __construct() {
        $this->data_store = DataStore::getInstance();
    }

    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getCredits($project_id) {
        return $this->getFromMongo($project_id);
    }

    private function getCreditCount($project_id) {
        $results = Mongo_Query::create(self::COLLECTION_NAME)
            ->columns(array('credit_id', 'position', 'name', 'project_id'))
            ->where('project_id', $project_id)
            ->orderBy('order', 'ASC')
            ->find();
        $assets = array();
        $i = 0;
        foreach($results as $result) {
            $assets[$i] = array(
                'credit_id' => $result['credit_id'],
                'project_id' => $result['project_id'],
                'position' => $result['position'],
                'name' => $result['name']
            );
            $i++;
        }
        return $assets;
    }

    public function calculateCreditsFields($project_id = 0) {
        $fields = array(
            'current' => array(),
            'new' => array()
        );
        $count = 1;
        if($project_id > 0) {
            $assets = $this->getCreditCount($project_id);
            $count = count($assets) + 1;
            $fields['current'] = $assets;
        }
        for($i = $count; $i <= self::MAX_CREDITS; $i++) {
            $fields['new'][] = array('n' => $i);
        }
        return $fields;
    }

    public function processCredits($project_id, $credits) {
        foreach($credits as $index => $item) {
            $credit = array(
                'name' => isset($credits[$index]['person']) ? $credits[$index]['person'] : '',
                'position' => isset($credits[$index]['position']) ? $credits[$index]['position'] : '',
            );
            if (!$credit['name'] || !$credit['position']) {
                continue;
            }

            $credit_id = $this->getNextId();
            $order = $this->getNextOrder();
            $this->data_store
                ->setCollection(self::COLLECTION_NAME)
                ->createDocument(array(
                    'credit_id' => $credit_id,
                    'project_id' => $project_id,
                    'position' => $credit['position'],
                    'name' => $credit['name'],
                    'order' => $order,
                    'created_time' => time(),
                    'removed' => self::CREDIT_ACTIVE
                ), $asset_id);
        }
    }

    public function processCurrentCredits($credits) {
        foreach($credits as $credit_id => $item) {
            $credit = array(
                'name' => isset($credits[$credit_id]['person']) ? $credits[$credit_id]['person'] : '',
            );
            if (!$credit['name']) {
                continue;
            }
            $this->data_store
                ->setCollection(self::COLLECTION_NAME)
                ->updateDocument($credit, $credit_id);
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
            ->columns(array('credit_id'))
            ->orderBy('credit_id', 'DESC')
            ->limit(1)
            ->find();
        $first = current($result);
        return !empty($first) ? $first['credit_id'] + 1 : 1;
    }

    private function getFromMongo($project_id) {
        $projects = array();
        $results = Mongo_Query::create(self::COLLECTION_NAME)
            ->columns(array('position', 'name'))
            ->where('project_id', $project_id)
            ->orderBy('order', 'ASC')
            ->find();
        foreach($results as $row) {
            $projects[] = array(
                'position' => $row['position'],
                'name' => str_replace(',', '<br/>', $row['name'])
            );
        }
        return $projects;
    }
}