<?php

namespace Di;

class DefinitionSource
{
    /**
     * @var DebugParser|Parser
     */
    protected $parser;

    /**
     * @var array
     */
    protected $tags;

    /**
     * @var array
     */
    protected $definitions;

    /**
     * @param array $dirs
     */
    public function __construct($dirs)
    {
        $this->parser = DEBUG ? new DebugParser : new Parser;
        $def = $this->parser->load($dirs);

        $this->definitions = $def['objects'];
        $this->tags = $def['tags'];
    }

    /**
     * Returns the DI definition for the entry name.
     * @param string $className
     * @return array
     */
    public function getDefinition($className)
    {
        return $this->definitions[$className];
    }

    public function getTag($tagName)
    {
        return isset($this->tags[$tagName]) ? $this->tags[$tagName] : array();
    }
}