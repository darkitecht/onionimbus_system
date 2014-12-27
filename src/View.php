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
        
        // Now let's add our custom filters!
        $this->filter('addslashes', 'addslashes');
        $this->filter('preg_quote', 'preg_quote');
        
        // Add a |cachebust filter; uses HMAC-SHA1 of a file's contents and its
        // modification time so as to break browser caches
        $this->filter('cachebust', function ($relative_path) {
            if ($relative_path[0] !== '/') {
                $relative_path = '/' . $relative_path;
            }
            $absolute = $_SERVER['DOCUMENT_ROOT'] . $relative_path;
            if (\is_readable($absolute)) {
                // It was found. let's cachebust it!
                return $relative_path.'?'.\base64_encode(hash_hmac(
                    'sha1',
                    \file_get_contents($absolute),
                    \filemtime($absolute),
                    true
                ));
            }
            // Not found? Let's add a special tag for it too:
            return $relative_path.'?404NotFound';
        });
        $this->engine->addGlobal('_GET', $_GET);
        $this->engine->addGlobal('_POST', $_POST);
        $this->engine->addGlobal('_SESSION', isset($_SESSION) ? $_SESSION : []);
        $this->engine->addGlobal('_COOKIE', $_COOKIE);
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
    
    /**
     * Add a filter to Twig
     * 
     * @param string $name - Name to access n Twig
     * @param callable $func - function to apply
     */
    public function filter($name, callable $func)
    {
        return $this->engine->addFilter(
            new \Twig_SimpleFilter($name, $func)
        );
    }
}
