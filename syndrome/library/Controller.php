<?php
class Controller {
	protected $template;
	protected $memory;
	protected $path;
	protected $meta;
	protected $static_content;
	protected $facebook;

	/**
	 * A local reference to the dispatcher object
	 * @var Dispatcher
	 */
	protected $dispatcher = null;
	/**
	 * The method that has been called by the query string
	 * @var string
	 */
	protected $method = null;
	
	/**
	 * The name of the default action/method if none other controller/method can be found
	 * @var string
	 */
	const DEFAULT_ACTION = 'default';

	/**
	 * Constructor function will create a new Controller instance
	 */
	protected function __construct() {
		$this->dispatcher = Dispatcher::getInstance();
		$this->template = Template::getInstance();
		$this->path = Path::getInstance();
		$this->memory = Memory::getInstance();
		$this->meta = Meta_Manager::getInstance();
		$this->static_content = StaticContent::getInstance();
		$this->facebook = FacebookWrapper::create();
	}

	/**
	 * Function that is run before each dispatch operation call (allows for cleanup and such)
	 *
	 * @return Controller
	 */
	protected function preDispatch() {
		return $this;
	}

	/**
	 * Run once post controller method for actions that will respond to the client
	 *
	 * @return Controller
	 */
	protected function postDispatch() {

		return $this;
	}

	/**
	 * Run once post controller method for actions that will respond to the client
	 *
	 * @return Controller
	 */
	protected function respond() {

		return $this;
	}

	/**
	 * The default action to be taken if the request action doesn't exist
	 */
	protected function defaultAction() {

		return $this;
	}

	/**
	 * Master body function that controls which action to execute
	 *
	 * @return Controller
	 */
	final private function execute() {
		$action = $this->getMethodName();
		$is_ajax = Helper_Request::isAjax();
		$data_type = strtolower(Helper_Request::setDefault($_SERVER['HTTP_ACCEPT'], ''));
		if($is_ajax && preg_match('/\w+\/json|\w+\/javascript/i', $data_type) && method_exists($this, $action . 'JsonAction')) {
			// it there was a ajax json request and the ajax json specific method exists, execute it
			return $this->{$action . 'JsonAction'}();
		} elseif($is_ajax && preg_match('/\w+\/(?:html|xml)/i', $data_type) && method_exists($this, $action . 'HtmlAction')) {
			// it there was a ajax html request and the ajax html specific method exists, execute it
			return $this->{$action . 'HtmlAction'}();
		} elseif($is_ajax && method_exists($this, $action . 'AjaxAction')) {
			// it there was a ajax request and the ajax specific method exists, execute it
			return $this->{$action . 'AjaxAction'}();
		} elseif(method_exists($this, $action . 'Action')) {
			// execute the named method if it exists
			return $this->{$action . 'Action'}();
		} else {
			// execute the default method otherwise (which always exist)
			return $this->defaultAction();
		}
		return $this;
	}

	/**
	 * Gets the forwarded controller object to be returned for the next iteration
	 *
	 * @param string|Controller $controller
	 * @param string $method
	 * @return Controller
	 */
	protected function forward($controller, $method = null) {
		if($controller === $this) {
			$new_controller = clone $this;
			$new_controller->method = $method ? strtolower($method) : self::DEFAULT_ACTION;
		} else {
			$new_controller = self::get($controller, $method);
			$new_controller->forwardProperties($this);
		}
		return $new_controller;
	}

	/**
	 * Stops the current execution of this script and redirect to another controller
	 *
	 * @param string $controller
	 * @param string $method
	 * @param array $params The $_GET variables to pass through
	 * @return Controller
	 */
	protected function redirect($controller, $method = null, array $params = null) {
		$url = '/';
		$url .= preg_replace('/(^|\/)default$/', '$1', trim(str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', is_string($controller) ? $controller : $controller->getControllerName()))), '-'));
		if($method) {
			$method = trim(strtolower($method));
			if($method != self::DEFAULT_ACTION) {
				$url .= '/' . str_replace('_', '-', $method);
			}
		}
		if(is_array($params)) {
			unset($params['action']);
			unset($params['method']);
			if(!empty($params)) {
				$url .= '?' . http_build_query($params);
			}
		}
		Helper_Request::respond(Config::$url.$url, Helper_Request::RESPONSE_REDIR);
		return $this;
	}

	/**
	 * Transfer properties from the passed in controller to this controller
	 *
	 * @param Controller $controller
	 * @return Controller
	 */
	protected function forwardProperties(Controller $controller) {
		return $this;
	}

	/**
	 * Get the real action name from the request string
	 *
	 * @param string $action
	 * @return string
	 */
	protected function getMethodName() {
		if(!$this->method) {
			return self::DEFAULT_ACTION;
		}
		$names = preg_split('/[^A-Za-z0-9]/', $this->method);
		$parts = array();
		foreach($names as $i => $part) {
			$key = strtolower($part);
			if($i != 0) {
				$key = ucfirst($key);
			}
			$parts[] = $key;
		}
		return implode('', $parts);
	}

	/**
	 * Gets the current controller action
	 *
	 * @return string
	 */
	protected function getControllerName() {
		return str_replace('Controller_', '', get_class($this));
	}

	/**
	 * Retrieve a $_REQUEST variable with validation rules
	 *
	 * @param string $param
	 * @param mixed $default_value
	 * @param string $type
	 * @return mixed
	 */
	protected function getRequest($param, $default_value = null, $type = '') {
		if(is_array($param)) {
			foreach($param as $p) {
				$r = Helper_Request::getRequest($p, $default_value, $type);
				if($r != $default_value) {
					return $r;
				}
			}
			return $default_value;
		}
		return Helper_Request::getRequest($param, $default_value, $type);
	}

	/**
	 * Asserts a $_REQUEST variable with validation rules
	 *
	 * @param string $param
	 * @param string $type
	 * @return bool
	 */
	protected function assureRequest($param, $type = '') {
		return Helper_Request::assureRequest($param, $type);
	}

	/**
	 * Asserts a $_REQUEST variable was send in
	 *
	 * @param string $param
	 * @param string $type
	 * @return bool
	 */
	protected function existsRequest($param, $type = '') {
		return Helper_Request::existsRequest($param, $type);
	}

	/**
	 * Retrieve a $_REQUEST sub variable with validation rules
	 *
	 * @param string $param
	 * @param string $key The sub key to get from the request variable
	 * @param mixed $default_value
	 * @param string $type
	 * @return mixed
	 */
	protected function getRequestFromRequestStr($param, $key, $default_value, $type = '') {
		if(!isset($_REQUEST[$param])) {
			return $default_value;
		}
		$params = $this->getRequestFromQuery($param);
		return Helper_Request::setDefault($params[$key], $default_value, $type);
	}

	/**
	 * Retrieves a $_REQUEST array from a redir
	 *
	 * @param string $param
	 * @return array()
	 */
	protected function getRequestFromQuery($param) {
		if(!isset($_REQUEST[$param])) {
			return array();
		}
		$query = parse_url($_REQUEST[$param], PHP_URL_QUERY);
		$params = array();
		if(!$query) {
			return $params;
		}
		parse_str($query, $params);
		return $params;
	}

	/**
	 * Checks if the current request is a GET request
	 *
	 * @return bool
	 */
	protected function isGet() {
		return strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET') === 'GET';
	}

	/**
	 * Checks if the current request is a POST request
	 *
	 * @return bool
	 */
	protected function isPost() {
		return strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET') === 'POST';
	}

	/**
	 * Checks if the current request is a ajax request
	 *
	 * @return bool
	 */
	protected function isAjax() {
		return Helper_Request::isAjax();
	}

	/**
	 * Checks if the current request is a ajax or a post request
	 *
	 * @return bool
	 */
	protected function isPostback() {
		return $this->isAjax() || $this->isPost();
	}

	/**
	 * Determine the template name used to load the subpage based on controller and supage
	 *
	 * @return string
	 */
	public function determineTemplateName() {
		return strtolower($this->getControllerName());
	}

	/**
	 * Retrieve the uri of the current page request
	 *
	 * @return string
	 */
	protected function getRequestUri() {
		return isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : '/';
	}

	/**
	 * Retrieve the uri of the page to be redirected to if it was passed along
	 *
	 * @return string
	 */
	protected function getRedirectUri() {
		$redir = $this->getRequest('redir', '', 'STR');
		if($redir) {
			return $redir;
		}
		return $this->getRequestUri();
	}

	/**
	 * Run the core execution logic of the controller framework
	 *
	 * @param Dispatcher $dispatcher
	 * @return Controller
	 */
	public static function dispatch(Dispatcher $dispatcher) {
		// Get the requested controller object as a starting point
		$controller = self::getFromQuery();

		// run any pre dispatch functions (only ever gets run once) and will return a new controller if it forwards
		$temp_controller = $controller->preDispatch();
		// we only want to set the new controller if it has changed
		if($temp_controller !== null && $temp_controller !== $controller) {
			$controller = $temp_controller;
		}
		// Continue to run the body function as long as we have transferred
		do {
			// then we want to execute the controller object (we're not allowing fowards for now)
			$temp_controller = $controller->execute();
			if($temp_controller !== null && $temp_controller !== $controller) {
				// set the transferred controller as the new controller
				$controller = $temp_controller;
			} else {
				// break out if we're not forwarding
				break;
			}
		} while(true);

		// run any post dispatch functions (only once)
		$controller->postDispatch();
		// now we have managed to finished the transfer routes and on our way to responding to the client
		$controller->respond();
		// return the controller object for no reason at all
		return $controller;
	}

	/**
	 * Gets the base controller object from the query string
	 *
	 * @param string $controller
	 * @param string $method
	 * @return Controller
	 */
	protected static function get($controller, $method = null) {
		if(!$controller) {
			$controller = self::DEFAULT_ACTION;
		}
		
		$controller = strtolower($controller);

		// We try to get the name of the controller to see if we can instantiate it
		$controller_name = self::getName($controller);
		$include_path = self::getIncludePath($controller_name);
		if(file_exists($include_path)) {
			// Great we have a real controller
			require_once $include_path;
		} elseif(strpos($controller, self::DEFAULT_ACTION) !== false) {
			// if we can't find a controller in here, then we're going to 404 out of the loop
			return self::get('error-page', 'default');
		} elseif(strpos($controller, 'error-page') === 0) {
			// this will prevent recursion!
			throw new Exception('Error controller not specified for this module');
		} else {
			// Uh-oh, we need to go to the default controller
			$parts = explode('/', $controller);
			array_pop($parts);
			$parts[] = self::DEFAULT_ACTION;
			return self::get(implode('/', $parts), $method);
		}
		$controller_name = 'Controller_' . $controller_name;
		
		$object = new $controller_name();
		$object->method = $method ? strtolower($method) : null;
		return $object;
	}

	/**
	 * Gets the base controller object from the query string
	 *
	 * @return Controller
	 */
	protected static function getFromQuery() {
		// TODO: we are currently using action as the object for bc
		return self::get(Helper_Request::getRequest('action', self::DEFAULT_ACTION), Helper_Request::getRequest('method', null));
	}

	/**
	 * Gets the include path of the controller object
	 *
	 * @param string $controller_name
	 * @return string
	 */
	protected static function getIncludePath($controller_name) {
		$library_base = array(
			Config::$controller
		);
		$syndrome_base = array(
			Config::$syndrome_controller
		);

		$parts = explode('_', $controller_name);
		$class = array(
			array_pop($parts)
		);
		
		$library_path = implode('/', array_merge($library_base, $parts, $class)) . '.php';
		$syndrome_path = implode('/', array_merge($syndrome_base, $parts, $class)) . '.php';

		if(file_exists($library_path)) {
			return $library_path;
		} else if(file_exists($syndrome_path)) {
			return $syndrome_path;
		}
	}

	/**
	 * Get the real controller name from the request string
	 *
	 * @param string $controller
	 * @return string
	 */
	protected static function getName($controller) {
		// we want to seperate the controller name from the actual path of the controller
		$paths = explode('/', $controller);
		$name = preg_split('/[^A-Za-z0-9]/', array_pop($paths));

		$controller_name = '';
		// Now we clean up the path names and make the class path delimited by _ for unique controllers
		foreach($paths as $path) {
			$controller_name .= ucfirst(strtolower(preg_replace('/[^\w]/', '', $path))) . '_';
		}
		// Now we make sure it's a proper php classname for the base controller part
		foreach($name as $part) {
			$controller_name .= ucfirst(strtolower($part));
		}
		
		return $controller_name;
	}
	
	protected function toString() {
		return strtolower($this->getControllerName().'/'.$this->method);
	}
	
}