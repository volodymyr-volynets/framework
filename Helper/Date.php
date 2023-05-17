<?php

namespace Helper;
class Date {

	/**
	 * Compare two dates
	 *
	 * @param string $date1
	 * @param string $date2
	 * @param string $type
	 * @return boolean
	 */
	public static function compare($date1, $date2, $type = 'date') {
		if ($type == 'date') {
			return date('Y-m-d', strtotime($date1)) === date('Y-m-d', strtotime($date2));
		} else if ($type == 'datetime' || $type == 'time') {
			return date('Y-m-d H:i:s', strtotime($date1)) === date('Y-m-d H:i:s', strtotime($date2));
		} else {
			return strtotime($date1) == strtotime($date2);
		}
	}

	/**
	 * Is
	 *
	 * @param string $date1
	 * @param string $date2
	 * @return int
	 */
	public static function is($date1, $date2) : int {
		return strtotime($date1 ?? '1970-01-01 00:00:00') <=> strtotime($date2 ?? '1970-01-01 00:00:00');
	}

	/**
	 * Compute difference between two dates
	 *
	 * @param string $date1
	 * @param string $date2
	 * @param string $type
	 *	days
	 *	abs days
	 *	seconds
	 *	abs seconds
	 * @param boolean $round
	 * @return mixed
	 */
	public static function diff($date1, $date2, $type = 'days', $round = true) {
		$result = strtotime($date2) - strtotime($date1);
		switch ($type) {
			case 'days':
				$result = $result / 86400;
				break;
			case 'abs days':
				$result = abs($result / 86400);
				break;
			case 'seconds':
				return $result;
				break;
			case 'abs seconds':
				return abs($result);
				break;
		}
		// rounding
		if ($round) {
			$result = (int) round($result);
		}
		return $result;
	}

	/**
	 * Extract minutes from datetime object
	 *
	 * @param \DateTime $datetime
	 * @return int
	 */
	public static function extractMinutes(\DateTime $datetime) : int {
		return (((int) $datetime->format('H')) * 60) + ((int) $datetime->format('i'));
	}

	/**
	 * Between
	 *
	 * @param string $date
	 * @param string $date1
	 * @param string $date2
	 * @return bool
	 */
	public static function between($date, $date1, $date2) : bool {
		$date = strtotime($date);
		return ($date >= strtotime($date1) && $date <= strtotime($date2));
	}

	/**
	 * Add interval
	 *
	 * @param string $date
	 * @param string $addition
	 * @param string $format
	 * @return string
	 */
	public static function addInterval($date, $addition, $format = 'Y-m-d H:i:s') {
		return date($format, strtotime($date . ' ' . $addition));
	}

	/**
	 * Generate intervals
	 *
	 * @param string $date1
	 * @param string $date2
	 * @param string $interval
	 * @param string $format
	 * @return array
	 */
	public static function generateIntervals($date1, $date2, $interval, $format = 'Y-m-d H:i:s') : array {
		$result = [];
		do {
			$result[] = date($format, strtotime($date1));
			$date1 = \Helper\Date::addInterval($date1, $interval);
		} while(\Helper\Date::is($date1, $date2) <= 0);
		return $result;
	}

	/**
	 * Swap two dates
	 *
	 * @param string $date1
	 * @param string $date2
	 */
	public static function swap(& $date1, & $date2) {
		if (self::is($date1, $date2) == 1) {
			$temp = $date2;
			$date2 = $date1;
			$date1 = $temp;
		}
	}

	/**
	 * To date
	 *
	 * @param string $date
	 * @return string
	 */
	public static function toDate(string $date) : string {
		return date('Y-m-d', strtotime($date));
	}
}