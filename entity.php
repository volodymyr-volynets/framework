<?php

class entity {

	/**
	 * Get logged in user
	 */
	public static function id() {
		return $_SESSION['numbers']['entity']['em_entity_id'] ?? null;
	}
}
