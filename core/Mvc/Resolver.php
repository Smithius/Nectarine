<?php

namespace Mvc;

use Error400;

class Resolver
{
    /**
     * @var array
     */
    protected $fn = array();

    /**
     * Resolve all parameters in array(key => value) structure
     *
     * @param array $params
     * @param array $definition
     * @return array
     */
    public function resolveParams($params, $definition)
    {
        foreach ($params as $param => &$value) {
            if (isset($definition[$param]['type']) &&
                (!array_key_exists('default', $definition[$param]) || $definition[$param]['default'] !== $value)
            )
                $value = $this->resolveParam($definition[$param]['type'], $value, $param);
        }
        return $params;
    }

    /**
     * Validate input params
     *
     * @param mixed $type
     * @param string $param
     * @param mixed $value
     * @return array|void
     * @throws Error400
     * @throws Exception
     */
    public function resolveParam($type, $value, $param)
    {
        switch ($type) {
            case 'array':
                if (!is_array($value))
                    throw new Error400("$param expected array but passed " . gettype($value));
                return $value;
            case 'boolean':
            case 'bool':
                if (1 === $value or '1' === $value or 'true' === $value or true === $value) return true;
                if (0 === $value or '0' === $value or 'false' === $value or false === $value) return false;
                throw new Error400("$param expected boolean but passed: (" . gettype($value) . ') ' . $value);
            case 'string':
                return (string)$value;
            case 'integer':
            case 'int':
                if (!ctype_digit((string)$value))
                    throw new Error400("$param expected integer but passed: " . gettype($value));
                return filter_var($value, FILTER_VALIDATE_INT);
            case 'float':
            case 'double':
                if (!is_numeric((string)$value))
                    throw new Error400("$param expected $type but passed: " . gettype($value));
                return filter_var($value, FILTER_VALIDATE_FLOAT);
            case 'mixed':
                return $value;
        }

        foreach ($this->fn as $callback) {
            $value = call_user_func($callback, $type, $value, $param);
            if ($value)
                return $value;
        }

        throw new Error400("{$param}: type $type not found");
    }

    /**
     * Add custom type resolver
     *
     * @param string $name
     * @param callable $callback
     */
    public function addExtension($name, $callback)
    {
        $this->fn[$name] = $callback;
    }
}