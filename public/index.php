<?php
namespace Slothsoft\Farah;

require_once __DIR__ . '/../farah/constants.php';

//if (!strpos($_SERVER['REMOTE_ADDR'], ':')) die('Wartungsarbeiten... BRB /o/');

$path = $_SERVER['REQUEST_URI'];

$path = explode('?', $path, 2);
$path = array_shift($path);

if (substr($path, -1) !== '/' and strpos($path, '.') === false) {
	$redirect = $path . '/';
	header(sprintf('Location: %s', $redirect));
	die();
}

$mode = Kernel::LOOKUP_PAGE;

$response = Kernel::parseRequest($path, $mode);
$response->send();