<?php

interface object_acl_interface {
	public function acl($acl_key, $acl_type, & $data = [], & $options = []);
}