<?php
class Twitter {
	private $user;
	private static $instance;
	private $exists;
	
	const TWITTER_MC_KEY = 'TWITTER';
	const COLLECTION_NAME = 'twitter';
	
	private function __construct(User $user) {
		$this->user = $user;
	}
	
	public static function getInstance(User $user) {
		if(self::$instance === null) {
			self::$instance = new self($user);
		}
		
		return self::$instance;
	}
	
	private function getCollectionName() {
		return self::COLLECTION_NAME.'_'.Helper_Number::getModuloId($this->user->uid);
	}
	
	public function saveAccessToken($twitter_username, $access_token, $access_token_secret) {
		if(!$this->exists()) {
			$id = $this->user->uid;
			DataStore::getInstance()->setCollection($this->getCollectionName())
				->createDocument(array(
					'fb_uid' => $this->user->uid,
					'twitter_username' => $twitter_username,
					'access_token' => $access_token,
					'access_token_secret' => $access_token_secret,
					'created_time' => time(),
				), $id);
			
			SynMemcache::getInstance()->set($this->user->appendUidTo(self::TWITTER_MC_KEY), true);		
		}
	}
	
	public static function getPage($template, $user) {
		if(Config::$platform !== Config::DEVICE_FACEBOOK) {
			$twitter_authentication_exists = Twitter::getInstance($user)->exists();
			if($twitter_authentication_exists) {
				$template->show_twitter_authentication = 'hide';
				$template->twitter_authentication_url = '';
				$template->show_twitter_viral = '';
			} else {
				$template->show_twitter_viral = 'hide';
				$template->show_twitter_authentication = '';
				$twitter = new EpiTwitter(Config::$third_party['twitter']['consumer_key'], Config::$third_party['twitter']['consumer_secret']);
				try{  
					$template->twitter_authentication_url = $twitter->getAuthorizeUrl(null, array('oauth_callback' => Config::$url.'/twitter/create'));
				} catch(EpiTwitterException $e){  
					$template->twitter_authentication_url = '';
				} catch(Exception $e){  
					$template->twitter_authentication_url = '';
				}  
			}
		} else {
			$template->show_twitter_viral = 'hide';
			$template->show_twitter_authentication = 'hide';
		}
	}
	
	public function getAccessToken() {
		return DataStore::getInstance()
			->setCollection($this->getCollectionName())
			->getById($this->user->uid);
	}
	
	public function exists() {
		if($this->exists === null) {
			$this->exists = SynMemcache::getInstance()->get($this->user->appendUidTo(self::TWITTER_MC_KEY)) && 
				DataStore::getInstance()
					->setCollection($this->getCollectionName())
					->getById($this->user->uid);
		}
		
		return $this->exists;
	}
	
	public function remove() {
		DataStore::getInstance()
			->setCollection($this->getCollectionName())
			->removeDocument($this->user->uid);
	}
}