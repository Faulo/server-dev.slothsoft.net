<?php
namespace Slothsoft\Farah;

require_once __DIR__ . '/../farah/constants.php';

$path = isset($_SERVER['PATH_INFO'])
	? '/' . $_SERVER['PATH_INFO']
	: '';
$mode = Kernel::LOOKUP_ASSET;

$response = Kernel::parseRequest($path, $mode);
$response->send();