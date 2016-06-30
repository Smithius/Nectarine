<?php

namespace Di;

class Container
{
    const NO_CACHE = 0;
    const CACHE = 1;
    const TRY_CACHE = 2;

    /**
     * @var array
     */
    protected $objects = array();

    /**
     * @var array
     */
    protected $aliases = array();

    /**
     * Return object if exists
     *
     * @param string $class
     * @param null $ifNull
     * @return null
     */
    public function get($class, $ifNull = null)
    {
        if (array_key_exists($class, $this->aliases))
            $class = $this->aliases[$class];

        return array_key_exists($class, $this->objects) ? $this->objects[$class] : $ifNull;
    }

    /**
     * Store new object
     *
     * @param Object $object
     * @param array|null $parameters
     * @param string|bool $alias
     * @return $this
     */
    public function set($object, $parameters = null, $alias = false)
    {
        $class = get_class($object);
        $this->objects[$class] = $object;

        if ($alias) {
            $this->setAlias($class, $alias);
        }
        return $this;
    }

    /**
     * Add alias to class
     *
     * @param Object $class
     * @param string $alias
     * @return $this
     */
    public function setAlias($class, $alias)
    {
        $this->aliases[$alias] = $class;
        return $this;
    }

    /**
     * @param string $alias
     * @return Object
     */
    public function getAlias($alias)
    {
        return $this->aliases[$alias];
    }
}