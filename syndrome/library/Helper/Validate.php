<?php
class Helper_Validate {
	/**
	 * Validates a credit card according to Luhn's algorithm
	 * @param int $card_number
	 * @param int $card_ccv
	 * @param int $card_provider
	 * @return bool
	 */
	public static function validateCreditCard($card_number, $card_ccv, $card_provider) {
		$first_number = substr($card_number, 0, 1);
		
		switch ($first_number) { 
			case 3: 
				if (!preg_match('/^3\d{3}[ \-]?\d{6}[ \-]?\d{5}$/', $card_number) || $card_provider !== 'American Express') {
					return false;
				}
			break;
			case 4: 
				if (!preg_match('/^4\d{3}[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}$/', $card_number) || $card_provider !== 'Visa') {
					return false;
				}
			break;
			case 5: 
				if (!preg_match('/^5\d{3}[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}$/', $card_number) || $card_provider !== 'MasterCard') {
					return false;
				}
			break;
			case 6: 
				if (!preg_match('/^6011[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}$/', $card_number) || $card_provider !== 'Discover Card'){
					return false;
				}
			default:
				return false;
			break;
		}
		
		
		$checksum = 0;
		$j = 1;

		for ($i = strlen($card_number) - 1; $i >= 0; $i--) {
			$calc = substr($card_number, $i, 1) * $j;

			if ($calc > 9) {
				$checksum = $checksum + 1;
				$calc = $calc - 10;
			}

			$checksum += $calc;
			$j = ($j == 1) ? 2 : 1 ;
		}

		if ($checksum % 10 != 0) {
			return false;
		}
		
		if ($first_number == 3) { 
			if (!preg_match("/^\d{4}$/", $card_ccv)) {
				return false;
			}
		} else { 
			if (!preg_match("/^\d{3}$/", $card_ccv)) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Returns whether $email_address is valid
	 * @param string $email_address
	 */
	public static function validateEmail($email_address) {
		$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
		return strstr($email_address, '@') && strstr($email_address, '.') && preg_match($chars, $email_address);
	}
	
	/**
	 * Returns whether $url is valid
	 * @param string $url
	 */
	public static function validateUrl($url) {
		return preg_match("/^http(s?):\/\/[a-z0-9|\.\_|-]+\.+[a-z]{2,4}/i", $url);
	}
}