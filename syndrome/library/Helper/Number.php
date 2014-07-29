<?php
class Helper_Number {
	
	public static function getModulus($number, $modulo = 100, $pad_length = 2) {
		$result = $number % $modulo;
		return $result < 10 ? self::zeroPad($result, $pad_length) : $result ;
	}
	
	public static function zeroPad($number, $pad_length = 2) {
		return str_pad($number, $pad_length, '0', STR_PAD_LEFT);
	}
	
	public static function getModuloId($id, $modulo = 100) {
		return self::getModulus(preg_replace('[\D+]', '', $id), $modulo);
	}
}