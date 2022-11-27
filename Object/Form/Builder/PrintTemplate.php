<?php

namespace Object\Form\Builder;
abstract class PrintTemplate {
	public $form_link;
	public $module_code;
	public $title;
	public $options = [];

	/**
	 * Build report
	 *
	 * @param object $form
	 * @return object
	 */
	abstract public function buildReport(& $form) : \Object\Form\Builder\Report;

	/**
	 * Render
	 *
	 * @param string $format
	 * @return mixed
	 */
	public function render($format, & $report) {
		$content_types_model = new \Object\Form\Model\Report\Types();
		$content_types = $content_types_model->get();
		if (empty($content_types[$format])) $format = 'text/html';
		$model =  new $content_types[$format]['no_report_content_type_model']();
		return $model->render($report);
	}
}