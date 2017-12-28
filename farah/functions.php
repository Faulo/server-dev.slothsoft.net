<?php
/*	
spl_autoload_register(
	function ($class) {
		if (CORE_AUTOLOAD_LOG_ENABLED) {
			$path = SERVER_ROOT . DIR_LOG . 'autoload.log';
			$log = sprintf('[%s] autoloading %s...%s', date(DATE_DATETIME), $class, PHP_EOL);
			if ($handle = fopen($path, 'ab')) {
				fwrite($handle, $log);
				fclose($handle);
			}
		}
		//echo ' now autoloading... '.$class.'! ';my_dump($class);
		//__import($class);
		if (preg_match('~^Slothsoft\\\\([^\\\\]+)\\\\(.+)$~', $class, $match)) {
			$class = str_replace(NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, $match[2]);
			$file = sprintf('%smod/%s/php/%s.php', SERVER_ROOT, strtolower($match[1]), $class);
		} else {
			$class = str_replace(NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, $class);
			$file = sprintf('%smod/lib/php/%s.php', SERVER_ROOT, $class);
		}
		if (file_exists($file)) {
			include $file;
		}
	}
);
//*/

function http_parse_language($language) {
	preg_match_all(BCP47_PREGMATCH, strtolower($language), $ret, PREG_SET_ORDER);
	return $ret;
}

function output($xmlDoc, $xslDoc = null, $debug = false) {
	if (!($xmlDoc instanceof DOMDocument )) {
		$xmlFile = $xmlDoc;
		$xmlDoc = new DOMDocument('1.0', 'UTF-8');
		$xmlDoc->load($xmlFile);
	}
	if ($xslDoc !== null) {
		if (!($xslDoc instanceof DOMDocument )) {
			$xslFile = $xslDoc;
			$xslDoc = new DOMDocument('1.0', 'UTF-8');
			$xslDoc->load($xslFile);
		}
	}
	
	$debug = ($debug or isset($_REQUEST['debug']));
	
	//$charset = 'iso-8859-1';
	$charset = 'UTF-8';
	$mime = 'application/xml';
	/*
	if (isset($_SERVER['HTTP_ACCEPT']) and !(stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml'))) {
		$mime = 'text/html';
		$method = 'html';
		$version = '5.0';
	} else {
		$mime = 'application/xhtml+xml';
		$method = 'xml';
		$version = '1.0';
	}
	if ($xslDoc !== null) {
		$outputElements = $xslDoc->getElementsByTagNameNS('http://www.w3.org/1999/XSL/Transform', 'output');
		foreach ($outputElements as $outputElement) {
			$outputElement->setAttribute('media-type', $mime);
			$outputElement->setAttribute('method', $method);
			$outputElement->setAttribute('encoding', $charset);
			$outputElement->setAttribute('version', $version);
			$outputElement->setAttribute('indent', $debug ? 'yes' : 'no');
		}
	}
	//*/
	if ($xslDoc === null) {
		$finalDoc = $xmlDoc; 
	} else {
		$xslt = new XSLTProcessor();
		$xslt->importStylesheet( $xslDoc );
		$xslt->registerPHPFunctions();
		$finalDoc = $xslt->transformToDoc( $xmlDoc );
		if ($debug) {
			$node = $finalDoc->createElement('DATA');
			$node->setAttribute('style', 'display: none');
			$node->appendChild($finalDoc->importNode($xmlDoc->documentElement, true));
			$finalDoc->documentElement->appendChild($node);
			$node = $finalDoc->createElement('TEMPLATE');
			$node->setAttribute('style', 'display: none');
			$node->appendChild($finalDoc->importNode($xslDoc->documentElement, true));
			$finalDoc->documentElement->appendChild($node);
		}
	}
	/*
	HttpResponse::setCache(true);
	HttpResponse::setGzip(true);
	HttpResponse::setContentType(sprintf('%s; charset=%s', $mime, $charset));
	HttpResponse::setData($finalDoc->saveXML());
	HttpResponse::send();
	return;
	//*/
	switch ($finalDoc->documentElement->namespaceURI) {
		case 'http://www.w3.org/1999/xhtml':
			$mime = 'application/xhtml+xml';
			break;
		case 'http://www.w3.org/2000/svg':
			$mime = 'image/svg+xml';
			break;
		default:
			break;
	}
	$finalDoc->formatOutput = true;
	$data = $finalDoc->saveXML();
	$etag = md5($data);
	$send = true;
	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) and $etag === $_SERVER['HTTP_IF_NONE_MATCH']) {
		header('HTTP/1.1 304 Not Modified');
		die();
	}
	if (!headers_sent()) {
		header(sprintf('Content-Type: %s; charset=%s', $mime, $charset));
		header(sprintf('ETag: %s', $etag));
	}
	if ($send) {
		echo $data;
	}
	//
	die();
}

function my_dump($var) {
	if (!headers_sent()) {
		header('Content-Type: text/plain; charset=UTF-8');
	}
	echo '________________________________________________________________________________' . PHP_EOL;
	/*
	ob_start(
		function($buffer) {
			return $buffer;
			return preg_replace(
				array('/ \(.*\), /s', '/ \(.*\)\)/s'),
				array(', ', ')'),
				$buffer
			);
		}
	);
	debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	ob_end_flush();
	//*/
	debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	echo '--------------------------------------------------------------------------------' . PHP_EOL;
	var_dump($var);
	echo '________________________________________________________________________________' . PHP_EOL;
}
function temp_file($folder, $prefix = null) {
	$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $folder;
	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}
	//return tempnam($path, (string) $prefix);
	//return $path . DIRECTORY_SEPARATOR . uniqid((string) $prefix, true) . '.tmp';
	for ($i = 0; $i < 10; $i++) {
		$ret = $path . DIRECTORY_SEPARATOR . uniqid((string) $prefix, true) . '.tmp';
		if (!file_exists($ret)) {
			return $ret;
		}
	}
	throw new \Exception(sprintf('Could not create temporary file at "%s" D:', $path));
}
function temp_dir($folder, $prefix = null) {
	$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $folder;
	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}
	for ($i = 0; $i < 10; $i++) {
		$ret = $path . DIRECTORY_SEPARATOR . uniqid((string) $prefix, true);
		if (!file_exists($ret) and mkdir($ret, 0777)) {
			return $ret;
		}
	}
	throw new \Exception(sprintf('Could not create temporary directory at "%s" D:', $path));
}

function get_execution_time() {
    static $microtime_start = null;
	if ($microtime_start === null) {
		$microtime_start = microtime(true);
	} else {
		return (int) (1000*(microtime(true) - $microtime_start));
	}
}
get_execution_time();

function log_execution_time($file, $line) {
    if (isset($_REQUEST['dev-time'])) {
    	static $previousMemory = null;
    	static $previousTime = null;
    	
    	$nowMemory = memory_get_usage();
    	$nowTime = get_execution_time();
    	
    	$diffMemory = $previousMemory === null
    		? 0
    		: $nowMemory - $previousMemory;
    	$diffTime = $previousTime === null
    		? 0
    		: $nowTime - $previousTime;
    		
    	$previousMemory = $nowMemory;
    	$previousTime = $nowTime;
	
		printf(
			'Took %5dMB (+%5dMB) and %6dms (+%4dms) to get to line %4d in file "%s"%s',
			$nowMemory / MEMORY_MEGABYTE, $diffMemory / MEMORY_MEGABYTE,
			$nowTime, $diffTime,
			$line, basename($file),
			PHP_EOL
		);
		flush();
	}
}
log_execution_time(__FILE__, __LINE__);

function print_execution_time($echo = true) {
	$ret = sprintf('Execution so far has taken %d ms and %.2f MB.%s', get_execution_time(), memory_get_peak_usage() / 1048576, PHP_EOL);
	if ($echo) {
		echo $ret;
	}
	return $ret;
}