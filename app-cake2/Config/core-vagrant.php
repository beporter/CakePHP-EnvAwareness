<?php
/**
 * This is an environment-specific core configuration file.
 *
 * It contains vagrant-specific overrides for the common config settings
 * in `Config/core.php`. Only items that must truly be different from the
 * master core config should be added here.
 */

$config = array(

    /**
     * Enable debugging in development environments.
     */
	'debug' => true,

	/**
     * Our vagrant VM runs Mailcatcher, so we want the Cake app to relay
     * outbound email messages to the waiting listener.
	 */
	'EmailTransport' => array(
		'default' => array(
			'transport' => 'Smtp',
            'host' => 'localhost',
            'port' => 1025,
            'timeout' => 5,
		),
	),

	/**
     * Our vagrant VM runs its own MySQL server internally. We want to
     * direct our Cake app to that database server in development.
	 */
	'Datasources' => array(
		'default' => array(
			'datasource' => 'Database/Mysql',
			'persistent' => false,
			'host' => 'localhost',
			'login' => 'vagrant',
			'password' => 'vagrant',
			'database' => 'vagrant',
		),
 
        /**
         * Include the db connection for running unit tests. This could
         * also be SQLite, to speed up local tests for developers, while
         * using MySQL in your automated testing environment to mirror
         * production.
         */
		'test' => array(
			'datasource' => 'Database/Mysql',
			'persistent' => false,
			'host' => 'localhost',
			'login' => 'vagrant',
			'password' => 'vagrant',
			'database' => 'vagrant_test',
		),
	),

	/**
	 * Vagrant environment hints.
	 *
     * Override some generic properties of the app to make it easier
     * to tell what environment we're in. We only override the properties
     * that are different from the default (production) configuration.
	 */
	'Defaults' => array(
        'longName' => 'Demo EnvAwareness App (VAGRANT)',
        'envFlagColor' => '#DD7777',
	),

);
