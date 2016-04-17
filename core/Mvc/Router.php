<?php

namespace Mvc;

use Http;
use Http\Request;
use Http\Response;

class Router
{

    /**
     * @var \Di
     */
    protected $di;

    /**
     * Routes avaliable
     * @var array<Annotation\Route>
     */
    protected $routeCollection;

    /**
     * Mached route
     * @var array
     */
    protected $match;

    /** @var \Http\Response */
    protected $response;

    /**
     * @var \Http\Request
     */
    protected $request;

    /**
     * @var \Mvc\Resolver
     */
    protected $resolver;

    /**
     * @param \Di $di
     */
    function __construct($di)
    {
        $this->di = $di;
        $this->resolver = $di->set(new Resolver(), null, 'resolver');
        $this->generateRoutes();
    }

    /**
     * @deprecated
     * @return \Http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Generate routes
     */
    public function generateRoutes()
    {
        $routes = $this->di->getTag('route');
        usort($routes, function ($a, $b) {
            return strcmp($a->getPath(), $b->getPath());
        });

        $this->routeCollection = $routes;
    }

    /**
     * find route
     * @param \Http\Request $request
     * @return array
     * @throws \Error404
     */
    public function findRoute(Request $request)
    {
        $this->request = $request;
        $match = $this->match($request->path(), $request->method());
        if (is_array($match))
            return $match;

        throw new \Error404();
    }

    /**
     * match url to route
     * @param string $url
     * @param string $method
     * @return array|boolean
     */
    public function match($url, $method)
    {
        $routes = array_filter($this->routeCollection, function ($route) use ($method) {
            return in_array($method, $route->getMethods());
        });

        foreach ($routes as $route) {
            $params = array();

            $error = false;
            $query = preg_replace_callback('$/?{([^/]+)}$', function ($value) use ($route, &$params, &$error) {
                if (!isset($route->getParameters()[$value[1]])) {
                    $error = true;
                    return;
                }

                $params[$value[1]] = '';
                return $route->getParameters()[$value[1]]['required'] ? "(/([^/]+))" : "(/([^/]+))?";
            }, $route->getPath());

            if ($error) {
                continue;
            }

            $i = 2;
            if (preg_match("@^/" . $query . "$@i", $url, $matches)) {
                foreach ($params as $name => &$value) {
                    $value = (isset($matches[$i]) && !empty($matches[$i])) ? $matches[$i] : $route->getParameters()[$name]['default'];
                    $i += 2;
                }

                $params = $this->resolver->resolveParams($params, $route->getParameters());
                return array('route' => $route, 'params' => $params);
            }
        }

        return false;
    }

    /**
     * Dispatch controller
     * @param ResponseHandler $responseHandler
     * @return \Http\Response
     */
    public function dispatch($responseHandler)
    {
        list($class, $method) = explode('::', $responseHandler->getRoute()->getName());
        $response = $this->di->call('Controller\\' . $class, $method, $responseHandler->getParams());

        if (is_array($response)) {
            $response = new Response($response);
        }

        $responseHandler->setResponse($response);
        return $response;
    }

    /**
     * Call controller action
     * @param $controller
     * @param array $args
     * @return \Http\Response
     */
    public function callAction($controller, array $args = array())
    {
        $pos = strrpos($controller, '/');
        $response = $this->di->call('Controller\\' . substr($controller, 0, $pos), substr($controller, $pos + 1), $args);
        if (is_array($response)) {
            $response = new Response($response);
        }

        return $response;
    }

    /**
     * Generate url's for controller
     * @param string $controller
     * @param array $args
     * @param boolean $escaped
     * @return string
     * @throws \Exception
     */
    public function url($controller, array $args = array(), $escaped = true)
    {
        $pos = strrpos($controller, '/');
        $controllerName = substr_replace($controller, "::", $pos, 1);

        foreach ($this->routeCollection as $r) {
            if ($r->getName() == $controllerName) {
                $route = $r;
                break;
            }
        }

        if (!isset($route))
            throw new \Exception('No such route: ' . $controller);

        $pattern = $route->getPath();
        foreach ($args as $key => $arg) {
            $pattern = preg_replace('({' . $key . '})', $escaped ? rawurlencode($arg) : $arg, $pattern);
        }
        $pattern = preg_replace('(/?{.+})', '', $pattern);
        return $this->getUrl($pattern);
    }

    /**
     * Return home site URL
     * @param string $path
     * @return string
     */
    public function getUrl($path = null)
    {
        $host = $this->request->host();
        if (is_null($path))
            return $host;

        return $host . '/' . rtrim($path, '/');
    }

    /**
     * @param string $controller
     * @param array $args
     * @param bool $escaped
     * @throws \Exception
     */
    public function redirect($controller, array $args = array(), $escaped = true)
    {
        //TODO
        // Cookie::destruct();
        Http::header('Location', $this->url($controller, $args, $escaped));
        exit;
    }

}
