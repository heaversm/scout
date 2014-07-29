<?php
class Email {
	private $template_id;
	private $subject;
	private $user;
	private $recipient;
	private $body;
	private $vars;
	private $ses;
	private $sesm;
	private $config = array();
	private $hard_limit;
	private $current_limit;
	
	const COLLECTION_NAME = 'emails';
	const MC_KEY = 'email_limit';
	
	private function __construct() {
	}
	
	public static function create() {
		return new self();
	}
	
	public function setUser($user) {
		$this->user = $user;
		return $this;
	}
	
	public function setRecipient($recipient) {
		$this->recipient = $recipient;
		return $this;
	}
	
	public function setBody($body) {
		$this->body = $body;
		return $this;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
		return $this;
	}
	
	public function setTemplate($template_id) {
		$this->template_id = $template_id;
		$this->config = EmailConfig::$config[$this->template_id];
		$this->hard_limit = $this->config['limit'];
		return $this;
	}
	
	public function setVars($vars) {
		$this->vars = $vars;
		$this->vars['url'] = Config::$url;
		$this->vars['static_host'] = Config::$static_host;
		$this->vars['static_images_url'] = Config::$images_url;
		return $this;
	}
	
	private function getMCKey() {
		return self::MC_KEY.'_'.$this->user->uid.'_'.$this->template_id.'_'.EmailConfig::$limits[$this->hard_limit];
	}
	
	private function canSend() {
		if($this->user->isUninstalled()) {
			return false;
		}
		
		if($this->hard_limit == EmailConfig::LIMIT_NONE) {
			return true;
		}
		
		$preferences = EmailPreferences::getInstance()
			->setUser($this->user)
			->getPreferences();
			
		if(isset($preferences[$this->template_id]) && $preferences[$this->template_id] === false) {
			return false;
		}
		
		$this->current_limit = SynMemcache::getInstance()->get($this->getMCKey());
		if($this->current_limit === false) {
			$this->current_limit = 0;
		}
		return $this->current_limit < $this->hard_limit;
	}
	
	private function parseSubject() {
		foreach($this->vars as $name => $value) {
			$this->subject = str_replace('${'.$name.'}', $value, $this->subject);
		}
	}
	
	public function directSave($email_address, $subject, $body) {
		Mongo_Query::create(self::COLLECTION_NAME)
			->setSanitizeStrings(false)
			->values(array(
				'fb_uid' => 0,
				'template_id' => $this->template_id,
				'subject' => $subject,
				'recipient' => $email_address,
				'body' => $body,
			))->insert();
	}
	
	public function save() {
		if(!$this->canSend()) {
			return false;
		}
		
		$this->setSubject($this->config['subject']);
		$this->parseSubject();
		$body = Template::create()
			->render($this->config['template'])
			->singularParse($this->vars)
			->getRender();
			
		Mongo_Query::create(self::COLLECTION_NAME)
			->setSanitizeStrings(false)
			->values(array(
				'fb_uid' => $this->user->uid,
				'template_id' => $this->template_id,
				'subject' => $this->subject,
				'recipient' => $this->user->email,
				'body' => $body,
			))->insert();
	}
	
	public function send() {
		$this->ses = new SimpleEmailService(Config::$third_party['amazon']['access_key'], Config::$third_party['amazon']['secret_key']);
		$this->sesm = new SimpleEmailServiceMessage();
		$this->sesm
			->addTo($this->recipient)
			->setFrom(EmailConfig::FROM_ADDRESS)
			->setSubject($this->subject)
			->setMessageFromString($this->body);
		$result = $this->ses
			->sendEmail($this->sesm);

		if(!empty($result)) {
			$this->current_limit++;
			SynMemcache::getInstance()->set($this->getMCKey(), $this->current_limit, EmailConfig::LIMIT_LIFE);
			return true;
		}
		
		return false;
	}
}