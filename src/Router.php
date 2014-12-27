<?php
namespace Onionimbus\System;

class Router
{
    private $routes = [];
    private $config = [];
    
    // Dependencies to inject:
    private $inject = [
        'database' => null,
        'template' => null
    ];

    public function __construct($routes = [])
    {
        foreach ($routes as $i => $r) {
            
            if (\strpos($i, ':') === false) {
                // No colon in the route index indicates no explicit port...
                
                if (\preg_match(
                    '#^'.\str_replace(
                        ['.','*'],
                        ['\\.', '.+?'],
                        $i
                    ),
                    $_SERVER['HTTP_HOST']
                )) {
                    // Are we on the correct host? (We're ignoring port.)
                    
                   // Add the route data to our current app state:
                    $this->addRoutes($r, $i);
                }
            } elseif (\preg_match(
                '#^'.\str_replace(
                    ['.','*'],
                    ['\\.', '.+?'],
                    $i
                ),
                $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT']
            )) {
                // If we have an explicit port, we check both hostname and port
                
                // Add the route data to our current app state:
                $this->addRoutes($r, $i);
            }
        }
    }
    
    /**
     * Inject dependencies here
     */
    public function inject($key, $val)
    {
        $this->inject[$key] = $val;
    }

    /**
     * Parse a route, add it to the index
     *
     * @param array $entry - hostname entry
     */
    public function addRoutes($entry = [])
    {
        $this->config['lazy'] = !empty($entry['lazy']);
        if (empty($entry['namespace'])) {
            $this->config['namespace'] = '\\';
        } else {
            $this->config['namespace'] = $entry['namespace'];
        }
        if (!empty($entry['routes'])) {
            foreach ($entry['routes'] as $path => $params) {
                $this->addRoute($path, $params);
            }
        }
    }

    public function addRoute($path = '', $params = [])
    {
        $path = str_replace([
                '*',
                '{param}',
                '{alphanum}',
                '{alpha}',
                '{number}'
            ], [
                '([^/]+)',
                '([\x20-\x2e\x30-\x7e]+)',
                '([a-zA-Z0-9]+)',
                '([a-zA-Z]+)',
                '((?:\+|\-)?[0-9]+|[0-9]+\.[0-9]+)'
            ], $path);
        if (\is_string($params)) {
            $route = [$params, 'index'];
        } elseif (\is_array($params)) {
            switch (\count($params)) {
                case 1:
                    $route = [$params, 'index'];
                    break;
                case 2:
                    $route = [$params[0], $params[1]];
                    break;
                default:
                    $route = [$params[0], $params[1]];
                    \user_error("More than two parameters defined", E_USER_NOTICE);
            }
        }
        $this->routes[$path] = $route;
    }

    /**
     * This calls the proper controller, etc.
     *
     * @param string $path
     */
    public function serve($path = '')
    {
        empty($path) AND $path = $_SERVER['REQUEST_URI'];

        foreach ($this->routes as $route => $dispatch) {
            if (\preg_match(
                '#^' . \str_replace('#', '', $route) . '/?$',
                $path,
                $matches
            )) {
                $controller = $this->getControllerName($dispatch);

                // Now let's figure out the method
                $method = !empty($dispatch[1])
                    ? $dispatch[1]
                    : 'index';

                // Let's dispatch it!
                if (\method_exists($controller, $method)) {
                    $ref = new \ReflectionMethod($controller, $method);
                    if ($ref->isPublic()) {
                        return $controller->$method($matches);
                    }
                    unset($ref);
                }
            }
        }
        if (!empty($this->config['lazy'])) {
            return $this->lazy($path);
        }
    }

    public function getController($dispatch = [])
    {
        $controller = $this->getControllerName($dispatch);
        return new $controller(
            $this->inject['database'],
            $this->inject['template']
        );
    }

    public function getControllerName($dispatch = [])
    {
        $ctrl = !empty($dispatch[0])
            ? $dispatch[0]
            : 'Index';
        // Let's figure out the controller!
        if (
            !empty($this->config['namespace'])
            && strpos($ctrl, '\\') === false
            && $this->config['namespace'] !== '\\'
        ) {
            // Use the namespace of the applications' contollers
            $ctrl = $this->config['namespace'] . '\\' . $ctrl;
            return $ctrl;
        } else {
            // Manual namespace provided
            return $ctrl;
        }
    }

    /**
     * Lazy loading
     */
    public function lazy($path = '/')
    {
        $params = [];
        if (\preg_match('#^/([^/]+)/([^/]+)/(.*)$#', $path, $m)) {
            $ctrl = $this->getControllerName([$m[1]]);
            $method = $m[2];
            $num = \count($m);
            $params = explode('/', $m[3]);
        } elseif(\preg_match('#^/([^/]+)/(.*)$#', $path, $m)) {
            $ctrl = $this->getControllerName([$m[1]]);
            $method = $m[2];
        } elseif(\preg_match('#^/(.+?)$#', $path, $m)) {
            $ctrl = $this->getControllerName([$m[1]]);
            $method = 'index';
        } else {
            $ctrl = $this->getControllerName();
            $method = 'index';
        }
        if (\class_exists($ctrl)) {
            $controller = new $ctrl(
                $this->inject['database'],
                $this->inject['template']
            );
            if (\method_exists($ctrl, $method)) {
                $ref = new \ReflectionMethod($ctrl, $method);
                if ($ref->isPublic()) {
                    return $controller->$method($params);
                }
            }
        }
        header("HTTP/1.1 404 Not Found");
        exit;
    }
}
