<?php

namespace Object\Activation;
abstract class Base {
	abstract public function activate(array $options = []) : array;
}