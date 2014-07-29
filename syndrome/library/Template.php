<?php
class Template {
	private $template;
	private $memory;
	private $store = array();
	private $flags;
	private static $instance;
	private $parsed_template = '';
	private $parsed_store = false;
	private $response_type = Helper_Request::RESPONSE_PRINT;

	const XML_DOCTYPE = '<?xml version="1.0" encoding="utf-8"?>';

	private static $template_keys = array(
		'start',
		'header',
		'footer',
		'pre_full',
		'full',
		'left',
		'center',
		'right',
		'whole',
		'xml'
	);

	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function create() {
		return new self();
	}

	public function __construct() {
		$this->memory = Memory::getInstance();
		$this->flags = Config::getAppData();

		return $this;
	}

	public function addFlag($key, $value) {
		if(isset($this->flags[$key])) {
			$this->flags[$key] = array_merge($this->flags[$key], $value);
		} else {
			$this->flags[$key] = $value;
		}
	}

	private function isTemplateKey($key) {
		return in_array($key, self::$template_keys);
	}

	public function __set($key, $value) {
		if($this->isTemplateKey($key)) {
			$this->template->$key = $value;
		} else {
			$this->memory->$key = $value;
		}
	}

	public function __get($key) {
		if(isset($this->template->$key) && $this->isTemplateKey($key)) {
			return $this->template->$key;
		} else if(isset($this->memory->$key)) {
			return $this->memory->$key;
		}
	}

	public function __isset($key) {
		return $this->isTemplateKey($key) ? isset($this->template->$key) : isset($this->memory->$key) ;
	}

	public function displayPage() {
		if(isset($this->template->xml)) {
			header('Content-type: text/xml');
			print self::XML_DOCTYPE;
			return $this->render($this->template->xml)
				->parse()
				->displayRender();
		}
		if(isset($this->template->whole)) {
			return $this->render($this->template->whole)
				->parse()
				->displayRender();
		}

		$start = (isset($this->template->start)) ? $this->template->start : 'start';
		$header = (isset($this->template->header)) ? $this->template->header : 'header';
		$footer = (isset($this->template->footer)) ? $this->template->footer : 'footer';

		$body = array();

		if(isset($this->template->pre_full)) {
			$body['column-full'] = $this->template->pre_full;
		}

		if(isset($this->template->left)) {
			$body['column-left'] = $this->template->left;
		}

		if(isset($this->template->center)) {
			$body['column-center'] = $this->template->center;
		}
		if(isset($this->template->right)) {
			$body['column-right'] = $this->template->right;
		}

		if(isset($this->template->full)) {
			$body['column-full'] = $this->template->full;
		}

		$this->render(array(
				$start,
				'container' => array(
					array(
						'header' => $header
					),
					array(
						'body' => $body
					),
					array(
						'footer' => $footer
					),
				),
				'google-analytics',
				'end'
			))
			->parse()
			->displayRender();
	}

	private function parseStore() {
		foreach($this->memory->getStore() as $key => $value) {
			if($value instanceof FormModel) {
				$xml = $value->getFormXml();
				foreach($xml as $xml_key => $xml_value) {
					if(is_array($xml_value)) {
						foreach($xml_value as $sub_xml_key => $sub_xml_value) {
							$this->store[$key.'.'.$xml_key.'.'.$sub_xml_key] = $sub_xml_value;
						}
					} else {
						$this->store[$key.'.'.$xml_key] = $xml_value;
					}
				}
			} else if(is_array($value)) {
				foreach($value as $sub_key => $sub_value) {
					$this->store[$key.'.'.$sub_key] = $sub_value;
				}
			} else {
				$this->store[$key] = $value;
			}
		}

		$this->store['config_flags'] = json_encode($this->flags);
	}

	public function parse() {
		$this->parseStore();

		foreach($this->store as $key => $value) {
			$this->parsed_template = str_replace('${'.$key.'}', $value, $this->parsed_template);
		}

		return $this;
	}

	public function singularParse($store) {
		foreach($store as $key => $value) {
			$this->parsed_template = str_replace('${'.$key.'}', $value, $this->parsed_template);
		}

		return $this;
	}

	public function iteratorParse($store) {
		if(empty($store)) {
			$this->parsed_template = '';
			return $this;
		}

		$partials = array();
		foreach($store as $key => $value) {
			$partial_template = $this->parsed_template;
			if(is_array($value)) {
				foreach($value as $sub_key => $sub_value) {
					$partial_template = str_replace('${'.$sub_key.'}', $sub_value, $partial_template);
				}
			} else {
				$partial_template = str_replace('${'.$key.'}', $value, $partial_template);
			}
			$partials []= $partial_template;
		}
		$this->parsed_template = implode("\n", $partials);
		return $this;
	}

	public function getRender() {
		return $this->parsed_template;
	}

	public function displayRender() {
		Helper_Request::respond($this->parsed_template, $this->response_type);
	}

	private function parseTemplate($template) {
		$this->parsed_template .= $template;
		return $template;
	}

	public function render($templates) {
		return $this->startRender()->renderTemplate($templates)->endRender();
	}

	public function startRender() {
		$this->parsed_template = '';
		ob_start(array($this, 'parseTemplate'));
		return $this;
	}

	public function endRender() {
	    ob_end_clean();
		return $this;
	}

	public function renderTemplate($templates) {
		if(is_array($templates) and count($templates) > 0) {
			foreach($templates as $template => $sub_templates) {
				if(is_array($sub_templates)) {
					include($this->isTemplate($template.'-start.html'));
					foreach($sub_templates as $tpl => $sub_tpl) {
						if(!is_array($tpl) && is_string($tpl) && $sub_tpl !== false) {
							include($this->isTemplate($tpl.'-start.html'));
							$this->renderTemplate($sub_tpl);
							include($this->isTemplate($tpl.'-end.html'));
						} else if($sub_tpl !== false) {
							$this->renderTemplate($sub_tpl);
						}
					}
					include($this->isTemplate($template.'-end.html'));
				} else if($sub_templates) {
					include($this->isTemplate($sub_templates.'.html'));
				}
			}
		} else if($templates !== '') {
			include($this->isTemplate($templates.'.html'));
		}
		return $this;
	}

	private function isTemplate($template) {
		if(file_exists(Config::$views.$template)) {
			return Config::$views.$template;
		} else if(file_exists(SyndromeConfig::$syndrome_views.$template)) {
			return SyndromeConfig::$syndrome_views.$template;
		} else {
			throw new Exception('Loading a non-existent template: '.$template);
		}

	}
}