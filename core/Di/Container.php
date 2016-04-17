<?php

namespace Di;

class Container
{
    const NO_CACHE = 0;
    const CACHE = 1;
    const TRY_CACHE = 2;

    protected $objects = array();

    protected $aliases = array();

    /**
     * Return OBJECTI
     *
     * @param $class
     * @param null $ifNull
     * @return null
     */
    public function get($class, $ifNull = null)
    {
        if (array_key_exists($class, $this->aliases))
            $class = $this->aliases[$class];

        return array_key_exists($class, $this->objects) ? $this->objects[$class] : $ifNull;
    }

    public function set($object, $parameters, $alias)
    {
        $class = get_class($object);
        $this->objects[$class] = $object;

        if ($alias) {
            $this->setAlias($class, $alias);
        }
        return $this;
    }

    public function setAlias($class, $alias)
    {
        $this->aliases[$alias] = $class;
        return $this;
    }

    public function getAlias($alias)
    {
        return $this->aliases[$alias];
    }
}