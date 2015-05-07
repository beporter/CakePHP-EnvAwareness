<?php

/**
 * Database connection configuration loader.
 *
 * Database connection details will be read from the Configure class.
 * Production database information should be placed in `app/config/core.php`.
 * Overrides for staging or vagrant environments should be placed in the
 * corresponding `app/config/core-*.php` files.
 *
 * @package app.Config
 */
class DATABASE_CONFIG {

	/**
	 * Default configuration. Will be populated by `__construct()` using the
	 * value from `Configure::read('Datasources.default')`.
	 *
	 * @var	array
	 */
	public $default = null;

	/**
	 * Loads database connection information from
	 * `Configure::read('Datasources')`, which should be defined in
	 * `Config/core.php`.
	 *
	 * @return void
	 */
	public function __construct() {
		$dbConfigs = Configure::read('Datasources');
		if (!is_array($dbConfigs)) {
			throw new Exception('No `Datasources` connections defined in core.php.');
		}

		foreach ($dbConfigs as $key => $config) {
			$this->{$key} = $config;
		}

		if (!property_exists($this, 'default') || !is_array($this->default)) {
			throw new Exception('No `Datasources.default` connection defined in core.php.');
		}
	}
}
