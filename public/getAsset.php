<?php

use Slothsoft\Farah\Kernel;

require_once __DIR__ . '/../vendor/autoload.php';

$path = isset($_SERVER['PATH_INFO'])
	? '/' . $_SERVER['PATH_INFO']
	: '';
$mode = Kernel::LOOKUP_ASSET;

$response = Kernel::parseRequest($path, $mode);
$response->send();