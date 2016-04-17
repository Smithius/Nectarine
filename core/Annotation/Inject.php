<?php

namespace Annotation;

/**
 * Marks a property as an injection point
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Inject
{
    /**
     * Entry name.
     * @var string
     */
    public $name;

    /**
     * @var lazy
     */
    private $lazy = true;

    /**
     * Inject constructor.
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['lazy']))
            $this->lazy = $values['lazy'];

        // @Inject(name="foo")
        if (isset($values['name']) && is_string($values['name'])) {
            $this->name = $values['name'];

            return;
        }

        // @Inject
        if (!isset($values['value'])) {
            return;
        }

        $values = $values['value'];

        // @Inject("foo")
        if (is_string($values)) {
            $this->name = $values;
        }
    }

    /**
     * @return string Name of the entry to inject
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function getLazy()
    {
        return $this->lazy;
    }
}