<?php
namespace Onionimbus\System;

class Controller extends Common
{
    public $view;

    public function __construct($db = null, $engine = null) {
        parent::__construct($db);
        $fqn = explode('\\', \get_class($this));
        do {
            $name = \array_pop($fqn);
        } while (!empty($fqn) && empty($name));

        $this->view = new \Onionimbus\System\View(
            $name,
            $engine
        );
    }
}
