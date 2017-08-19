<?php
namespace salodev;

class Utils {
	static public function Ifnull(&$value, $default = null) {
		return isset($value) ? $value : $default;
	}
	
	static public function Ifempty(&$value, $default = null) {
		return empty($value) ? $default : $value;
	}
	
	/**
	 * Return a filtered array.
	 * @param array $params   Original assoc array
	 * @param array $keysList List of keys to get with their values.
	 * @return array Filtered array
	 */
	static public function FilterParams(array $params, array $keysList) {
		$newArray = [];
		foreach($keysList as $keyName) {
			$newArray[$keyName] = Utils::Ifnull($params[$keyName]);
		}
		return $newArray;
	}
	
	static public function FillParams(array $defaultValues, array $params) {
		return array_merge($defaultValues, $params);
	}
	
	static public function RotateDate(&$date, $glue='-') {
		$date = self::GetRotatedDate($date, $glue);
	}
	static public function GetRotatedDate(&$date, $glue='-') {
		return implode($glue, array_reverse(explode($glue, $date)));
	}
}