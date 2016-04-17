<?php

namespace Mvc;

use Http;
use Exception;
use ErrorException;

/**
 * Error class
 */
class Errors
{

    /**
     * @var \Di
     */
    protected $di;

    /**
     * @param \Di $di
     */
    public function __construct($di)
    {
        $this->di = $di;
    }

    /**
     * attachment
     */
    public function register()
    {
        set_error_handler(array($this, 'handle'));
        set_exception_handler(array($this, 'exceptionHandle'));
    }

    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @param $errcontext
     * @throws ErrorException
     */
    public function handle($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if (!error_reporting())
            return;

        throw new ErrorException($errstr, -1, $errno, $errfile, $errline);
    }

    /**
     * @param Exception $ex
     * @throws Exception
     */
    public function exceptionHandle(Exception $ex)
    {
        $status = property_exists($ex, 'status') ? $ex->status : 500;
        Http::status($status);
        if (!DEBUG) {
            try {
                if (is_file(WEB . "/View/Error/{$status}.html.twig")) {
                    $viewFile = "Error/{$status}.html.twig";
                } elseif (is_file(WEB . "/View/error{$status}.html.twig")) {
                    $viewFile = "error{$status}.html.twig";
                } elseif (is_file(WEB . "/View/Error/default.html.twig")) {
                    $viewFile = "Error/default.html.twig";
                } else
                    exit("error");

                $twig = $this->di->get('twig');
                echo $twig->render($viewFile, array('status' => $status));
            } catch (Exception $e) {
            }
            exit;
        }

        echo "<pre>" . $ex->getTraceAsString() . "</pre>";
        throw $ex;
    }

}
