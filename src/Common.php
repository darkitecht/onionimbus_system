<?php
namespace Onionimbus\System;

class Common
{
    public $db; // \Resonantcore\Lib\Db
    
    public function __construct($db = null)
    {
        $this->db = $db;
    }
    
    /**
     * Retrieve POST data, validates the CSRF token, and optionally
     * blends everything through a given function first.
     *
     * @param callable $func - a function to apply to $_POST
     * @return array (or null)
     */
    public function post(callable $func = null)
    {
        if ($this->method !== 'POST' || empty($_POST)) {
            return null;
        }
        if (\Resonantcore\Lib\Security\CSRF::validate_request()) {
            $data = $_POST;
            if (!empty($func)) {
                if (\is_callable($func)) {
                return \Onionimbus\System\Utility::map($data, $func);
                }
            }
            return $data;
        } else {
            return false;
        }
    }

    /**
     * Retrieve GET data, optionally blend everything through a given function first.
     *
     * @param callable $func - a function to apply to $_POST
     * @return array (or null)
     */
    public function get(callable $func = null)
    {
        if (empty($_GET)) {
            return null;
        }
        $data = $_GET;
        if (!empty($func)) {
            if (\is_callable($func)) {
                return \Onionimbus\System\Utility::map($data, $func);
            }
        }
        return $data;
    }
}
