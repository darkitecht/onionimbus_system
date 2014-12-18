<?php
namespace Onionimbus\System;

class Controller
{
    public $view;
    public $db;

    public function __construct($db = null, $engine = null) {
        $fqn = explode('\\', \get_class($this));
        $this->db = $db;
        do {
            $name = \array_pop($fqn);
        } while (!empty($fqn) && empty($name));

        $this->view = new \Onionimbus\System\View(
            $name,
            $engine
        );
    }
}
