<?php

class Autoloader
{

    /**
     * Register autolad function
     * @param boolean $prepend
     */
    public static function register($prepend = false)
    {
        spl_autoload_register(array(__CLASS__, 'autoload'), true, $prepend);
    }

    /**
     * Autolad function
     * @param string $class class name
     * @return boolean
     */
    public static function autoload($class)
    {
        $file = self::findClassPath($class);
        if ($file) {
            require $file;
            return true;
        }

        return false;
    }

    /**
     * Find path to file
     * @param string $class
     * @return string
     */
    public static function findClassPath($class)
    {
        $name = '/' . strtr($class, '\\', '/') . '.php';

        if (file_exists(WEB . $name))
            return WEB . $name;

        foreach (array_reverse(\App::modules()) as $module)
            if (file_exists($module . $name))
                return $module . $name;

        if (file_exists(CORE . $name))
            return CORE . $name;

        return false;
    }

}
