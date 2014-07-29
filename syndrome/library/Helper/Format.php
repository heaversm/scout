<?php
class Helper_Format {
	/**
	 * Returns a cardinal format of $number
	 * @param int $number
	 * @return string
	 */
	public static function formatCardinal($number) {
		$small = 0;
	 	$last_char_number = substr("$number", -1);
		switch($last_char_number) {
			case '1' :
				$th = 'st';
			break;
			case '2' :
				$th = 'nd';
				break;
			case '3' :
				$th = 'rd';
				break;
			default :
				$th = 'th';
			break;
		}
		
		if ($number > 10 && $number < 20) {
			$th = 'th';	
		}
		
		if ($small == 0) {
			$cardinal = $number.$th;	
		}
		return $cardinal;
	}
	

	/**
	 * Outputs a json string in a pretty format
	 *
	 * @param mixed $object
	 * @return string
	 */
	public static function printJson($object) {
		$tokens = preg_split('/([\{\}\]\[,])/', json_encode($object), -1, PREG_SPLIT_DELIM_CAPTURE);
		$result = '';
		$indent = 0;
		foreach($tokens as $token) {
			if($token == '') {
				continue;
			}
			$padding = str_repeat('    ', $indent);
			if($token == '{' || $token == '[') {
				$indent++;
				if($result != '' && $result[strlen($result) - 1] == "\n") {
					$result .= $padding;
				}
				$result .= $token . "\n";
			} else if($token == '}' || $token == ']') {
				$indent--;
				$padding = str_repeat('    ', $indent);
				$result .= "\n" . $padding . $token;
			} else if($token == ',') {
				$result .= $token . "\n";
			} else {
				$result .= $padding . $token;
			}
		}
		$result = preg_replace('/\{\s+\}/m', '{}', $result);
		$result = preg_replace('/\[\s+\]/m', '[]', $result);
		$result = preg_replace('/":/', '" : ', $result);
		return $result;
	}
	
	/**
	 * Returns a human legible string of $size starting from bytes
	 * @param int $size
	 * @return string
	 */
	public static function formatFilesize($size) {
		$size = max(0, $size);
		$u = array('&nbsp;b', 'kb', 'mb', 'gb');
		$i = 0;
			while ($size >= 1024 && $i < 4) {
				$size /= 1024;
				$i++;
			}
	 	return number_format($size, 1).$u[$i];
	}
	
	/**
	 * Returns a pluralized text of a value
	 * @param int $value
	 * @param string $single
	 * @param string $plural
	 * @return string
	 */
	public static function formatPlural($value, $single, $plural) {
		return ($value != 1) ? $value.' '.$plural : $single ;
	}

	/**
	 * Formats $seconds into MM:SS
	 * @param int $seconds
	 * @return string
	 */
	function formatDuration($seconds) {
		return number_format(floor($seconds/60).'.'.$seconds % 60, 2, ':', '');
	}
	
	/**
	 * Formats a telephone NNNNNNNNNN to (NNN) NNN-NNNN
	 * @param int $telephone
	 * @return string
	 */
	function formatTelephone($telephone) {
		if(strpos($telephone, '+') !== false) {
			return $telephone;
		} else return '('.substr($telephone, 0, 3).') '.substr($telephone, 3, 3).'-'.substr($telephone, 6);
	}
	
	/**
	 * Returns a human legible string of an Unix timestamp
	 * @param int $timestamp
	 * @param int $granularity
	 * @return string
	 */
	function formatInterval($timestamp, $granularity = 2) {
		$timestamp = time() - $timestamp;
		$units = array('year|years' => 31536000, 'week|weeks' => 604800, 'day|days' => 86400, 'hour|hours' => 3600, 'minute|minutes' => 60, 'second|seconds' => 1);

		foreach ($units as $key => $value) {
			$key = explode('|', $key);
			if ($timestamp >= $value) {
				$output .= ($output ? ' ' : '') . self::formatPlural(floor($timestamp / $value), $key[0], $key[1]);
				$timestamp %= $value;
				$granularity--;
			}

			if ($granularity == 0) break;
		}
		
		return $output ? $output : '0 sec' ;
	}
	
	/**
	 * Returns $text as URL slug
	 * @param string $text
	 * @return string
	 */
	public static function slug($text) {
		return str_replace('_', '-', strtolower(preg_replace("[^a-z0-9A-Z\_\-]", "", $text)));
	}
  
	function formatPossessive($name) {
		return ($name[strlen($name) - 1] === 's') ? $name."'" : $name."'s";
	}
}