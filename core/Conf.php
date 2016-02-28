<?php

class Conf {

	/**
	 * @var array config
	 */
	private static $conf;

	public static function init() {
		//  Load default config
		$conf = require_once ABSPATH . '/config.php';

		//  Add local config
		if (is_file(ABSPATH . '/local.php'))
			$conf = array_merge($conf, require_once ABSPATH . '/local.php');
		self::$conf = $conf;
	}

	/**
	 * @param string $key
	 * @param mixed $ifNull
	 * @return mixed
	 */
	public static function get($key = null, $ifNull = null) {
		if (is_null($key))
			return self::$conf;
		return array_key_exists($key, self::$conf) ? self::$conf[$key] : $ifNull;
	}

	/**
	 * @param string $key
	 * @param mixed $val
	 */
	public static function set($key, $val) {
		self::$conf[$key] = $val;
	}

}
