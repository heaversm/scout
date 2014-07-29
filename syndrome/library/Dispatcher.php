<?php
class Dispatcher {

	/**
	 * The internal Authentication instance for checking if a user is logged in
	 * @var Authentication
	 */
	protected $auth = null;

	/**
	 * The currently logged in user
	 * The fb_uid will be 0 if not logged in
	 * @var User
	 */
	protected $user = null;

	/**
	 *
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected static $instance = null;

	/**
	 * Constructor that creates a new Dispatcher
	 */
	protected function __construct() {
	}

	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Tracking bookmark visit information
	 *
	 * @return Dispatcher
	 */
	public function trackBookmarkVisitFlow() {
		return $this;
	}
	/**
	 * Forces app to use the fully qualified name
	 */
	public function useFQDN() {
		Helper_Request::redirectToWWW();
		return $this;
	}

	public function useSecure() {
		return $this;
	}

	/**
	 * Create the internal authentication object for use with the controllers
	 *
	 * @return Dispatcher
	 */
	protected function authenticate() {
		// we only want to do this once
		if($this->auth !== null) {
			return $this;
		}

		// create the auth object
		$this->auth = Authentication::create();

		// attempt to get the logged in user
		$user = $this->auth->getLoggedInUser();
		if($user && is_a($user, 'User')) {
			// user is logged in
			$this->user = $user;
		}
		return $this;
	}

	public function trackInvite() {
		$inviter_uid = Helper_Request::getRequest('inviter_uid', '', 'STR');
		if($inviter_uid !== '' && $this->getUser() && $inviter_uid != $this->getUser()->uid) {
			Invite::getInstance()
				->setParams($inviter_uid, array($this->getUser()->uid))
				->initTrack();
		}
	}
	/**
	 * Handle dispatching these events to the controllers
	 *
	 * @return Controller
	 */
	public function dispatch() {
//		$this->useFQDN();
		$this->useSecure();
		// attempt to authenticate the user
		$this->authenticate();
		return Controller::dispatch($this);
	}

	/**
	 * Gets the current Authentication object
	 *
	 * @return FacebookAuthentication
	 */
	public function getAuth() {
		return $this->auth;
	}

	/**
	 * Gets the currently logged in user if any
	 *
	 * @return AyiUser
	 */
	public function getUser() {
		return $this->user;
	}
}