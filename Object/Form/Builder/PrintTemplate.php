<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Form\Builder;

use Object\Form\Model\Report\Types;

abstract class PrintTemplate
{
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
    abstract public function buildReport(& $form): Report;

    /**
     * Render
     *
     * @param string $format
     * @return mixed
     */
    public function render($format, & $report)
    {
        $content_types_model = new Types();
        $content_types = $content_types_model->get();
        if (empty($content_types[$format])) {
            $format = 'text/html';
        }
        $model =  new $content_types[$format]['no_report_content_type_model']();
        return $model->render($report);
    }
}
