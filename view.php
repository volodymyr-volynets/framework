<?php

class view {

	/**
	 * View constructor
	 * 
	 * @param object $controller
	 * @param string $file
	 * @return object
	 */
	public function __construct(& $controller, $file, $type = 'html')
	{
		// get values from controller
		$vars = get_object_vars($controller);
		foreach ($vars as $k=>$v) {
			$this->{$k} = $v;
		}
		// process view file
		switch ($type) {
			case 'twig':
				$loader = new Twig_Loader_Filesystem(dirname($file));
				$twig = new Twig_Environment($loader);
				$template = $twig->loadTemplate(basename($file));
				echo $template->render($vars);
				break;
			case 'html':
			default:
				require($file);
		}
		// set values back into controller
		$vars = get_object_vars($this);
		foreach ($vars as $k=>$v) {
			$controller->{$k} = $v;
		}
		return $controller;
	}

}