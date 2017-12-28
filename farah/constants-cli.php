<?php

foreach ([
	'SERVER_PROTOCOL' => 'HTTP/1.0',
	'SERVER_NAME' => SERVER_NAME,
    'HTTP_HOST' => SERVER_NAME,
	'DOCUMENT_ROOT' => __DIR__,
	'REQUEST_URI' => '/',
	'REQUEST_METHOD' => 'GET',
	'REMOTE_ADDR' => '::1',
] as $key => $val) {
	if (!isset($_SERVER[$key])) {
		$_SERVER[$key] = $val;
	}
}

if (isset($_SERVER['argv'])) {
	if (isset($_SERVER['argv'][0])) {
		$_SERVER['REQUEST_URI'] = $_SERVER['argv'][0];
	}
	if (isset($_SERVER['argv'][1])) {
		$_SERVER['PATH_INFO'] = $_SERVER['argv'][1];
		$_SERVER['REQUEST_URI'] = sprintf('/%s%s', basename($_SERVER['REQUEST_URI']), $_SERVER['PATH_INFO']);
	}
	if (isset($_SERVER['argv'][2])) {
		parse_str($_SERVER['argv'][2], $_REQUEST);
	}
	unset($_SERVER['argv']);
}
if (isset($_SERVER['argc'])) {
	unset($_SERVER['argc']);
}

/**
 * @return array
 */
function apache_request_headers() {
	return [];
}