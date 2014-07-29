<?php
/**
 * Connection manager class that manages mongodb connections
 *
 * @author Man Hoang
 * @name Mongo_Manager
 */
class Mongo_Manager extends Mongo_Config {

	/**
	 * Internal singleton instance
	 * @var Mongo_Manager
	 */
	protected static $instance = null;

	/**
	 * Array of named connections
	 * @var array of Mongo & MongoDB objects
	 */
	protected $connections = array();


	/**
	 * The standard connection options to pass over to the mongo driver
	 * @var array
	 */
	protected static $connection_options = array(
	);

	/**
	 * Constructor function
	 *
	 * @return Mongo_Manager
	 */
	protected function __construct() {
	}

	/**
	 * Retrieve the singleton instance of this class
	 *
	 * @return Mongo_Manager
	 */
	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Closes all open mongo connections
	 */
	public static function killConnections() {
		if(self::$instance === null) {
			return;
		}
		// close all open connections
		foreach(self::$instance->connections as $name => $conn) {
			/* @var $conn Mongo */
			$conn['conn']->close();
			unset(self::$instance->connections[$name]);
		}
	}

	/**
	 * Gets a Mongo connection based on collection pools
	 *
	 * @param string $collection_pool The collection pool name
	 * @return array of Mongo & MongoDB
	 */
	protected function getConnection($collection_pool) {
		// if we currently don't have a active connection, then try to make one
		if(!isset($this->connections[$collection_pool])) {
			$server = self::$servers[SyndromeConfig::isDev() ? 'development' : 'production'];
			if(!isset($server[$collection_pool])) {
				throw new Exception('Invalid pool selected: ' . $collection_pool);
			}
			try {
				// build the connection string
				$connection_string = $this->buildConnectionString($server[$collection_pool]);
				// connect to the server
				$mongo = new Mongo($connection_string, self::$connection_options);
				// initialize the connection array
				$this->connections[$collection_pool] = array();
				// choose the database on this pool
				$this->connections[$collection_pool]['db'] = $mongo->selectDB($server[$collection_pool]['db']);
				// assign it to a local reference
				$this->connections[$collection_pool]['conn'] = $mongo;
			} catch(Exception $e) {
				// do some stuff?
				throw $e;
			}
		}

		// return the Mongo instance for the Query layer to use
		return $this->connections[$collection_pool];
	}

	/**
	 * Gets a collection from a connected database
	 *
	 * @param string $collection_name
	 * @param string $server_pool
	 * @return MongoCollection
	 */
	public function getCollection($collection_name, $server_pool = null) {
		if(!Mongo_Schema::getInstance()->collectionExists($collection_name)) {
			return false;
		}
		if($server_pool === null) {
			$server_pool = Mongo_Schema::getInstance()->getPool($collection_name);
		}
		// connect to the database
		$conn = $this->getConnection($server_pool);
		/* @var $conn MongoDB */
		return $conn['db']->__get($collection_name);
	}

	/**
	 * Gets the server pool's connection string
	 *
	 * @param array $server_details
	 * @return string
	 */
	protected function buildConnectionString(array $server_details) {
		$string = 'mongodb://';
		if(isset($server_details['user']) && $server_details['user']) {
			$string .= $server_details['user'] . ':' . $server_details['pass'] . '@';
		}
		return $string . implode(',', $server_details['server']);
	}
}