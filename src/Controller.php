<?php
namespace Onionimbus\System;

class Controller extends Common
{
    public $view;

    public function __construct($db = null, $engine = null) {
        // \Onionimbus\System\Common::__connstruct()
        parent::__construct($db);
        
        // Let's explode the current class
        $fqn = explode('\\', \get_class($this));
        do {
            // Get the class name
            $name = \array_pop($fqn);
        } while (!empty($fqn) && empty($name));

        // Let's instantiate a View object (which wraps Twig)
        $this->view = new \Onionimbus\System\View(
            $name,
            $engine
        );
    }
}
