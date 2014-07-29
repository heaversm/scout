<?php
class Mongo_Config {
	/**
	 * List of connection details for each pool of mongo servers
	 * @var array
	 */
	protected static $servers = array(
		'production' => array(
			'localhost' => array(
				'user' => '',
				'pass' => '',
				'db' => 'scout',
				'server' => array(
					'localhost'
				)
			)
		),
	);
}