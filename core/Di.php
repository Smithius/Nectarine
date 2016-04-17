<?php

use Di\Container;

class Di
{

    /**
     * @var \Di\Container
     */
    protected $container;

    /**
     * @var \Di\DefinitionSource
     */
    protected $definitionSource;

    public function __construct($source)
    {
        $this->definitionSource = $source;
        $this->container = new Container();
        $this->set($this, 'di');
    }

    /**
     * Return object, if not exist, create it.
     *
     * @param string $name
     * @param mixed $parameters
     * @return object
     */
    public function get($name, $parameters = null)
    {
        $name = trim($name, '\\');
        $obj = $this->container->get($name, $parameters);
        return $obj ?: $this->_set($name, $parameters, null, Container::TRY_CACHE);
    }

    public function set($className, $parameters = array(), $alias = null)
    {
        return $this->_set($className, $parameters, $alias, Container::CACHE);
    }

    /**
     * No cachable class
     */
    public function make($className, $parameters = array())
    {
        return $this->_set($className, $parameters, null, Container::NO_CACHE);
    }

    /**
     * @param $className
     * @param $parameters
     * @param $alias
     * @param $cacheable
     * @return object|void
     * @throws Exception
     */
    protected function _set($className, $parameters, $alias, $cacheable)
    {
        if (is_object($className)) {
            if ($cacheable) {
                if (is_string($parameters))
                    $alias = $parameters;

                $this->container->set($className, $parameters, $alias);
            }
            return $className;
        } elseif (!class_exists($className)) {
            throw new Exception("Di: missing class: '" . $className . "'.");
        }

        $obj = $this->resolve($className, $parameters, $cacheable);
        // if alias bind value

        return $obj;
    }

    /**
     * Call calable
     * @param $class
     * @param $method
     * @param array $parameters
     * @return mixed
     */
    public function call($class, $method, $parameters = array())
    {
        if (!is_object($class)) {
            $class = $this->resolve($class);
        }

        $ref = new ReflectionMethod($class, $method);
        $args = $this->args($ref, $parameters);
        return $ref->invokeArgs($class, $args);
    }

    /**
     * Resolve ars
     * @param \ReflectionMethod $reflectionMethod
     * @param array $args
     * @return array
     */
    protected function args($reflectionMethod, $args)
    {
        $definiton = $this->definitionSource->getDefinition($reflectionMethod->class);
        $definiton = $definiton['methods'][$reflectionMethod->name]['parameters'];

        $r = [];
        foreach ($reflectionMethod->getParameters() as $parameter) {
            if (array_key_exists($parameter->name, $args))
                $r[] = $args[$parameter->name];
            elseif ($class = $parameter->getClass()) {
                //var_dump($class);
            } elseif ($definiton && isset($definiton[$parameter->name]['type'])) {
                $r[] = $this->get($definiton[$parameter->name]['type']);
            } else
                $r[] = $this->get($parameter->name);

//            if ($param->isDefaultValueAvailable())
//                $r[] = $param->getDefaultValue();
        }

        return $r;
    }

    /**
     * Get tagged targets
     * @param $tag tag name
     * @return array of matching targets
     */
    public function getTag($tag)
    {
        return $this->definitionSource->getTag($tag);
    }

    /**
     * resolve object
     * @param $className
     * @param array $parameters
     * @param bool $cacheable
     * @return object
     */
    protected function resolve($className, $parameters = array(), $cacheable = false)
    {
        $definiton = $this->definitionSource->getDefinition($className);

        $ref = new ReflectionClass($className);
        $inst = $ref->newInstanceWithoutConstructor();

        if (isset($definiton['properties'])) {
            foreach ($definiton['properties'] as $name => $property) {
                $prop = $ref->getProperty($name);
                $prop->setAccessible(true);

                // create proxy
                $prop->setValue($inst, $this->get($property->getName()));
            }
        }

        $constructor = $ref->getConstructor();
        if (!is_null($constructor)) {
            $this->call($inst, $constructor->name, $parameters);
        }

        return $inst;
    }


}
