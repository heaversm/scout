<?php
/**
 * Controller level base class in the ayi MVC framework for web pages
 *
 * @author Man Hoang
 * @name ControllerSite
 */
class ControllerSite extends Controller {

	/**
	 * Response type enumeration
	 * Autodetect based on the passed through content
	 * @var int
	 */
	const RESPONSE_AUTO = 1;
	/**
	 * Response type enumeration
	 * Respond with plain text
	 * @var int
	 */
	const RESPONSE_TEXT = 2;
	/**
	 * Response type enumeration
	 * Respond with full page content
	 * @var int
	 */
	const RESPONSE_PAGE = 3;
	/**
	 * Response type enumeration
	 * Respond with a json string
	 * @var int
	 */
	const RESPONSE_JSON = 4;
	/**
	 * Response type enumeration
	 * Respond with a redirect
	 * @var int
	 */
	const RESPONSE_REDIR = 5;
	/**
	 * Response type enumeration
	 * Respond with content
	 * @var int
	 */
	const RESPONSE_CONTENT = 6;

	/**
	 * The response to be sent to the client
	 * @var mixed
	 */
	protected $response = '';
	/**
	 * The response type to be sent to the client
	 * @var int
	 */
	protected $response_type = self::RESPONSE_TEXT;
	/**
	 * The post processed response that is will be sent to the client
	 * @var mixed
	 */
	protected $processed_response = '';
	/**
	 * The page response title
	 * @var string
	 */
	protected $title = null;
	/**
	 * A list of css classes to be inserted into the body tag
	 * @var array
	 */
	protected $css_classes = array();
	/**
	 * A list of javascript modules to be included on the page
	 * @var array
	 */
	protected $js_modules = array();

	/**
	 * A list of css modules to be included on the page
	 * @var array
	 */
	protected $css_modules = array();

	/**
	 * Function that is run before each dispatch operation call
	 *
	 * @return ControllerSite
	 */
	protected function preDispatch() {
		// if not logged in, we'll want to transfer to a not logged in controller and call the predispatch method
		if(Config::ALWAYS_REQUIRE_AUTHORIZATION && !$this->getUser() && !in_array(Config::$uri, Config::$no_authorization_uris)) {
			return $this->forward(Config::NO_AUTHORIZATION_CONTROLLER)->preDispatch();
		}

		return $this;
	}

	/**
	 * Run once post controller method for actions that will respond to the client
	 *
	 * @return ControllerSite
	 */
	protected function postDispatch() {
		// preprocess all responses here
		$this->processed_response = $this->processResponse();
		// then close all external connections before responding
		return $this;
	}

	/**
	 * Sets the internal response parameters to be sent to the client
	 *
	 * @param mixed $response
	 * @param int $type
	 * @return ControllerSite
	 */
	protected function setResponse($response, $type = self::RESPONSE_AUTO) {
		$this->response = $response;
		if($type === self::RESPONSE_AUTO) {
			if($response === null) {
				$type = self::RESPONSE_TEXT;
				$this->response = '';
			} elseif(is_scalar($response)) {
				// scalar values (numbers, strings or bools) are easily responded with
				if(strpos($response, 'http') === 0) {
					// if it starts with http, we're assuming it's a url
					$type = self::RESPONSE_REDIR;
				} else {
					// otherwise it's just plain text
					$type = self::RESPONSE_TEXT;
				}
			} elseif(is_a($response, 'Template')) {
				// if this is a template class, we're going to assume it's a ControllerTemplate
				$type = self::RESPONSE_PAGE;
			} else {
				// we're going to assume this is a json response otherwise
				$type = self::RESPONSE_JSON;
			}
		}
		$this->response_type = $type;
		return $this;
	}

	/**
	 * Run once post controller method for actions that will respond to the client
	 * This function might exit so it is recommended that this be the last function to execute
	 *
	 * @return ControllerSite
	 */
	protected function respond() {
		switch($this->response_type) {
			case self::RESPONSE_PAGE:
				// always try to make ie use the latest rendering engine
			case self::RESPONSE_TEXT:
				Helper_Request::respond($this->processed_response, Helper_Request::RESPONSE_PRINT, Config::$platform);
				break;
			case self::RESPONSE_JSON:
				Helper_Request::respond($this->processed_response, Helper_Request::RESPONSE_JSON, Config::$platform);
				break;
			case self::RESPONSE_REDIR:
				Helper_Request::respond($this->processed_response, Helper_Request::RESPONSE_REDIR, Config::$platform);
				break;
			case self::RESPONSE_CONTENT:
				// TODO: we'll need to figure the out, but don't need to worry about it for now
				break;
		}
		return $this;
	}

	/**
	 * Method to check if the response is a text string
	 *
	 * @return bool
	 */
	protected function isText() {
		return $this->response_type === self::RESPONSE_TEXT;
	}

	/**
	 * Method to check if the response is a html page
	 *
	 * @return bool
	 */
	protected function isPage() {
		return $this->response_type === self::RESPONSE_PAGE;
	}

	/**
	 * Method to check if the response is a json object
	 *
	 * @return bool
	 */
	protected function isJson() {
		return $this->response_type === self::RESPONSE_JSON;
	}

	/**
	 * Method to check if the response is a redirect
	 *
	 * @return bool
	 */
	protected function isRedirect() {
		return $this->response_type === self::RESPONSE_REDIR;
	}

	/**
	 * Method to check if the response is content output
	 *
	 * @return bool
	 */
	protected function isContent() {
		return $this->response_type === self::RESPONSE_CONTENT;
	}

	/**
	 * Retrieves the template object for handling page responses
	 *
	 * @return ControllerTemplate
	 */
	protected function getTemplate() {
		if($this->template === null) {
			$this->template = Template::getInstance();
		}
		return $this->template;
	}

	/**
	 * Response string preprocessor that handles modifying the response so it can be sent to the client
	 *
	 * @return string
	 */
	final protected function processResponse() {
		switch($this->response_type) {
			case self::RESPONSE_TEXT:
				return $this->processResponseText($this->response);
			case self::RESPONSE_PAGE:
				return $this->processResponsePage($this->response);
			case self::RESPONSE_JSON:
				return $this->processResponseJson($this->response);
			case self::RESPONSE_REDIR:
				return $this->processResponseRedirect($this->response);
			case self::RESPONSE_CONTENT:
				return $this->processResponseContent($this->response);
		}
		return $this->response;
	}

	/**
	 * Response pre processor for text responses
	 *
	 * @param string $response
	 * @return string
	 */
	protected function processResponseText($response) {
		return $response;
	}

	/**
	 * Response pre processor for page responses
	 *
	 * @param Template $response
	 * @return string
	 */
	protected function processResponsePage(Template $response) {
		$this->setTemplateRenderer($response);
		$this->path->getPathValues();
		return $response->displayPage();
	}

	/**
	 * Response pre processor for json responses
	 *
	 * @param mixed $response
	 * @return mixed
	 */
	protected function processResponseJson($response) {
		return $response;
	}

	/**
	 * Response pre processor for url redirect responses
	 *
	 * @param string $response
	 * @return string
	 */
	protected function processResponseRedirect($response) {
		return $response;
	}

	/**
	 * Response pre processor for content responses
	 *
	 * @param string $response
	 * @return string
	 */
	protected function processResponseContent($response) {
		return $response;
	}

	/**
	 * The default action to be taken if the request action doesn't exist
	 *
	 * @return ControllerSite
	 */
	protected function defaultAction($response) {

		return $this;
	}

	/**
	 * Gets the currently logged in user
	 *
	 * @return AyiUser
	 */
	protected function getUser() {
		if(is_null($this->dispatcher)) {
			$this->dispatcher = Dispatcher::getInstance();
		}
		return $this->dispatcher->getUser();
	}

	/**
	 * Sets all the internal templates along with the proper files associated with them
	 *
	 * @param Template $template
	 */
	protected function setTemplateRenderer($template) {
		$controller_action = $this->getControllerName();
		// set the page response templates
		$this->title = $template->title;

		// append the site name to the title
		if(strpos($this->title, CONFIG::NAME) === false) {
			if($this->title) {
				$this->title = $this->title;
			}
			$template->title = CONFIG::NAME.' / '.$this->title;
		} else {
			$template->title = $this->title;
		}

		if(!isset($template->header)) {
			$template->header = array('header');
		}
		$template->meta_keywords = $this->meta->getKeywords();
		$template->meta_description = $this->meta->getDescription();

		$this->meta->setPage($this->toString());
		$template->addFlag('App', array(
			'lab_files' => $this->static_content->parseFiles(Config::$js_group),
			'authenticated' => $this->getUser() !== null
		));
		$template->lab_javascript_tag = $this->static_content->getJSLoaderContentTag();
		$template->css_tag = $this->static_content->addCSS($this->css_modules)->getContentTag('css');

		$template->copyright_year = date('Y');
		$agent = $_SERVER['HTTP_USER_AGENT'];

	    $os = 'Mac';
		if(preg_match('/Linux/',$agent)) {
		   $os = 'Linux';
		} elseif(preg_match('/Win/',$agent)) {
		   $os = 'Windows';
		} elseif(preg_match('/Mac/',$agent)) {
		  $os = 'Mac';
		}
		$browser = Helper_Browser::getBrowser();
		$template->body_css = implode(' ', array_merge(array(
			Config::getDeviceName(),
			$browser['browser'],
			$browser['browser'] . $browser['version'],
			$os,
			$this->determineTemplateName(),
		), $this->css_classes));
	}

	/**
	 * Add a javascript module to the page
	 *
	 * @param string $name
	 * @return ControllerSite
	 */
	protected function addJsModule($name) {
		$this->js_modules[] = $name;
		return $this;
	}

	/**
	 * Add a css module to the page
	 *
	 * @param string $name
	 * @return ControllerSite
	 */
	protected function addCssModule($name) {
		$this->css_modules[] = $name;
		return $this;
	}

	/**
	 * Add a css class to the page body
	 *
	 * @param string $class
	 * @return ControllerSite
	 */
	protected function addBodyClass($class) {
		$this->css_classes[] = $class;
		return $this;
	}
}