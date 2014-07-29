<?php
class Helper_Publish {
	public static function publishLinks($content) {
		$content = ' ' . $content;
		$content = preg_replace_callback('#([\s>])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is', array($this, 'makeUrlClickableCB'), $content);
		$content = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>", $content);
		$content = trim($content);
		return $content;
	}

	public static function makeUrlClickableCB($matches) {
		$content = '';
		$url = $matches[2];
		if ( empty($url) )
			return $matches[0];
		if ( in_array(substr($url, -1), array('.', ',', ';', ':')) === true ) {
			$content = substr($url, -1);
			$url = substr($url, 0, strlen($url)-1);
		 }

		 $url = strtolower($url);
		 return $matches[1] . "<a href=\"$url\" rel=\"nofollow\" target=\"_blank\">".strtolower($url)."</a>" . $content;
	}

	public static function resizeEmbedCode($embed_code, $width, $height) {
		$embed_width = Helper_String::substr_between($embed_code, 'width="', '"');
		$embed_height = Helper_String::substr_between($embed_code, 'height="', '"');

        if ($width == $embed_width) {
            $height = $embed_height;
        }

        if ($embed_code && $width != $embed_width) {
            $ratio = $embed_width/$embed_height;
            $height = $width/$ratio;
        }

		return str_replace(array('width="'.$embed_width.'"', 'height="'.$embed_height.'"'), array('width="'.$width.'"', 'height="'.$height.'"'), $embed_code);
	}
}