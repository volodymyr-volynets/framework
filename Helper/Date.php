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
	 * Compute difference between two dates
	 *
	 * @param string $date1
	 * @param string $date2
	 * @param string $type
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
}