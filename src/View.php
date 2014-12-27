<?php
namespace Onionimbus\System;

class View
{
    private $default_file = ''; //
    private $template = null; // Template engine (for now, Twig)

    /**
     * Instantiate a View object
     *
     * @param string $default_template - default template to render (controller-specific)
     * @param Twig_Environment $engine - Which rendering engine should we employ?
     */
    public function __construct(
        $default_template = '',
        \Twig_Environment $engine = null
    ) {
        if (isset($default_template)) {
            $this->default_file = $default_template;
        }
        $this->engine = $engine;
    }

    /**
     * Render a template using whichever template engine was selected
     * @param $template - specify which template to render
     * @param $params - which parameters to pass to the template
     */
    public function render($template = null, $params = [])
    {
        if (\is_array($template)) {
            // We're going to use $default_file anyway, we only passed params.
            // Sigh, okay.
            $params = $template;
            $template = null;
        }
        return $this->template->render(
            empty($template) ? $this->default_file : $template,
            $params
        );
    }
}
