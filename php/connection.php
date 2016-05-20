<?php

	require_once('lib.php');
	require_once('lib/rb.php');
	require_once(PROJECT_ROOT.'php/models/user.php');	
	
	if(!file_exists(PROJECT_ROOT."config.inc.php")){ 
		header("Location: install/index.php");
        exit;
	}
	require_once(PROJECT_ROOT . "config.inc.php");
	
	R::setup('mysql:host='.$DB_HOST.';dbname='.$DB_NAME, $DB_USER, $DB_PASS);
	R::exec(file_get_contents(PROJECT_ROOT. 'php/shpm.sql'));
	R::freeze(['banking', 'category', 'device', 'license', 'login', 'pin', 'user']);
	
	function debug() {
		$trace = debug_backtrace();
		$rootPath = dirname(dirname(__FILE__));
		$file = str_replace($rootPath, '', $trace[0]['file']);
		$line = $trace[0]['line'];
		$var = $trace[0]['args'][0];
		$lineInfo = sprintf('<div><strong>%s</strong> (line <strong>%s</strong>)</div>', $file, $line);
		$debugInfo = sprintf('<pre>%s</pre>', print_r($var, true));
		print_r($lineInfo.$debugInfo);
	}