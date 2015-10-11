<?php

class controller_error {

	public $title = 'Application Error';
	public $acl = array(
		'login' => false,
		'tokens' => array()
	);

	public function action_error() {
		if (isset($this->exception)) {
			echo '<div>';
				echo '<h3>Exception information:</h3>';
				echo '<b>Message: </b><br/>';
				echo $this->exception->getMessage() . '<br/>';
				if (application::get('environment')!='production') {
					echo '<h3>Stack trace:</h3>';
					echo '<pre>';
						echo $this->exception->getTraceAsString();
					echo '</pre>';
					echo '<h3>MVC Parameters:</h3>';
					echo '<pre>';
						echo var_export(application::get('mvc'), true);
					echo '</pre>';
					echo '<h3>MVC Parameters (Initial):</h3>';
					echo '<pre>';
						echo var_export(application::get('mvc_prev'), true);
					echo '</pre>';
					echo '<h3>Request Parameters:</h3>';
					echo '<pre>';
						echo var_export(request::input(), true);
					echo '</pre>';
				}
			echo '</div>';
		}
	}
}