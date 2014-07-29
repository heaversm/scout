<?php
class Authentication {
	const STATE_INSTALLED = 1;
	const STATE_UNINSTALLED = 0;

	const ROLE_USER = 'user';

	private $roles = array(
		self::ROLE_USER => 1,
	);

	const COLLECTION_NAME = 'users';

	private $fb_uid;
	private $facebook;
	private $user;
	private static $instance;
	private $time;
	protected $secret = 'J8re?&3RE*t7JE*R';
	protected $logged_in_user;

	const AUTH_EC_SUCCESS = 0;
	const AUTH_EC_NO_ACCOUNT = 1;
	const AUTH_EC_NO_FBUID = 2;

	private function __construct() {
		$this->facebook = FacebookWrapper::create();
	}

	public static function create() {
		if(self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function getUserShard($uid) {
		return self::COLLECTION_NAME;
	}

	public function createAccount($username = '') {
		$fb_user_data = $this->facebook->getUserData();

		if(!in_array($fb_user_data['email'], Config::$authorized_users)) {
			return false;
		}

		if(!$this->getFBUser()) {
			return false;
		}

		if($this->userExists($this->fb_uid)) {
			$user = new User($this->fb_uid);
			if($user->isUninstalled()) {
				$user->reinstall();
			}

			return true;
		}

		$user = new User($this->fb_uid);
		$user->createUserProfile();

		Mongo_Query::create($this->getUserShard($this->fb_uid))
			->values(array(
				'fb_uid' => $this->fb_uid,
				'username' => $user->username,
				'state' => self::STATE_INSTALLED,
				'created_time' => time(),
			))
			->insert();

		$this->login();
		return true;
	}

	public function updateRole($uid, $role) {
		Mongo_Query::create($this->getUserShard($uid))
			->values(array(
				'role' => $role,
				'fb_uid' => $uid
			))
			->update();
	}

	/**
	 * Validate the session that exists for a visitor
	 *
	 * @todo refactor this so it only makes one db call
	 * @param int $current_platform
	 * @return int
	 */
	public function login() {
		if(!$this->facebook->isCurrentlyFBConnected() || !$this->getFBUser()){
			return self::AUTH_EC_NO_FBUID;
		}

		if(!$this->userExists($this->fb_uid)) {
			return self::AUTH_EC_NO_ACCOUNT;
		}

		$this->setSession($this->fb_uid);
		return self::AUTH_EC_SUCCESS;
	}

	/**
	 * Determine whether or not we're using a fb session
	 * @return bool
	 */
	public function useFBSession(){
		return Config::$platform == Config::DEVICE_FACEBOOK || $this->facebook->isCurrentlyFBConnected();
	}

	/**
	 * Gets the currently logged in user
	 * @return AyiUser|boolean
	 */
	public function getLoggedInUser(){
		if (!$this->isAuthenticated()) {
				return false;
		}
		return $this->logged_in_user;
	}

	/**
	 * detects if a user is logged in or not
	 *
	 * @param bool $use_master use master database to look up user
	 * @return bool
	 */
	public function isAuthenticated() {
		if($this->logged_in_user instanceof User){
			return true;
		}

		SessionHandler::create();
		$isValidSession = $this->validateSession();
		if($isValidSession){
			$temp_user = new User($_SESSION['uid']);
			$isValidSession = $temp_user->isInstalled();
			if($isValidSession){
				$this->logged_in_user = $temp_user;
			}
		}
		return $isValidSession;
	}

	/**
	 * Set the session variables for a user to designate the user is logged in
	 * @return boolean TRUE
	 */
	public function setSession($fb_uid = '') {
		SessionHandler::create();
		$this->time = time();
		$_SESSION['hash'] = md5($fb_uid.$this->time.$this->secret);
		$_SESSION['uid'] = $fb_uid;
		$_SESSION['time'] = $this->time;

		return true;
	}

	/**
	 * Validate the session that exists for a visitor
	 * @return boolean TRUE if session is valid False if not
	 */
	public function validateSession() {
		$session_handler = SessionHandler::create();
		if (isset($_SESSION['time']) && isset($_SESSION['uid']) && isset($_SESSION['hash'])){
		   	$ayi_uid = $_SESSION['uid'];
			if ($ayi_uid < 1) {
				return false;
			}

			$time = $_SESSION['time'];
			//make sure timestamp isn't older than
			if ($time > ($this->time - $session_handler->life_time)) {
				$fb_uid = $_SESSION['uid'];
				$hash = $_SESSION['hash'];
				$time = $_SESSION['time'];
				$recheck_hash = md5($fb_uid . $time . $this->secret);
				if ($recheck_hash == $hash) {
					return true;
				}
			}
		}
		return false;
	}

	public function deauthorize() {
		if($this->useFBSession()){
			$this->facebook->destroySession();
		} else {
			SessionHandler::create()->destroySession();
		}
	}

	public function userExists($uid) {
		$result = Mongo_Query::create($this->getUserShard($uid))
			->where('fb_uid', $uid)
			->findOne();
		return !empty($result);
	}

	public function getPermission() {
		return Mongo_Query::create($this->getUserShard($this->fb_uid))
			->where('fb_uid', $this->fb_uid)
			->columns('permission')
			->findOne();
	}

	public function getUser() {
		if($this->user instanceof User) {
			return $this->user;
		}
		$this->getFBUser();

		if($this->fb_uid !== 0) {
			$this->user = new User($this->fb_uid);
			return $this->user;
		}

		return null;
	}

	public function getFBUser() {
		if($this->fb_uid === null) {
			$this->fb_uid = $this->facebook->getUser();
		}

		return $this->fb_uid;
	}

	public function isLoggedIn() {
		return $this->getFBUser() !== 0;
	}
}