<?php

namespace Mvc;

use Http;

class ResponseHandler
{
    /**
     * @var \Http\Request
     */
    protected $request;

    /**
     * @var \Http\Response
     */
    protected $response;

    /**
     * @var \Annotation\Route
     */
    protected $route;

    /**
     * @var array
     */
    protected $params;

    /**
     * @return \Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param \Http\Request $request
     * @return ResponseHandler
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return \Http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param \Http\Response $response
     * @return ResponseHandler
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return \Annotation\Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param \Annotation\Route $route
     * @return ResponseHandler
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return ResponseHandler
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @param array $match
     * @return ResponseHandler
     */
    public function matchedRoute($match)
    {
        $this->route = $match['route'];
        $this->params = $match['params'];
        return $this;
    }

    /**
     * Make response
     */
    public function sendResponse()
    {
        $debug = ob_get_clean();
        foreach ($this->getResponse()->getHeaders() as $name => $value) {
            Http::header($name, $value);
        }

        Http::header('Content-Type:', $this->getResponse()->getType() . '; charset=utf-8');
        Http::status($this->getResponse()->getStatus());

        $body = $this->getResponse()->getBody();
        if ($debug && DEBUG) {
            $body = '<pre class="debug_dump">' . $debug . '</pre>' . $body;
        }

        if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
            header('Content-Encoding: gzip');
            $body = gzencode($body);
        }

        echo $body;
    }
}
