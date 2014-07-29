<?php
class Controller_User extends BaseController_Web {
	protected $auth;

	public function setUrlKey($url_key) {
		$this->url_key = $url_key;
	}
	
	public function __construct() {
		parent::__construct();
		$this->auth = Authentication::create();
	}
	
	public function createAction() {
		$this->auth->createAccount();
		Helper_Request::respond(Config::$url, Helper_Request::RESPONSE_REDIR, Config::$platform);
	}
	
	public function installAction() {
		Helper_Request::respond($this->facebook->getLoginURL());
	}

	public function loginAction() {
		$error = $this->auth->login();
		Helper_Request::respond(Config::$url, Helper_Request::RESPONSE_REDIR, Config::$platform);
	}
	
	public function logoutAction() {
		$this->auth->deauthorize();
		Helper_Request::respond(FacebookWrapper::create()->getLogoutURL());
	}
}