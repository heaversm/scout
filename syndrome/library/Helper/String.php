<?php
class Helper_String {
	/**
	 * Returns text between html tags
	 * @param string $text
	 * @param string $tagname
	 * @return string
	 */
	public static function substr_between_tag($text, $tagname) {
		$pattern = "/<$tagname>(.*?)<\/$tagname>/";
		preg_match($pattern, $text, $matches);
		return $matches[1];
	}
	
	/**
	 * Returns text between $left and $right
	 * @param string $text
	 * @param string $left
	 * @param string $right
	 * @param string $offset
	 * @return string
	 */
	public static function substr_between($text, $left, $right, $offset = 0) {
		if (strpos($text, $left) === false or strpos($text, $right) === false) {
			return false;
		} else {
			$left_position = strpos($text, $left, $offset) + strlen($left);
			$right_position = strpos($text, $right, $left_position);
			return substr($text, $left_position, $right_position - $left_position);
		}
	}
}