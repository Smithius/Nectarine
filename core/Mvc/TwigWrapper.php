<?php

namespace Mvc;

use App;
use Twig_Environment;
use Twig_SimpleFunction;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;
use Twig_ExtensionInterface;

class TwigWrapper
{
    /**
     * @var array
     */
    protected $_extensions = array();

    /**
     * @var array
     */
    protected $_functions = array();

    /**
     * @var array
     */
    protected $_filters = array();

    /**
     * @var array
     */
    protected $_globals = array();

    /**
     * @var \Twig_Environment
     */
    protected $_twig;

    /**
     * @param \Di $di
     */
    public function __construct($di)
    {
        $di->set($this, 'twig');
    }

    /**
     * Create lazy twig instance
     */
    protected function constructTwig()
    {
        $loader = new Twig_Loader_Filesystem(App::modules('View'));
        $this->_twig = new Twig_Environment($loader, array(
            'cache' => CORE . '/cache/twig',
            'debug' => DEBUG,
            'strict_variables' => DEBUG,
        ));

        foreach ($this->_extensions as $ext) {
            $this->_twig->addExtension($ext);
        }

        $types = array("function", "filter", "global");
        foreach ($types as $type) {
            foreach ($this->{'_' . $type . 's'} as $key => $value) {
                $this->_twig->{"add" . ucfirst($type)}($key, $value);
            }
        }

        if (DEBUG)
            $this->_twig->addExtension(new Twig_Extension_Debug());
    }

    /**
     * Registers an extension.
     *
     * @param Twig_ExtensionInterface $extension A Twig_ExtensionInterface instance
     */
    public function addExtension(Twig_ExtensionInterface $extension)
    {
        $this->_extensions[] = $extension;
    }

    /**
     * Registers a Function.
     *
     * @param string|Twig_SimpleFunction $name The function name or a Twig_SimpleFunction instance
     * @param Twig_FunctionInterface|Twig_SimpleFunction $function A Twig_FunctionInterface instance or a Twig_SimpleFunction instance
     */
    public function addFunction($name, $function = null)
    {
        if ($name instanceof Twig_SimpleFunction) {
            $function = $name;
            $name = $function->getName();
        }

        $this->_functions[$name] = $function;
    }

    /**
     * Registers a Filter.
     *
     * @param string|Twig_SimpleFilter $name The filter name or a Twig_SimpleFilter instance
     * @param Twig_FilterInterface|Twig_SimpleFilter $filter A Twig_FilterInterface instance or a Twig_SimpleFilter instance
     */
    public function addFilter($name, $filter = null)
    {
        $this->_filters[$name] = $filter;
    }

    /**
     * Registers a Global.
     *
     * New globals can be added before compiling or rendering a template;
     * but after, you can only update existing globals.
     *
     * @param string $name The global name
     * @param mixed $value The global value
     */
    public function addGlobal($name, $value)
    {
        $this->_globals[$name] = $value;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (!$this->_twig) {
            $this->constructTwig();
        }
        return call_user_func_array(array($this->_twig, $method), $arguments);
    }
}
