<?php

interface object_name_interface {
	public static function explain($type = null, $options = []);
	public static function check($type, $name);
}