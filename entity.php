<?php

class entity {

	/**
	 * Get logged in user id
	 */
	public static function id() {
		return $_SESSION['numbers']['entity']['em_entity_id'] ?? null;
	}

	/**
	 * Get grouped values
	 *
	 * @return array
	 */
	public static function groupped($group) {
		return $_SESSION['numbers']['entity'][$group] ?? [];
	}
}