<?php
class StaticContent {
	private static $instance;
	private $javascript;
	private $css;

	private $lab_js = 'LAB.min.js';
	private $lab_loader = 'LAB.loader.js';
	private $css_group = array();

	private function __construct() {
	}

	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function addJS(array $js_files = array()) {
		$this->js_group = array_merge(Config::$js_group, $js_files);
		return $this;
	}

	public function addCSS(array $css_files = array()) {
		$this->css_group = $css_files;
		return $this;
	}

	public function getJSLoaderContentTag() {
  		return '<script type="text/javascript" src="'.Config::$js_url.$this->lab_js.'"></script>'."\n";
	}

	public function getContentTag($type) {
		$tag = '';
		if($type == 'css') {
			$tag .= '<link rel="stylesheet" href="'.Config::$css_url.'serve?'.time().'" type="text/css" media="all" />'."\n";
		} else if($type == 'javascript') {
			$files_for_js = array();
			foreach($this->js_group as $file) {
				if(strpos($file, '//') === 0) {
	  				$tag .= '<script type="text/javascript" src="https:'.$file.'"></script>'."\n";
				} else {
					$files_for_js[] = $file;
				}
			}
			$tag .= '<link rel="stylesheet" href="'.Config::$js_url.'serve?f='.implode(',', $files_for_js).'&v='.Config::APP_VERSION.'" type="text/css" media="all" />'."\n";
		}

		return $tag;
	}

	public function parseFiles($files) {
		$file_urls = array();
		$files_for_js = array();
		foreach($files as $file) {
			if(strpos($file, '//') === 0) {
  				$file_urls[] = 'https:'.$file;
			} else {
				$files_for_js[] = $file;
			}
		}
		$file_urls[] = Config::$js_url.'serve';

		return $file_urls;
	}
}