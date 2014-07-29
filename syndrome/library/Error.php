<?php
class Error {
	const CODE_SUCCESS = 0;
	const CODE_ERROR = 1;
	
	private static $instance;
	private $error_messages;
	
	private function __construct($error_messages) {
		$this->error_messages = $error_messages;
	}
	
	public static function getInstance($error_messages) {
		if(self::$instance === null) {
			self::$instance = new self($error_messages);
		}
		
		return self::$instance;
	}
	
	public function getError($error_code) {
		return array(
			'error' => self::CODE_ERROR,
			'code' => $error_code,
			'message' => $this->error_messages[$error_code],
		);
	}
}