<?php

use Di\DefinitionSource;
use Http\Request;
use Mvc\Errors;
use Mvc\TwigWrapper;
use Mvc\ResponseHandler;

final class App
{
    /**
     * @var \App
     */
    private static $instance = null;

    /**
     * @var \Di
     */
    private $di;

    /**
     * @var float Start script time
     */
    private $runTime;

    /**
     * @var array
     */
    private $modulesPaths = null;

    /**
     * @return App
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Startup point
     * @throws Exception
     */
    public function execute()
    {
        $this->stopwatchStart();

        if (version_compare(phpversion(), '5.4.0', '<'))
            throw new Exception('Invalid PHP version, required 5.4.0 or greater.');

        $this->define();
        $this->autoloader();
        $this->configurePhp();

        $paths = $this->modulesPaths();
        $this->di = new Di(new DefinitionSource($paths));

        $errors = new Errors($this->di);
        $errors->register();

        //TODO session class
        session_start();
        ob_start();

        new TwigWrapper($this->di);
        $this->di->set(new Mvc\Router($this->di), 'router');
        $this->di->set(new Mvc\Template($this->di), 'template');

        if (!CLI) {
            $responseHandler = $this->di->set(new ResponseHandler, 'responseHandler');
            $responseHandler->setRequest(Request::createFromGlobals());
        }

        $this->callBootClass();
        if (!CLI) {
            $this->resolveHttpRequest();
        } else {
            $this->resolveCliRequest();
        }
    }

    /**
     * Init conf and define constants
     */
    private function define()
    {
        require CORE . '/Conf.php';
        Conf::init();
        define('WEB', ABSPATH . '/web');
        define('DEBUG', Conf::get('nc.debug'));
        define('CLI', !isset($_SERVER['HTTP_HOST']));
    }

    /**
     * Init autoloaders
     */
    private function autoloader()
    {
        require CORE . '/Autoloader.php';
        include CORE . '/vendor/autoload.php';
        Autoloader::register();
    }

    /**
     * Override php.ini config
     */
    private function configurePhp()
    {
        ini_set('date.timezone', 'Europe/Warsaw');
        ini_set('short_open_tag', 1);
        ini_set('magic_quotes_gpc', 0);
        ini_set('display_errors', DEBUG);
        ini_set('error_reporting', 1);
        if (!DEBUG) {
            ini_set('error_reporting', -1);
        }
    }

    /**
     * Return all modules paths, include web folder
     * @param string $subdirectory
     * @return array
     */
    public static function modules($subdirectory = null)
    {
        return self::instance()->modulesPaths($subdirectory);
    }

    /**
     * @param string|null $subdirectory
     * @return array
     */
    public function modulesPaths($subdirectory = null)
    {
        if (is_null($this->modulesPaths)) {
            $modules = glob(ABSPATH . '/module/*', GLOB_ONLYDIR);

            if (!DEBUG)
                $modules = array_diff($modules, glob(ABSPATH . '/module/*-dev', GLOB_ONLYDIR));
            array_unshift($modules, WEB);
            $this->modulesPaths = $modules;
        }

        if (!is_null($subdirectory)) {
            $r = array();
            foreach ($this->modulesPaths as $modulePath) {
                $path = realpath($modulePath . '/' . $subdirectory);
                if (is_dir($path))
                    $r[] = $path;
            }
            return $r;
        }

        return $this->modulesPaths;
    }

    /**
     * call boot annotation
     */
    public function callBootClass()
    {
        $boots = $this->di->getTag('boot');
        usort($boots, function ($a, $b) {
            return $a->getOrder() > $b->getOrder();
        });

        foreach ($boots as $boot) {
            $this->di->make($boot->getName());
        }
    }

    /**
     * Http request
     */
    public function resolveHttpRequest()
    {
        define('AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));

        $responseHandler = $this->di->get('responseHandler');
        $router = $this->di->get('router');

        $responseHandler->matchedRoute($router->findRoute($responseHandler->getRequest()));
        $router->dispatch($responseHandler);
        $this->di->get('template')->render($responseHandler);

        $responseHandler->sendResponse();
        //TODO Cookie class update
        //Cookie::destruct();
    }

    /**
     * Cli request
     */
    public function resolveCliRequest()
    {
        define('AJAX', FALSE);

        set_exception_handler(function ($e) {
            fprintf(STDERR, "%s\n\n%s\n", $e->getMessage(), $e->getTraceAsString());
            exit(1);
        });

        $cliCallback = Conf::get('nc.cli.callback');
        if ($cliCallback)
            $this->di->make($cliCallback);
        else
            echo "Undefined config variable: 'nc.cli.callback' \n";
    }

    /**
     * @return float|mixed
     */
    private function stopwatchStart()
    {
        return $this->runTime = microtime(true);
    }

    /**
     * @return number
     */
    public function stopwatchStop()
    {
        return abs(self::$time - microtime(true));
    }

}
