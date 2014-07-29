<?php
/**
 * @author Man Hoang
 * @name Helper_Date
 */
class Helper_Date {

	/**
	 * Returns whether $month/$day/$year is valid
	 * @param int $month
	 * @param int $day
	 * @param int $year
	 * @return bool
	 */
	public static function validateDate($month, $day, $year) {
		$timestamp = strtotime($month.'/'.$day.'/'.$year);
		if(!checkdate($month, $day, $year)) {
			return false;	
		}

		$date = getdate($timestamp);
		return mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']) > 0;
	}
}