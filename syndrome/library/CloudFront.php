<?php

/**
 * A PHP5 class for invalidating Amazon CloudFront objects via its API.
 */

require_once 'HTTP/Request.php';  // grab with "pear install --onlyreqdeps HTTP_Request"


class CloudFront {
	
	var $serviceUrl;
	var $accessKeyId;
	var $responseBody;
	var $responseCode;
	var $distributionId;
	
	
	/**
	 * Constructs a CloudFront object and assigns required account values
	 * @param $accessKeyId		{String} AWS access key id
	 * @param $secretKey		{String} AWS secret key
	 * @param $distributionId	{String} CloudFront distribution id
	 * @param $serviceUrl 		{String} Optional parameter for overriding cloudfront api URL
	 */
	function __construct($accessKeyId, $secretKey, $distributionId, $serviceUrl="https://cloudfront.amazonaws.com/"){
		$this->accessKeyId    = $accessKeyId;
		$this->secretKey      = $secretKey;
		$this->distributionId = $distributionId;
		$this->serviceUrl     = $serviceUrl;		
	}
	
	
	/**
	 * Invalidates object with passed key on CloudFront
	 * @param $key 	{String|Array} Key of object to be invalidated, or set of such keys
	 */   
	function invalidate($keys, $debug=false){
		if (!is_array($keys)){
			$keys = array($keys);
		}
		$date       = gmdate("D, d M Y G:i:s T");
		$requestUrl = $this->serviceUrl."2010-08-01/distribution/" . $this->distributionId . "/invalidation";
		// assemble request body
		$body  = "<InvalidationBatch>";
		foreach($keys as $key){
			$key   = (preg_match("/^\//", $key)) ? $key : "/" . $key;
			$body .= "<Path>".$key."</Path>";
		}
		$body .= "<CallerReference>".time()."</CallerReference>";
		$body .= "</InvalidationBatch>";
		// make and send request		
		$req = & new HTTP_Request($requestUrl);
		$req->setMethod("POST");
		$req->addHeader("Date", $date);
		$req->addHeader("Authorization", $this->makeKey($date));
		$req->addHeader("Content-Type", "text/xml");
		$req->setBody($body);
		$response           = $req->sendRequest();
		$this->responseCode = $req->getResponseCode();
		if ($debug==true){
			$er = array();
			array_push($er, "CloudFront: Invalidating Object: $key");
			array_push($er, $requestUrl);
			array_push($er, "body: $body");
			array_push($er, "response: $response");
			array_push($er, "response string: " . $req->getResponseBody());
			array_push($er, "");
			array_push($er, "response code: " . $this->responseCode);
			array_push($er, "");
			return implode("\n",$er);
		}
		else {
			return ($this->responseCode === 201);
		}
	}
	
	
	/**
	 * Returns header string containing encoded authentication key
	 * @param 	$date 		{Date}
	 * @return 	{String}
	 */
	function makeKey($date){
		return "AWS " . $this->accessKeyId . ":" . base64_encode($this->hmacSha1($this->secretKey, $date));
	}
	
	
	/**
	 * Returns HMAC string
	 * @param 	$key 		{String}
	 * @param 	$date		{Date}
	 * @return 	{String}
	 */	
	function hmacSha1($key, $date){
		$blocksize = 64;
		$hashfunc  = 'sha1';
		if (strlen($key)>$blocksize){
			$key = pack('H*', $hashfunc($key));
		}
		$key  = str_pad($key,$blocksize,chr(0x00));
		$ipad = str_repeat(chr(0x36),$blocksize);
		$opad = str_repeat(chr(0x5c),$blocksize);
		$hmac = pack('H*', $hashfunc( ($key^$opad).pack('H*',$hashfunc(($key^$ipad).$date)) ));
		return $hmac;
	}
}
?>	
