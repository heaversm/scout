<?php
class Helper_Clean {
	/**
	 *
	 * regular expression for blocking content
	 * @var string
	 */
	const URL_PATTERN = '{(xxx|cock|nigger|bitch|fukk|fuk|suck|vagina|tits|nude|penis|fuck|cunt|pussy|dollar|million|\$|((https?)://)?[-\w]+(\.\w[-\w]*)+ (?i: [a-z0-9] (?:[-a-z0-9]*[a-z0-9])? \. )+(?-i: com| edu| biz| gov| in(?:t|fo)| mil| net| org| [a-z][a-z]\.[a-z][a-z]| [a-z][a-z]\b))( : \d+ )?( /?([.!,?]+ [^.!,?;Ó\Õ<>()\[\]\{\}\s\x7F-\xFF]+)*)?}ix';
	
	/**
	 * The text used to cover up spam text
	 * @var string
	 */
	const SPAM_REPLACEMENT = '*';
	
	public static function sanitizeFileName( $file_name ) { // Like sanitize_title, but with periods
		$file_name = strtolower( $file_name );
		$file_name = preg_replace('/&.+?;/', '', $file_name); // kill entities
		$file_name = str_replace( '_', '-', $file_name );
		$file_name = preg_replace('/[^a-z0-9\s-.]/', '', $file_name);
		$file_name = preg_replace('/\s+/', '-', $file_name);
		$file_name = preg_replace('|-+|', '-', $file_name);
		$file_name = trim($file_name, '-');
		
		return $file_name;
	}
  
	public static function sanitizeTelephone($telephone) {
		return preg_replace('[\D+]', '', $telephone);
	}
	
	public static function cleanUrl($str, $replace = array(), $delimiter = '-') {
		if( !empty($replace) ) {
			$str = str_replace((array)$replace, ' ', $str);
		}
	
		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
	
		return $clean;
	}
	
	/**
	 * makes text utf8 safe
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	public static function utf8SafeText($input) {
		// handle input of type array
		if(is_array($input)) {
			foreach($input as $key => $val) {
				$input[$key] = self::utf8SafeText($val);
			}
			return $input;
		}

		if(!is_string($input) || $input == '') {
			return $input;
		}

		return @iconv('UTF-8', 'UTF-8//IGNORE', $input);
	}
	
	/**
	 * cleans user generated text for display, but doesn't do
	 * complex phrase removal
	 *
	 * @param string $text
	 * @param bool $clean
	 * @return string
	 */
	public static function websafeText($text, $clean = false){
		$clean_text = htmlspecialchars(strip_tags($text), ENT_NOQUOTES);
		$clean_text = str_replace('&amp;amp;', '&', $clean_text);
		
		if($clean){
			$clean_text = preg_replace(self::URL_PATTERN, self::SPAM_REPLACEMENT, $clean_text);
		}
		return $clean_text;
	}
	
	public static function dbSafeText($input) {
		return self::websafeText(self::utf8SafeText($input), true);
	}
}