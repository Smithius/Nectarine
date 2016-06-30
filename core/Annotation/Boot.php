<?php

namespace Annotation;

/**
 * Marks a class as an bootstrap
 *
 * @Annotation
 * @Target({"CLASS"})
 */
final class Boot
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $order = 10;

    /**
     * Boots only in debug mode
     * @var bool
     */
    private $debug = false;

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }
}