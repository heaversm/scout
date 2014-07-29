<?php
class Data_DateTime {
	private static $months = array(
		1 => 'Jan', 
		2 => 'Feb', 
		3 => 'Mar', 
		4 => 'Apr', 
		5 => 'May', 
		6 => 'Jun', 
		7 => 'Jul', 
		8 => 'Aug', 
		9 => 'Sep', 
		10 => 'Oct', 
		11 => 'Nov', 
		12 => 'Dec'
	);
	
	private static $quaterly_hour = array(
		'00' => '00', 
		'15' => '15', 
		'30' => '30', 
		'45' => '45'
	);
	private static $meridians = array(
		'PM' => 'PM', 
		'AM' => 'AM'
	);

	public static function getMinutes() {
		$minutes = array();
		for($i = 0; $i <= 59; $i++) {
			$j = ($i < 10) ? '0' . $i : $i;
			$minutes[$j] = $j;
		}
		return $minutes;
	}
		
	public static function getDays() {
		$days = array();
		for($i = 1; $i <= 31; $i++) {
			$j = ($i < 10) ? '0'.$i : $i ;
			$days[$i] = $j;
		}
		return $days;
	}

	public static function getNumericMonths() {
		return array_keys(self::$months);
	}

	public static function getMonths() {
		return self::$months;
	}

	public static function getYears($range = 2) {
		$years = array();
		$start_year = date('Y');
		$end_year = date('Y') + $range;
		for($i = $start_year; $i <= $end_year; $i++) {
			$years[$i] = $i;
		}
		return $years;
	}
	
	public static function getBirthdayYears($minimum_age = 18, $maximum_age = 90) {
		$years = array();
		$start_year = date('Y') - $minimum_age;
		$end_year = date('Y') - $maximum_age;
		for($i = $start_year; $i >= $end_year; $i--) {
			$years[$i] = $i;
		}
		return $years;
	}
	
	public static function meridians_set() {
		return self::$meridians;
	}

}