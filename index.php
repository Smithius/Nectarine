<?php

define('ABSPATH', dirname(__FILE__) . '/');
define('CORE', ABSPATH . 'core/');

if (file_exists(ABSPATH . 'config.php')) {
	require CORE . 'Kernel.php';
	Kernel::init();
} else
	die("ERROR (Invalid config path)");
