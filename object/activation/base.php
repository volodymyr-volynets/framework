<?php

abstract class object_activation_base {
	abstract public function activate(array $options = []) : array;
}