<?php
/**
 * FacebookWrapper
 * creates a common interface for facebook functionality,
 * useful while we migrate to the new facebook library
 *
 * @author Man Hoang
 */
class FacebookWrapper {
	/**
	 * The singleton instance
	 * @var FacebookWrapper
	 */
	protected static $instance = null;

	/**
	 * Internal Facebook Graph Object
	 *
	 * @var BaseFacebook
	 */
	protected $facebook_obj;

	/**
	 * Device that the object is instiantiated from
	 * @var int
	 */
	protected $device;

	protected $facebook_uid;

	public $facebook_user_array;

	protected $cached_user_data = array();

	/**
	 * Set to true if we should use
	 * @var boolean
	 */
	protected $use_app_access_token = false;
	
	protected static $fb_bookmark_keys = array(
		'bookmarks',
		'canvas_bkmk_top',
		'canvas_bkmk_more'
	);

	protected static $data_fields = array(
		'uid',
		'username',
		'first_name',
		'last_name',
		'email'
	);

	protected static $data_fields_extra = array(
		'interests',
		'music',
		'movies',
		'tv',
		'books',
		'activities',
		'religion',
		'political',
		'about_me',
	);

	/**
	 * The data fields being used in Graph API calls
	 * @var array
	 * @todo:#4162
	 * 	- replace self::$data_fields
	 *  - add extra fields used in self::$data_fields
	 */
	protected static $graph_data_fields = array(
		'id',
		'username',
		'first_name',
		'last_name',
	);

	/**
	 * The extra data fields being used in Graph API calls
	 * @var array
	 * @todo:#4162 replace self::$data_fields_extra
	 */
	protected static $graph_data_fields_extra = array(
		'interests',
		'music',
		'movies',
		'television',
		'books',
		'activities',
		'religion',
		'political',
		'bio',
		'likes',
	);

	/**
	 * The map of key convertion from Graph indexes to FQL indexes
	 * @var array
	 */
	protected static $graph_to_fql = array(
		'bio' => 'about_me',
		'interests' => 'interests',
		'music' => 'music',
		'movies' => 'movies',
		'television' => 'tv',
		'music' => 'music',
		'books' => 'books',
		'activities' => 'activities',
		'religion' => 'religion',
		'political' => 'politics',
		'likes' => 'likes',
		'id' => 'uid',
		'birthday' => 'birthday_date',
		'location' => 'current_location',
		'interested_in' => 'meeting_sex',
		'gender' => 'sex',
		'hometown' => 'hometown_location'
	);

	protected static $graph_like_fields = array(
		'interests',
		'music',
		'movies',
		'television',
		'books',
		'activities',
		'likes'
	);

	const OTHER_LIKE_FIELD = 'likes';

	/**
	 * ISO locale code to use when an unsupported locale is detected
	 * @var string
	 */
	const DEFAULT_LOCALE = 'en_US';

	/**
	 * constructor
	 * @param int $device
	 */
	public function __construct(){
		$this->facebook_obj = self::getGraphAPIObject();
	}

	/**
	 * factory to return the singleton instance
	 * @return FacebookWrapper
	 */
	public static function create(){
		if(self::$instance === null){
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * gets a facebook object for external use
	 *
	 * @return BaseFacebook
	 */
	public static function getGraphAPIObject($use_cookie = true) {
		return new Facebook(array(
			'appId' => Config::$third_party['facebook']['app_id'],
			'secret' => Config::$third_party['facebook']['app_secret']
		));
	}

	/**
	 * gets the facebook uid of the logged in user
	 *
	 * @return int
	 */
	public function requireLogin(){
		$user = $this->getUser();
		if(is_null($user) || $user == 0) {
			$this->promptUserForPermissions();
		}
		return $user;
	}

	/**
	 * Gets the fb uid from the session
	 * @return int|null
	 */
	public function getUser(){
		return $this->facebook_obj->getUser();
	}
	
	private function getParamsForLogin() {
		return array(
			'client_id' => Config::$third_party['facebook']['app_id'],
			'redirect_uri' => Config::$url . '/user/create',
			'scope' => FacebookWrapper::getPermissionCSV()
		);	
	}

	/**
	 * Get a url for a full page facebook login
	 *
	 * @return string
	 */
	public function getLoginURL(){
		return $this->facebook_obj->getLoginURL($this->getParamsForLogin());
	}

	/**
	 * Get a url for a full page facebook login
	 *
	 * @return string
	 */
	public function getLogoutURL(){
		return $this->facebook_obj->getLogoutURL(array('next' => Config::$url));
	}

	/**
	 * Redirect the user to the fb permissions page
	 */
	public function promptUserForPermissions() {
		$url = 'http://www.facebook.com/dialog/oauth?' . http_build_query($this->getParamsForLogin());
		Helper_Request::respond($url, Helper_Request::RESPONSE_REDIR_JS);
	}

	/**
	 * see http://developers.facebook.com/docs/authentication/permissions for full list
	 *
	 * @return string
	 */
	public static function getPermissionCSV(){
		return implode(',',array(
			'email',
		));
	}

	/**
	 * Get the mutual firends between the current user and a passed in facebook uid
	 * @param int $uid_them
	 * @return array
	 */
	public function getMutualFriends($uid_them){
		return $this->callGraph('/me/mutualfriends/'.$uid_them);
	}

	/**
	 * turns on app access token, and returns object for chaining
	 *
	 * @return FacebookWrapper
	 */
	public function useAppAccessToken(){
		$this->use_app_access_token = true;
		return $this;
	}

	/**
	 * whether or not the user provided facebook credentials
	 *
	 * @return boolean
	 */
	public function isCurrentlyFBConnected(){
		return $this->getFacebookUid() > 0;
	}

	/**
	 * Checks if a user has the fb platform installed and is currently connected to facebook
	 * @param AyiUser $user
	 * @return bool
	 */
	public function userIsInstalledAndConnected(AyiUser $user){
		return $user->hasPlatform(Config::DEVICE_FACEBOOK) && $this->isCurrentlyFBConnected();
	}

	/**
	 * function that either returns the objects facebook uid or the provided one
	 *
	 * @param int $facebook_uid
	 * @return int
	 */
	public function getFacebookUid($facebook_uid = null){
		if(!is_null($facebook_uid)) {
			return $facebook_uid;
		}

		if(!is_null($this->facebook_uid)){
			return $this->facebook_uid;
		}

		return $this->facebook_obj->getUser() ;
	}

	/**
	 * Uses appropriate facebook object to call users.getInfo
	 *
	 * @param int $facebook_uid
	 * @param array $fields
	 * @return array
	 */
	private function usersGetInfo($facebook_uid, $fields) {
		return $this->callGraph(array(
			'method' => 'users.getInfo',
			'uids' => $facebook_uid,
			'fields' => $fields
		), 'users_get_info');
	}

	/**
	 * Facebook object way of getting some users data
	 *
	 * @param boolean $get_extra_info
	 * @param int $facebook_uid
	 * @return array
	 */
	public function getUserData($get_extra_info = false, $facebook_uid = null){
		$facebook_uid = $this->getFacebookUid($facebook_uid);
		if(!$get_extra_info && array_key_exists($facebook_uid,$this->cached_user_data)){
			return $this->cached_user_data[$facebook_uid];
		}
		
		$fields = $get_extra_info ? array_merge(self::$data_fields, self::$data_fields_extra) : self::$data_fields;
		$user_info_res = $this->usersGetInfo($facebook_uid, $fields);

		$user_data = isset($user_info_res[0]) ? $user_info_res[0] : array();

		if(!$get_extra_info){
			$this->cached_user_data[$facebook_uid] = $user_data;
		}
		return $user_data;
	}

	/**
	 * Return an id from facebook that we can share with third parties
	 * @param int $facebook_uid
	 * @return string
	 */
	public function getThirdPartyIdentifier($facebook_uid) {
		$third_party_id = '';
		$result = $this->callGraph($facebook_uid . '&fields=third_party_id', 'get_third_party_id');
		if(is_array($result) && isset($result['third_party_id'])) {
			$third_party_id = $result['third_party_id'];
		}
		return $third_party_id;
	}

	/**
	 * Return an id from facebook that we can share with third parties
	 * @param int $facebook_uid
	 * @return string
	 */
	public function getFacebookUidFromThirdPartyIdentifier($third_party_id) {
		$facebook_uid = '';
		$fql = 'SELECT uid FROM user WHERE third_party_id = "' . $third_party_id . '"';
		$this->useAppAccessToken();
		$fql_result = $this->runFQL($fql, 'fql_fbid_from_tpid');
		if(self::isValidFQLResult($fql_result) && isset($fql_result[0]['uid'])) {
			$facebook_uid = $fql_result[0]['uid'];
		}
		return $facebook_uid;
	}

	/**
	 * gets data for a group of facebook uids
	 *
	 * @param array $facebook_uids
	 * @param bool $get_extra_info
	 * @return array
	 */
	public function getUsersData(array $facebook_uids = null, $get_extra_info = false) {
		if (empty($facebook_uids)) {
			return array();
		}

		$fields_to_use = $get_extra_info ? array_merge(self::$graph_data_fields, self::$graph_data_fields_extra) : self::$graph_data_fields;
		$http_params = array(
			'ids' => implode(",", $facebook_uids),
			'fields' => implode(',', $fields_to_use)
		);
		$result =  $this->callGraph('?' . http_build_query($http_params, null, '&'), 'new_users_info');
		return $this->convertGraphDataToAyi($result);
	}


	/**
	 * Creates an array of facebook picture urls indexed by facebook uid
	 * @param array $facebook_uids
	 * @return array
	 */
	public function getProfilePics(array $facebook_uids){
		$profile_pics = array();
		$fb_result = $this->usersGetInfo($facebook_uids, array('pic'));
		if(Helper_Request::assureVariable($fb_result, 'ARR')){
			foreach($fb_result as $index => $facebook_info){
				if(is_array($facebook_info) && array_key_exists('uid', $facebook_info) && array_key_exists('pic', $facebook_info)){
					$profile_pics[$facebook_info['uid']] = $facebook_info['pic'];
				}
			}
		}
		return $profile_pics;
	}

	/**
	 * Uses appropriate facebook object to run an fql query
	 *
	 * @param string query
	 * @param string $nickname
	 * @return array
	 * @throws Exception in case of error if $catch_exception is false
	 */
	protected function runFQL($query, $nickname = 'fql_query') {
		return $this->callGraph(array(
			'method' => 'fql.query',
			'query' => $query
		), $nickname);
	}

	/**
	 * checks to see if an fql call is valid
	 *
	 * @param array $fql_result
	 * @return boolean
	 */
	private static function isValidFQLResult($fql_result){
		return is_array($fql_result) && !isset($fql_result['error_code']);
	}

	/**
	 * parses a friend id response
	 *
	 * @param array $fql_result
	 * @return array
	 */
	private function parseFriendIds($fql_result){
		$friends = array();
		if(self::isValidFQLResult($fql_result)){
			foreach ($fql_result as $friend){
				$friends[] = $friend['uid'];
			}
		}
		return $friends;
	}

	/**
	 * gets an array of the current users friend ids
	 *
	 * @return array
	 */
	public function getFriendIds(){
		return $this->callGraph(array(
			'method' => 'friends_get'
		), 'get_friend_ids');
	}

	/**
	 * gets an array of the current users friends
	 *
	 * @return array/false the list of friends or false, in case of error
	 */
	public function getFriends(){
		$fql = 'SELECT uid, name, sex FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1 = '.$this->getFacebookUid().')';
		$fql_result = $this->runFQL($fql, 'fql_get_friends');
		return $fql_result;
	}


	/**
	 * old facebook object way of getting online now app users
	 *
	 * @param int $facebook_uid
	 * @return array
	 */
	public function getFriendIdsOnlineAppUser($facebook_uid = null){
		$fql = 'SELECT uid FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1="'.$this->getFacebookUid($facebook_uid).'" ORDER BY rand() LIMIT 500) AND is_app_user=1 AND (online_presence="idle" OR online_presence="active") ORDER BY last_name';
		$fql_result=$this->runFQL($fql, 'fql_online_friends');
		return $this->parseFriendIds($fql_result);
	}

	/**
	 * Is this visit from a bookmark?
	 * @return bool
	 */
	public static function isBookmarkVisit(){
		return in_array(Helper_Request::getRequest('ref', ''), self::$fb_bookmark_keys);
	}

	/**
	 * get the bookmark count
	 * @return int
	 */
	public static function getBookmarkCount(){
		return self::isBookmarkVisit() ? Helper_Request::getRequest('count', 0, 'INT') : 0;
	}
	
	/**
	 * tracks a bookmark visit
	 */
	public static function trackBookmarkVisit(){
		if(self::isBookmarkVisit()) {
			$bookmark_count = self::getBookmarkCount();
			if($bookmark_count > 10) {
				$bookmark_count = '10+';
			}
			$bookmark_position = Helper_Request::getRequest('fb_bmpos', '', 'STR');
			$bookmark_source = Helper_Request::getRequest('fb_source', '', 'STR');
			if($bookmark_position != '' && $bookmark_source != '') {
				$position_prepend = $bookmark_source == 'bookmarks_apps' ? 'apps_' : 'favs_';
				$position_parts = explode('_', $bookmark_position);
				if(count($position_parts) == 2) {
				}
			}
		}
	}

	/**
	 * Retrieve users' like information from Facebook
	 * @param array $facebook_uids
	 * @return array
	 */
	public function getOtherLikes(array $facebook_uids = array()){
		$result = array();

		if(empty($facebook_uids)){
			$facebook_uids = array($this->getFacebookUid());
		}
		$http_params = array(
			'ids' => implode(',',$facebook_uids),
			'fields' => implode(',',self::$graph_like_fields),
		);

		try{
			$info_result = $this->facebook_obj->api('?'.http_build_query($http_params, null, '&'));
		}catch(Exception $e){
			return array();
		}

		if(isset($info_result['error_code'])) {
			return $result;
		}
		foreach($info_result as $facebook_uid => $user_data){
			$result[$facebook_uid] = '';
			if(!is_array($user_data) || !array_key_exists(self::OTHER_LIKE_FIELD,$user_data) || empty($user_data[self::OTHER_LIKE_FIELD]['data'])){
				continue;
			}

			//consume all data except "other" likes
			$defined_likes = array();
			foreach(self::$graph_like_fields as $like_field){
				if($like_field == self::OTHER_LIKE_FIELD || !array_key_exists($like_field,$user_data) || !is_array($user_data[$like_field]['data'])){
					continue;
				}
				foreach ($user_data[$like_field]['data'] as $defined_like){
					if(isset($defined_like['name'])){
						$defined_likes[$defined_like['id']] = $defined_like['name'];
					}
				}
			}

			//compare "other" likes to defined ones
			$other_likes = array();
			foreach($user_data[self::OTHER_LIKE_FIELD]['data'] as $other_like){
				if(!array_key_exists($other_like['id'],$defined_likes) && array_key_exists('name', $other_like)){
					$other_likes[$other_like['id']] = $other_like['name'];
				}
			}
			$result[$facebook_uid] = implode(', ',$other_likes);
		}
		return $result;
	}

	/**
	 * Retrieves the app access token from facebook
	 * The app access tokens are already stored, this should only be called if we ever need to replace them or add a test app
	 * @return string
	 */
	public static function getAppAccessToken($app_id, $secret){
		$curl =  new Curl();
		$response =  $curl->post('https://graph.facebook.com/oauth/access_token', array(
			'grant_type' => 'client_credentials',
			'client_id' => $app_id,
			'client_secret' => $secret
		));
		return strpos($response, '=') ? substr($response, strpos($response, '=') + 1) : '';

	}

	/**
	 * Make a facebook graph call
	 * if parameters is an array the rest server is called
	 * if parameters is a string the graph is called
	 *
	 * @param mixed string|array $parameters
	 * @param string $nickname
	 * @param string $graph_method
	 * @param array $graph_parameters
	 * @return array
	 * @throws Exception in case of error if $catch_exception is false
	 */
	public function callGraph($parameters, $nickname = '', $graph_method = 'GET', $graph_parameters = array()){
		try {
			if($this->use_app_access_token){
				$this->facebook_obj->setAccessToken(Config::$third_party['facebook']['app_access_token']);
			}
			if(!empty($graph_parameters)){
				$result = $this->facebook_obj->api($parameters, $graph_method, $graph_parameters);
			}else{
				$result = $this->facebook_obj->api($parameters);
			}
			return is_array($result) && !isset($result['error_code']) ? $result : array();
		} catch(Exception $e) {
			$error_message = substr(preg_replace('/\d+/', 'XXX', $e->getMessage()), 0, 15);
			return array();
		}
	}

	/**
	 * Destroy the users facebook session
	 */
	public function destroySession(){
		//the facebook either sets the domain for cookie, or doesn't...
		setcookie('fbsr_'.$this->facebook_obj->getAppId(), '', time() - 3600, '/');
		
		setcookie('fbsr_'.$this->facebook_obj->getAppId(), '', time() - 3600, '/', Config::$cookie_domain);
		setcookie('PHPSESSID', '', time() - 3600, '/');

		unset($_SESSION['fb_' . $this->facebook_obj->getAppId() . '_code']);
		unset($_SESSION['fb_' . $this->facebook_obj->getAppId() . '_access_token']);
		unset($_SESSION['fb_' . $this->facebook_obj->getAppId() . '_user_id']);
	}	
	
	public function getGraphProfilePicUrl($facebook_uid, $size = 'square', $force_https = true) {
		return ($force_https ? 'https://' : 'http://') . 'graph.facebook.com/' . $facebook_uid . '/picture' . '?type=' . $size; // . '#/' . $facebook_uid;
	}
	
	public function getGraphProfilePicTag($facebook_uid, $size = 'square', $force_https = true) {
		return '<img src="'.$this->getGraphProfilePicUrl($facebook_uid, $size, $force_https).'" alt=""/>';
	}
}
