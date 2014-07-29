<?php
include '../../include.php';
header('Content-type: text/javascript;');
foreach(Config::$js_group as $file) {
	if(strpos($file, '//') === false) {
		include $file;
	}
}