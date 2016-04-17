<?php

define('ABSPATH', __DIR__);
define('CORE', ABSPATH . '/core');

if (file_exists(ABSPATH . '/config.php')) {
	require CORE . '/App.php';
	$app = App::instance();
	$app->execute();
} else
	die("ERROR (Invalid config path)");
