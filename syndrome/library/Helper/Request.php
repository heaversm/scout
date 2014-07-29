<?php
class Helper_Request {
	const RESPONSE_JSON = 'JSON';
	const RESPONSE_REDIR = 'REDIR';
	const RESPONSE_REDIR_JS = 'REDIR_JS';
	const RESPONSE_PRINT = 'PRINT';

	public static function redirectToWWW() {
		if(Config::$platform == Config::DEVICE_WEB && isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'www.') === false) {
			self::respond(Config::$url.Config::$uri);
		}
	}

	public static function redirectToSSL() {
		self::respond(Config::$secure_url.Config::$uri);
	}
	
	/**
	 * Checks to see if the current script is in HTTPS mode
	 *
	 * @return bool
	 */
	public static function isSsl(){
		return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on');
	}
	
	/**
	 * Detects if request is AJAX
	 * @return bool
	 */
	public static function isAjax(){
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
			&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * alias for getParam
	 *
	 * @param string $param
	 * @param mixed $default_value
	 * @param string $type
	 * @return mixed
	 */
	public static function getRequest($param, $default_value = null,$type = ''){
		return self::setDefault($_REQUEST[$param],$default_value,$type);
	}

	/**
	 * detects if a request param is empty.
	 *
	 * @param string $param
	 * @param string $type
	 * @return boolean
	 */
	public static function assureRequest($param, $type = ''){
		return self::assureVariable($_REQUEST[$param],$type);
	}

	/**
	 * detects if any of a list of request params are empty.
	 *
	 * @param array $params
	 * @param string $type
	 * @return boolean
	 */
	public static function assureRequestList(array $params, $type = ''){
		foreach($params as $param){
			if(!self::assureRequest($param, $type)){
				return false;
			}
		}
		return true;
	}

	/**
	 * detects if the $_REQUEST param is present!
	 *
	 * @param string $param
	 * @return boolean
	 */
	public static function existsRequest($param){
		return array_key_exists($param,$_REQUEST);
	}

	/**
	 * checks a variables and if not matching, defaults to a given value
	 *
	 * @param mixed $variable
	 * @param mixed $default_value
	 * @param string $type
	 * @return mixed
	 */
	public static function setDefault(&$variable,$default_value,$type=''){
		return self::assureVariable($variable,$type)?$variable:$default_value;
	}

	/**
	 * checks to see if a passed in variable meets certain criteria
	 *
	 * @param mixed $variable
	 * @param mixed $type
	 * @return boolean
	 */
	public static function assureVariable(&$variable,$type=''){
		//check that variable exists
		if(!isset($variable) || $variable==''){
			return FALSE;
		}

		//if list of values for type, check if it's present!
		if(is_array($type)){
			return in_array($variable,$type);
		}

		//if type is a string, check for certain strings!
		switch($type){
			case 'INT':
				return ctype_digit((string) $variable);
				break;
			case 'POSINT':
				return ctype_digit((string) $variable) && $variable > 0;
				break;
			case 'STR':
				return is_string($variable);
				break;
			case 'ARR':
				return is_array($variable);
				break;
			default:
				return TRUE;
				break;
		}
	}

	/**
	 * outputs a javascript redirect
	 * @param string $url
	 */
	private static function redirJS($url){
		die('<script type="text/javascript">
			//<![CDATA[
				top.location.href = "' . $url . '";
			//]]>
			</script>');
	}

	/**
	 * tells the browser to go to the specified url and stops current script execution
	 * @param string $url
	 */
	private static function redir($url){
		header('Location: '.$url);
		die();
	}
	
	/**
	 * either redirect or print a response (plain text or json encoded
	 *
	 * @param string $response
	 * @param string $response_type
	 * @param int $platform
	 */
	public static function respond($response, $response_type = self::RESPONSE_REDIR, $platform = Config::DEVICE_WEB){
		if($response_type == self::RESPONSE_REDIR){
			if(Config::getPlatform() == Config::DEVICE_FACEBOOK){
				if(strpos($response, 'apps.facebook') !== FALSE){
					self::redirJS($response);
				}else{
					$signed_request = Helper_Request::getRequest('signed_request', '');
					if($signed_request != ''){
						$separator = (strpos($response, '?') !== false) ? '&' : '?';
						$response .= $separator . 'signed_request='. $signed_request;
					}
				}
			}
			self::redir($response);
		}elseif($response_type == self::RESPONSE_REDIR_JS){
			self::redirJS($response);
		}elseif($response_type == self::RESPONSE_PRINT){
			die($response.'');
		}elseif($response_type == self::RESPONSE_JSON){
			die(json_encode($response).'');
		}
	}
}