<?php

namespace Http;

class Response
{
    /**
     * @var string
     */
    protected $type = 'text/html';

    /**
     * @var array
     */
    protected $headers = array();

    /**
     * @var int
     */
    protected $status = \Http::STATUS_OK;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var \Annotation\Route
     */
    protected $route;

    /**
     * @var string
     */
    protected $template;

    /**
     * @param array $data
     * @param string $template
     */
    public function __construct($data, $template = null)
    {
        $this->data = $data;
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Response
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return Response
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Response
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return Response
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return Response
     */
    public function setData($data)
    {
        $this->data = $data;
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
     * @return Response
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return Response
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

}
