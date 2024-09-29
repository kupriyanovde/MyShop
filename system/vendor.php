<?php
function load_vendor($dir, $autoloader){
	// aws/aws-crt-php
	$autoloader->register('AWS/CRT', $dir . 'aws/aws-crt-php/src/AWS/CRT/', true);
	$autoloader->register('AWS/CRT/Auth', $dir . 'aws/aws-crt-php/src/AWS/CRT/Auth/', true);
	$autoloader->register('AWS/CRT/HTTP', $dir . 'aws/aws-crt-php/src/AWS/CRT/HTTP/', true);
	$autoloader->register('AWS/CRT/IO', $dir . 'aws/aws-crt-php/src/AWS/CRT/IO/', true);
	$autoloader->register('AWS/CRT/Internal', $dir . 'aws/aws-crt-php/src/AWS/CRT/Internal/', true);

// aws/aws-sdk-php
	$autoloader->register('Aws', $dir . 'aws/aws-sdk-php/src/', true);
	if (is_file($dir . 'aws/aws-sdk-php/src/functions.php')) {
		require_once($dir . 'aws/aws-sdk-php/src/functions.php');
	}

// guzzlehttp/guzzle
	$autoloader->register('GuzzleHttp', $dir . 'guzzlehttp/guzzle/src/', true);
	if (is_file($dir . 'guzzlehttp/guzzle/src/functions_include.php')) {
		require_once($dir . 'guzzlehttp/guzzle/src/functions_include.php');
	}

// guzzlehttp/promises
	$autoloader->register('GuzzleHttp\Promise', $dir . 'guzzlehttp/promises/src/', true);

// guzzlehttp/psr7
	$autoloader->register('GuzzleHttp\Psr7', $dir . 'guzzlehttp/psr7/src/', true);

// mtdowling/jmespath.php
	$autoloader->register('JmesPath', $dir . 'mtdowling/jmespath.php/src/', true);
	if (is_file($dir . 'mtdowling/jmespath.php/src/JmesPath.php')) {
		require_once($dir . 'mtdowling/jmespath.php/src/JmesPath.php');
	}

// psr/http-client
	$autoloader->register('Psr\Http\Client', $dir . 'psr/http-client/src/', true);

// psr/http-factory
	$autoloader->register('Psr\Http\Message', $dir . 'psr/http-factory/src/', true);

// psr/http-message
	$autoloader->register('Psr\Http\Message', $dir . 'psr/http-message/src/', true);

// ralouphie/getallheaders
	if (is_file($dir . 'ralouphie/getallheaders/src/getallheaders.php')) {
		require_once($dir . 'ralouphie/getallheaders/src/getallheaders.php');
	}

// scssphp/scssphp
	$autoloader->register('ScssPhp\ScssPhp', $dir . 'scssphp/scssphp/src/', true);

// symfony/deprecation-contracts
	if (is_file($dir . 'symfony/deprecation-contracts/function.php')) {
		require_once($dir . 'symfony/deprecation-contracts/function.php');
	}

// symfony/polyfill-ctype
	$autoloader->register('Symfony\Polyfill\Ctype', $dir . 'symfony/polyfill-ctype//', true);
	if (is_file($dir . 'symfony/polyfill-ctype/bootstrap.php')) {
		require_once($dir . 'symfony/polyfill-ctype/bootstrap.php');
	}

// symfony/polyfill-mbstring
	$autoloader->register('Symfony\Polyfill\Mbstring', $dir . 'symfony/polyfill-mbstring//', true);
	if (is_file($dir . 'symfony/polyfill-mbstring/bootstrap.php')) {
		require_once($dir . 'symfony/polyfill-mbstring/bootstrap.php');
	}

// symfony/polyfill-php80
	$autoloader->register('Symfony\Polyfill\Php80', $dir . 'symfony/polyfill-php80//', true);
	if (is_file($dir . 'symfony/polyfill-php80/bootstrap.php')) {
		require_once($dir . 'symfony/polyfill-php80/bootstrap.php');
	}

// twig/twig
	$autoloader->register('Twig', $dir . 'twig/twig/src/', true);
	if (is_file($dir . 'twig/twig/src/Resources/core.php')) {
		require_once($dir . 'twig/twig/src/Resources/core.php');
	}
	if (is_file($dir . 'twig/twig/src/Resources/debug.php')) {
		require_once($dir . 'twig/twig/src/Resources/debug.php');
	}
	if (is_file($dir . 'twig/twig/src/Resources/escaper.php')) {
		require_once($dir . 'twig/twig/src/Resources/escaper.php');
	}
	if (is_file($dir . 'twig/twig/src/Resources/string_loader.php')) {
		require_once($dir . 'twig/twig/src/Resources/string_loader.php');
	}

}
