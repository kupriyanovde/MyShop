<?php

namespace Opencart\System;

if (!defined('NAMESPACE_SEPARATOR')) {
    define('NAMESPACE_SEPARATOR', '\\');
}

/**
 * Class Autoloader
 */
class Autoloader {
	/**
	 * @var array<string, array<string, mixed>>
	 */
	private array $path = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		spl_autoload_register(function(string $class): void { $this->load($class); });
		spl_autoload_extensions('.php');
	}

	/**
	 * Register
	 *
	 * @param string $namespace
	 * @param string $directory
	 * @param bool   $psr4
	 *
	 * @return void
	 *
	 * @psr-4 filename standard is stupid composer has lower case file structure than its packages have camelcase file names!
	 */
	public function register(string $namespace, string $directory, bool $psr4 = false): void {
		$this->path[$namespace] = [
			'directory' => $directory,
			'psr4'      => $psr4
		];
	}

	/**
	 *
	 *
	 * @param string $class
	 * @return bool
	 */
	public function exist(string $class): bool
	{
		return $this->findFile($class) !== '';
	}

	/**
	 * Load
	 *
	 * @param string $class
	 *
	 * @return bool
	 */
	public function load(string $class): bool {
		$file = $this->findFile($class);

		if (is_file($file)) {
			include_once($file);

			return true;
		}

        //echo 'NOT autoload ' . $file . '  (' . $class . ')' . '<br>';
		return false;
	}

	/**
	 *
	 *
	 * @param string $class
	 * @return string
	 */
	private function findFile(string $class): string
	{
		$namespace = '';
		$file = '';

		$parts = explode(NAMESPACE_SEPARATOR, $class);

		foreach ($parts as $part) {
			if (!$namespace) {
				$namespace .= $part;
			} else {
				$namespace .= NAMESPACE_SEPARATOR . $part;
			}

			if (isset($this->path[$namespace])) {
				$file = substr($class, strlen($namespace));
				if (!$this->path[$namespace]['psr4']) {
					$file = strtolower(preg_replace('~([a-z])([A-Z]|[0-9])~', '\1_\2', $file));
				}
				$file = $this->path[$namespace]['directory'] . trim(str_replace(NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, $file), DIRECTORY_SEPARATOR) . '.php';
			}
		}
		return $file;
	}
}
