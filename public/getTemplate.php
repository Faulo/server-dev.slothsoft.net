<?php
namespace Slothsoft\Farah;

require_once __DIR__ . '/../farah/constants.php';

$path = isset($_SERVER['PATH_INFO'])
	? $_SERVER['PATH_INFO']
	: '';
$mode = HTTPDocument::LOOKUP_TEMPLATE;

HTTPDocument::parseRequest($path, $mode);