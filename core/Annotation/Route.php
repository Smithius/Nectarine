<?php

namespace Annotation;

/**
 * Marks a controller method as a route
 * @Annotation
 * @Target({"METHOD"})
 */
final class Route
{
    /**
     * Route path, appended to controller path. Defaults to method name.
     * @var string
     */
    private $path = "";

    /**
     * HTTP method for which route is defined
     * @var array
     */
    private $methods = array('GET');

    /**
     * Route name, used to reference route from templates. Defaults to ControllerName::MethodName.
     * @var string
     */
    private $name = NULL;

    /**
     * Parameters, indexed by the parameter number (index) or name.
     *
     * Used if the annotation is set on a method
     * @var array
     */
    private $parameters = [];

    /**
     * Inject constructor.
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['methods']) && is_array($values['methods']))
            $this->methods = $values['methods'];

        // @Route(path="foo")
        if (isset($values['path']) && is_string($values['path'])) {
            $this->path = $this->normalizePath($values['path']);
            return;
        }

        // @Route
        if (!isset($values['value'])) {
            return;
        }

        $values = $values['value'];
        // @Route("foo")
        if (is_string($values)) {
            $this->path = $this->normalizePath($values);
        }
    }

    /**
     * @param string $path
     * @return string
     */
    private function normalizePath($path)
    {
        return trim($path, '/');
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }


    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param array $methods
     * @return Route
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Route
     */
    public function setName($name)
    {
        $this->name = str_replace('Controller\\', '', $name);
        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     * @return Route
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

}
