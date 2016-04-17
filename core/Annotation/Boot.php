<?php

namespace Annotation;

/**
 * Marks a property as an injection point
 * @Annotation
 * @Target({"CLASS"})
 */
final class Boot
{
    /**
     * Entry name.
     * @var string
     */
    public $name;

    /**
     * Entry name.
     * @var string
     */
    public $order = 10;

    /**
     * Entry name.
     * @var bool
     */
    private $debug = false;

    public function getOrder()
    {
        return $this->order;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDebug()
    {
        return $this->debug;
    }
}