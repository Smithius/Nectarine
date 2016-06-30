<?php

namespace Http;

use Conf;

class Request
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $queryPath;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $get;

    /**
     * @var array
     */
    protected $post;

    /**
     * @param string $host
     * @param string $path
     * @param string $method
     * @param array|null $get
     * @param array|null $post
     */
    public function __construct($host, $path, $method, $get = null, $post = null)
    {
        $this->host = $host;
        $this->queryPath = $path;
        $this->method = $method;
        $this->get = $get;
        $this->post = $post;
    }

    /**
     * Creates a new request with values from PHP's super globals.
     *
     * @return Request A new request
     */
    public static function createFromGlobals()
    {
        $uri = $_SERVER ['REQUEST_URI'];
        $uriQuery = parse_url($uri, PHP_URL_PATH);

        $host = Conf::get('nc.site', $_SERVER['HTTP_HOST']);

        if (strpos($host, 'http') === false) {
            $host = 'http' . (empty($_SERVER['HTTPS']) ? '' : 's') . '://' . $host;
        }

        $withoutUriPath = parse_url($host, PHP_URL_PATH);
        $queryPath = preg_replace('$^' . $withoutUriPath . '$', "", $uriQuery, 1);

        $method = $_SERVER ['REQUEST_METHOD'];
        $get = isset ($_GET) ? $_GET : null;
        $post = isset ($_POST) ? $_POST : null;

        return new Request($host, $queryPath, $method, $get, $post);
    }

    /**
     * @return string
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->queryPath;
    }

    /**
     * @return string
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * @return array|null
     */
    public function get()
    {
        return $this->get;
    }

    /**
     * @return array|null
     */
    public function post()
    {
        return $this->post;
    }
}
