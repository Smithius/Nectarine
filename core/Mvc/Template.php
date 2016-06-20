<?php

namespace Mvc;

use Di;
use Conf;
use Http\Response;
use Twig_SimpleFunction;
use Mvc\ResponseHandler;

class Template
{

    /** @var string */
    private $template;

    /** @var \Mvc\Router */
    private $router;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Template constructor.
     * @param \Di $di
     */
    public function __construct($di)
    {
        $this->router = $di->get('router');
        $this->template = Conf::get('nc.template', '');

        $this->twig = $di->get('twig');
        $this->twig->addFunction(new Twig_SimpleFunction('url', array($this, '_url'), array('is_safe' => array('html'))));
        $this->twig->addFunction(new Twig_SimpleFunction('asset', array($this, '_asset'), array('is_safe' => array('html'))));
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param \Mvc\ResponseHandler|NULL $responseHandler
     */
    public function render(ResponseHandler $responseHandler = NULL)
    {
        $debug = ob_get_clean();
        if ($debug && DEBUG) {
            echo('<pre class="debug_dump">' . $debug . '</pre>');
        }

        $response = $responseHandler->getResponse();
        if ($response instanceof Response) {
            $route = $responseHandler->getRoute();
            $name = str_replace("::", "\\", $route->getName());
            $response->setBody($this->twig->render($this->getTemplate() . $name . '.html.twig', $response->getData()));
        }
    }

    /**
     * Twig url function
     * @param $route
     * @param array $args
     * @param bool $escaped
     * @return string
     * @throws \Exception
     */
    public function _url($route, $args = array(), $escaped = true)
    {
        if ($route === 'site')
            return $this->router->getUrl();
        if ($route === 'template')
            return $this->router->getUrl('web/View/' . $this->getTemplate());

        return $this->router->url($route, $args, $escaped);
    }

    /**
     * Twig asset function
     * @param $path
     * @return bool|string
     */
    public function _asset($path)
    {
        $modulesPaths = $this->twig->getLoader()->getPaths();

        foreach ($modulesPaths as $modulePath) {
            $file = realpath($modulePath . '/assets/' . $path);
            if (is_file($file))
                return $this->router->getUrl(str_replace(ABSPATH, '', $file));
        }

        return false;
    }

}
