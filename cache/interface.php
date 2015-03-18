<?php

interface cache_interface {
	public static function get($cache_id, $id);
	public static function set($cache_id, $data, $expire, $tags, $id);
	public static function gc($mode, $tags, $id);
}