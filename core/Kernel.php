<?php

use core\Autoloader;
use core\Cookie;

define('WEB', ABSPATH . 'web/');

class Kernel {

	/**
	 * @var float Start script time
	 */
	private static $time;

	/**
	 * modules dir
	 * @var array 
	 */
	private static $modules = null;

	public static function init() {
		self::stopwatchStart();
		if (version_compare(phpversion(), '5.3.0', '<'))
			throw new Exception('Invalid PHP version, required 5.3.0 or greater.');

		require CORE . 'Autoloader.php';
		include CORE . 'vendor/autoload.php';

		Autoloader::register();
		Conf::init();
		define('DEBUG', Conf::get('nc.debug'));
		define('CLI', !isset($_SERVER['HTTP_HOST']));
		Errors::register();

		session_start();
		ob_start();

		ini_set('date.timezone', 'Europe/Warsaw');
		ini_set('short_open_tag', 1);
		ini_set('magic_quotes_gpc', 0);

		if (!Conf::get('nc.debug')) {
			ini_set('display_errors', 0);
			ini_set('error_reporting', -1);
		}

		Di::set('core\Router');
		Di::set('core\Template');

		// add init files
		foreach (self::modules() as $module) {
			if (file_exists($module . '/init.php'))
				require $module . '/init.php';
		}

		// map All Di
		Di::init();

		if (CLI) {
			self::cli();
		} else {
			self::http();
		}
	}

	public static function modules() {
		if (is_null(self::$modules))
			self::$modules = glob(ABSPATH . 'module/*', GLOB_ONLYDIR);

		return self::$modules;
	}

	public static function http() {
		define('AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
		Di::get('Router')->render(true);
		Cookie::destruct();
		Di::get('Template')->render();
	}

	public static function cli() {
		define('AJAX', FALSE);

		function exception_handler($e) {
			fprintf(STDERR, "%s\n\n%s\n", $e->getMessage(), $e->getTraceAsString());
			exit(1);
		}

		set_exception_handler('exception_handler');
		// TODO
	}

	private static function stopwatchStart() {
		self::$time = microtime(true);
		return self::$time;
	}

	public static function stopwatchStop() {
		return abs(self::$time - microtime(true));
	}

}
