<?php

class Session
{
    /**
     * @param Di $di
     */
    public function __construct($di)
    {
        $di->set($this, 'session');
        if ($path = Conf::get('session.save_path'))
            session_save_path($path);

        if ($storage = Conf::get('session.storage')) {
            $handler = $di->get($storage);
            session_set_save_handler(
                array($handler, 'open'),
                array($handler, 'close'),
                array($handler, 'read'),
                array($handler, 'write'),
                array($handler, 'destroy'),
                array($handler, 'gc')
            );

            session_register_shutdown();
        }

        session_set_cookie_params(Conf::get('nc.cookie.lifetime', 259200), '/');
        session_start();
    }

    /**
     * Has field value
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        if (!isset($_SESSION) || !array_key_exists($name, $_SESSION))
            return false;
        return true;
    }

    /**
     * Get field value
     *
     * @param string $name
     * @param string $default
     * @param bool $del
     * @return mixed
     */
    public function get($name, $default = null, $del = false)
    {
        if (!isset($_SESSION) || !array_key_exists($name, $_SESSION))
            return $default;
        $value = $_SESSION[$name];
        if ($del)
            unset($_SESSION[$name]);
        return $value;
    }

    /**
     * Set field value
     *
     * @param string $name
     * @param string $value
     * @return mixed
     */
    public function set($name, $value)
    {
        return $_SESSION[$name] = $value;
    }

    /**
     * Clear session
     *
     * @note userkey is not cleared
     */
    public function clear()
    {
        session_unset();
    }

    /**
     * Update session lifetime
     *
     * @param int $lifetime
     */
    public function changeLifetime($lifetime)
    {
        if ($lifetime > 0)
            $lifetime += time();
        setcookie(session_name(), session_id(), $lifetime, '/');
    }
}
