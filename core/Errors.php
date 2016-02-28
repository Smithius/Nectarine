<?php

/**
 * Error class
 */
class Errors {

	/**
	 * attachment
	 * @return
	 */
	public static function register() {
		set_error_handler('Errors::handle');
		set_exception_handler('Errors::exceptionHandle');
	}

	/**
	 * handle
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 * @param $errcontext
	 * @return
	 */
	public static function handle($errno, $errstr, $errfile, $errline, $errcontext) {
		if (!error_reporting())
			return;

		throw new ErrorException($errstr, -1, $errno, $errfile, $errline);
	}

	public static function exceptionHandle(Exception $ex) {
		if (!DEBUG)
			switch (get_class($ex)) {
				case 'Error403':
				case 'Error404':
					$ex->view();
					exit();
					break;

				default :
					$template = Di::get('Template');
					if (!$template)
						$template = new \core\Template();
					$template->render(new \core\Response(array(), 'error500'));
					exit();
			}
			
		throw $ex;
	}

}
