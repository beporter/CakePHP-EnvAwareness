<?php

/**
 * Email configuration class.
 *
 * All configurations are defined in Config/core.php and imported by
 * ::__construct() using Configure::read().
 *
 * @package app.Config
 */
class EmailConfig {

	/**
	 * Default configuration. Will be populated by `__construct()` using the
	 * value from `Configure::read('EmailTransport.default')`.
	 *
	 * @var	array
	 */
	public $default = array();

	/**
	 * Loads email transport information from
	 * `Configure::read('EmailTransport')`, which should be defined in
	 * `Config/core.php`.
	 *
	 * @return void
	 */
	public function __construct() {
		$emailConfigs = Configure::read('EmailTransport');
		if (!is_array($emailConfigs)) {
			throw new Exception('No `EmailTransport` key defined in core.php.');
		}

		foreach ($emailConfigs as $key => $config) {
			$this->{$key} = $config;
		}

		// Optional. Disable if your app does not require at least one default email config.
		if (!property_exists($this, 'default') || !is_array($this->default)) {
			throw new Exception('No `EmailTransport.default` array defined in core.php.');
		}
	}
}
