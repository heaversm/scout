<?php
/**
 * Schema Manager for Mongo
 * @author Man Hoang
 */
class Mongo_Schema {
	/**
	 * Internal instance
	 * @var Mongo_Schema
	 */
	private static $instance;

	/**
	 * Memoized array for collections
	 * @var array
	 */
	private $memoized_collections = array();
	
	const ASCENDING = 1;
	const DESCENDING = -1;
	
	public static $memcached_collections = array(
        'homepages',
        'features',
		'users',
		'categories',
		'projects',
		'assets',
		'user_profile'
	);

	/**
	 * Array of collectrions
	 * @var array
	 */
	private static $collections = array(
        'homepages' => array(
            'schema' => array(
                'homepage_id',
                'large_fileid',
                'created_time',
                'removed',
            ),
            'indexes' => array(
                array('order' => self::ASCENDING),
            ),
            'primary_key' => 'homepage_id',
            'pool' => 'localhost',
        ),
        'features' => array(
            'schema' => array(
                'feature_id',
                'title',
                'embed_code',
            ),
            'indexes' => array(
            ),
            'primary_key' => 'feature_id',
            'pool' => 'localhost',
        ),
        'video_embeds' => array(
            'schema' => array(
                'embed_id',
                'project_id',
                'embed_code',
                'order',
                'created_time',
                'removed',
            ),
            'indexes' => array(
                array('order' => self::ASCENDING),
                array('project_id' => self::ASCENDING),
            ),
            'primary_key' => 'embed_id',
            'pool' => 'localhost',
        ),
        'credits' => array(
            'schema' => array(
                'credit_id',
                'project_id',
                'position',
                'name',
                'order',
                'created_time',
                'removed',
            ),
            'indexes' => array(
                array('order' => self::ASCENDING),
                array('project_id' => self::ASCENDING),
            ),
            'primary_key' => 'credit_id',
            'pool' => 'localhost',
        ),
		'assets' => array(
			'schema' => array(
				'asset_id',
				'project_id',
				'large_fileid',
                'asset_type',
				'order',
				'created_time',
				'removed',
			),
			'indexes' => array(
				array('order' => self::ASCENDING),
				array('project_id' => self::ASCENDING),
			),
			'primary_key' => 'asset_id',
			'pool' => 'localhost',
		),
		'projects' => array(
			'schema' => array(
				'project_id',
				'name',
                'client',
				'category_id',
                'hero_fileid',
                'vimeo_url',
                'video_embed1',
                'video_embed2',
                'video_embed3',
                'video_embed4',
                'video_embed5',
				'cover_fileid',
				'description',
				'order',
				'created_time',
				'removed',
			),
			'indexes' => array(
				array('order' => self::ASCENDING),
				array('category_id' => self::ASCENDING),
				array('name' => self::ASCENDING),
			),
			'primary_key' => 'project_id',
			'pool' => 'localhost',
		),
		'categories' => array(
			'schema' => array(
				'id',
				'name',
				'order',
				'created_time',
				'removed',
			),
			'indexes' => array(
				array('id' => self::ASCENDING),
				array('name' => self::ASCENDING),
			),
			'primary_key' => 'id',
			'pool' => 'localhost',
		),
		'users' => array(
			'schema' => array(
				'fb_uid',
				'state',
				'created_time'
			),
			'indexes' => array(
				array('fb_uid' => self::DESCENDING),
				array('username' => self::ASCENDING),
			),
			'primary_key' => 'fb_uid',
			'pool' => 'localhost',
		),
		'user_profile' => array(
			'schema' => array(
				'fb_uid',
				'role',
				'removed',
				'modified_time',
				'created_time',
			),
			'indexes' => array(
				array('fb_uid' => self::DESCENDING),
			),
			'primary_key' => 'fb_uid',
			'pool' => 'localhost',
		),
	);

	/**
	 * Empty contsructor!!
	 */
	protected function __construct() {
	}

	/**
	 * For singletons
	 * @return Mongo_Schema
	 */
	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Checks if a collection exists
	 * @param string $collection_name
	 * @return bool
	 */
	public function collectionExists($collection_name) {
		return $this->getCollection($collection_name) !== false;
	}
	
	public static function isMemcachedCollection($collection_name) {
		if(!$collection_name) {
			return false;
		}
		if(in_array($collection_name, self::$memcached_collections)) {
			return true;
		}
		foreach(self::$memcached_collections as $name) {
			if(strpos($name, '*') !== false && preg_match('/^' . str_replace('*', '[A-Za-z0-9]', $name) . '$/', $collection_name)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets the collection definition by straight name or wildcarded names
	 * @param string $collection_name
	 * @return array|bool if collection is found
	 */
	protected function getCollection($collection_name) {
		if(!$collection_name) {
			return false;
		}

		if(isset($this->memoized_collections[$collection_name])) {
			return $this->memoized_collections[$collection_name];
		}
		if(isset(self::$collections[$collection_name])) {
			$this->memoized_collections[$collection_name] = self::$collections[$collection_name];
			return self::$collections[$collection_name];
		}
		foreach(self::$collections as $name => $schema) {
			if(strpos($name, '*') !== false && preg_match('/^' . str_replace('*', '[A-Za-z0-9]', $name) . '$/', $collection_name)) {
				$this->memoized_collections[$collection_name] = $schema;
				return $schema;
			}
		}

		return false;
	}

	/**
	 * Get Pool of Collection
	 * @param string $collection_name
	 * @return string|bool
	 */
	public function getPool($collection_name) {
		return ($this->collectionExists($collection_name)) ? $this->memoized_collections[$collection_name]['pool'] : false;
	}

	/**
	 * Get Schema of Collection
	 * @param string $collection_name
	 * @return array|bool
	 */
	public function getSchema($collection_name) {
		return ($this->collectionExists($collection_name)) ? $this->memoized_collections[$collection_name]['schema'] : false;
	}

	/**
	 * Get Indexes of Collection
	 * @param string $collection_name
	 * @return array|bool
	 */
	public function getIndexes($collection_name) {
		return ($this->collectionExists($collection_name)) ? $this->memoized_collections[$collection_name]['indexes'] : false;
	}

	/**
	 * Get Primary Key of Collection
	 * @param string $collection_name
	 * @return array|bool
	 */
	public function getPrimaryKey($collection_name) {
		return ($this->collectionExists($collection_name)) ? $this->memoized_collections[$collection_name]['primary_key'] : false;
	}

	/**
	 * Validates Schema Keys of a Collection, returns invalid, unaccounted for keys else true
	 * @param string $collection_name
	 * @return array|bool
	 */
	public function validateSchema($collection_name, $keys) {
		$invalid_keys = array();
		foreach($keys as $key) {
			if(!in_array($key, $this->getSchema($collection_name))) {
				$invalid_keys[] = $key;
			}
		}

		return !empty($invalid_keys) ? $invalid_keys : true;
	}
}