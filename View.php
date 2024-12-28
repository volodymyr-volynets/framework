<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * View
 */
class View
{
    /**
     * View constructor
     *
     * @param object $controller
     * @param string $file
     * @return object
     */
    public function __construct(& $controller, string $file, string $type = 'html', ?string $controller_html = null)
    {
        // get values from controller
        foreach ((array) $controller->data as $k => $v) {
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
                ob_start();
                require($file);
                $html = I18n::htmlReplaceTags(ob_get_clean());
                if (isset($controller_html)) {
                    $html = str_replace('<!-- [numbers: controller content] -->', $controller_html, $html);
                }
                echo Request::htmlReplaceTags($html);
        }
        // set values back into controller
        $vars = get_object_vars($this);
        foreach ($vars as $k => $v) {
            $controller->data->{$k} = $v;
        }
    }
}
