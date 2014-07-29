<?php

class Curl {
	/**
	 * The curl resource needed to make outbound calls
	 *
	 * @var resource
	 */
	protected $curl;
	/**
	 * The file resource of the cookie needed to make curl calls
	 *
	 * @var resource
	 */
	protected $cookie;
	/**
	 * The path to the cookie needed to make curl calls
	 *
	 * @var string
	 */
	protected $cookie_name;

	/**
	 * Status code of headers from curl_exec response
	 * @var int
	 */
	protected $status_code = 0;
	/**
	 * Status message of headers from curl_exec response
	 * @var string
	 */
	protected $status_message = '';

	/**
	 * Class constructor initialized the curl options
	 */
	public function __construct() {
		$this->initCURL();
	}

	/**
	 * Factory method
	 *
	 * @return Curl
	 */
	public static function create(){
		return new self();
	}

	/**
	 * Logs the user out of thier current session with the remote site
	 */
	public function __destruct(){
		$this->closeCURL();
	}

	/**
	 * Initializes the curl options needed for this service
	 */
	protected function initCURL(){
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->curl, CURLOPT_HEADER, false);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, true);
		curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($this->curl, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, array($this, 'parseHeaders'));
	}

	/**
	 *
	 * Set Connection Timeout
	 * @param int $seconds
	 */
	public function setConnectTimeout($seconds){
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $seconds);
	}

	/**
	 * Initializes the curl cookie options needed for this service
	 */
	protected function initCookieOpt(){
		if(!$this->cookie_name){
			$this->cookie_name = '/tmp/' . md5(rand(1,99999999) . time()) . '.cookie';
			$this->createCookieJar();

			curl_setopt($this->curl, CURLOPT_COOKIEFILE,$this->cookie_name);
			curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie_name);
		}
	}

	/**
	 * Opens the file resource for  the curl cookie options needed for this service
	 */
	protected function createCookieJar(){
		$this->cookie = fopen($this->cookie_name, "w");
	}

	/**
	 * Close the curl connection with a option to remove the cookie
	 *
	 * @param book $clean_cookie If true, it will destroy the cookie that contains the curl session information
	 */
	public function closeCURL($clean_cookie = true) {
		if(is_resource($this->curl)) {
			curl_close($this->curl);
		}
		if($clean_cookie && is_resource($this->cookie)) {
			fclose($this->cookie);
			unlink($this->cookie_file);
		}
	}

	/**
	 * Function to set a curl option outside of standard range
	 * @param int $option
	 * @param mixed $value
	 * @return Curl
	 */
	public function setOpt($option, $value) {
		curl_setopt($this->curl, $option, $value);
		return $this;
	}

	/**
	 * Parses headers from a curl_exec response
	 * @param resource $curl
	 * @param string $header
	 * @return int
	 */
	protected function parseHeaders($curl, $header) {
		$matches = array ();
		if (preg_match('/HTTP\/\d.\d (\d+) (.+)/', $header, $matches)) {
			$this->status_code = intval($matches[1]);
			$this->status_message = trim($matches[2]);
		}
		return strlen($header);
	}

	/**
	 * Returns status header code from curl response
	 * @return int
	 */
	public function getStatusCode() {
		return $this->status_code;
	}

	/**
	 * Does a GET request to a remote server using a curl call
	 *
	 * @param string $url The remote url for the curl call
	 * @param bool $header $header If TRUE the returned value will also contain the received header information of the request
	 * @param bool $referer If FALSE it will not send any HTTP_REFERER headers to the server. Otherwise the value of this variable is the HTTP_REFERER sent.
	 * @param array $headers An array of custom headers to be sent to the server
	 * @param bool $cookie If TRUE, a cookie jar will be used
	 * @return string|false
	 */
	public function get($url, $header = false, $referer = false, $headers = array(), $cookie = false){
		if($cookie){
			$this->initCookieOpt();
		}
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_POST,false);
		curl_setopt($this->curl, CURLOPT_HTTPGET ,true);
		$this->setCurlOpt($header, $referer, $headers);
		$result=curl_exec($this->curl);

		return $result;
	}

	/**
	 * Does a POST request to a remote server using a curl call
	 *
	 * @param string $url The remote url for the curl call
	 * @param mixed $post_elements An array of all the elements being send to the server or a string if we are sending raw data
	 * @param bool $header $header If TRUE the returned value will also contain the received header information of the request
	 * @param bool $referer If FALSE it will not send any HTTP_REFERER headers to the server. Otherwise the value of this variable is the HTTP_REFERER sent.
	 * @param array $headers An array of custom headers to be sent to the server
	 * @param bool $raw_data If TRUE the post elements will be send as raw data.
	 * @param bool $cookie If TRUE, a cookie jar will be used
	 * @return string
	 */
	public function post($url, $post_elements, $header = false, $referer = false, $headers = array(), $raw_data = false, $cookie = false){
		if($cookie){
			$this->initCookieOpt();
		}
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_POST,true);
		$this->setCurlOpt($header, $referer, $headers);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->serializePostParams($post_elements, $raw_data));
		$result=curl_exec($this->curl);

		return $result;
	}

	/**
	 * Serializes post request data for a curl request
	 *
	 * @param mixed $post_elements
	 * @param bool $raw_data
	 * @return string
	 */
	protected function serializePostParams($post_elements, $raw_data = false) {
		if($raw_data) {
			return $post_elements;
		}
		$flag = false;
		$elements = '';
		foreach($post_elements as $name => $value) {
			if($flag) {
				$elements .= '&';
			}
			$elements .= "{$name}=" . urlencode($value);
			$flag = true;
		}
		return $elements;
	}

	/**
	 * Set additional curl options related to header, referer and headers
	 *
	 * @param bool $header $header If TRUE the returned value will also contain the received header information of the request
	 * @param bool $referer If FALSE it will not send any HTTP_REFERER headers to the server. Otherwise the value of this variable is the HTTP_REFERER sent.
	 * @param array $headers An array of custom headers to be sent to the server
	 */
	protected function setCurlOpt($header = false, $referer = false, $headers = array()){
		//Send headers?
		if ($headers) {
			$curl_headers=array();
			foreach ($headers as $header_name=>$value)
				$curl_headers[]="{$header_name}: {$value}";
			curl_setopt($this->curl,CURLOPT_HTTPHEADER,$curl_headers);
		}
		//Send referer?
		if ($referer)
			curl_setopt($this->curl, CURLOPT_REFERER, $referer);
		else
			curl_setopt($this->curl, CURLOPT_REFERER, '');
		//Get headers?
		if ($header)
			curl_setopt($this->curl, CURLOPT_HEADER, true);
		else
			curl_setopt($this->curl, CURLOPT_HEADER, false);

	}
}