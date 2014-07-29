<?php 
class Data_Gender {
	private static $gender = array(
		1 => 'Male',
		2 => 'Female',
	);
	
	private static $interested_in = array(
		1 => 'Male',
		2 => 'Female',
		3 => 'Both',
	);
	
	public static function getGender() {
		return self::$gender;
	}
	
	public static function getReadableGender($gender) {
		return self::$gender[$gender];
	}
	
	public static function getInterestedIn() {
		return self::$interested_in;
	}
}

