<?php
return [
    /**
     * Enable debugging in development environments.
     */
    'debug' => true,

    /**
     * Our vagrant VM runs Mailcatcher, so we want the Cake app to relay
     * outbound email messages to the waiting listener.
     */
    'EmailTransport' => [
        'default' => [
            'className' => 'Smtp',
            'host' => 'localhost',
            'port' => 1025,
            'timeout' => 5,
        ],
    ],

    'Email' => [
        'default' => [
            'transport' => 'default',
            'from' => 'vagrant@localhost',
            //'charset' => 'utf-8',
            //'headerCharset' => 'utf-8',
        ],
    ],

    /**
     * Our vagrant VM runs its own MySQL server internally. We want to
     * direct our Cake app to that database server in development.
     */
    'Datasources' => [
        'default' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => 'localhost',
            //'port' => 'nonstandard_port_number',
            'username' => 'vagrant',
            'password' => 'vagrant',
            'database' => 'vagrant',
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
        ],

        /**
         * Include the db connection for running unit tests. This could
         * also be SQLite, to speed up local tests for developers, while
         * using MySQL in your automated testing environment to mirror
         * production.
         */
        'test' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => 'localhost',
            //'port' => 'nonstandard_port_number',
            'username' => 'vagrant',
            'password' => 'vagrant',
            'database' => 'vagrant_test',
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
        ],
    ],

    /**
     * Override some generic properties of the app to make it easier
     * to tell what environment we're in. We only override the properties
     * that are different from the default (production) configuration.
     */
    'Defaults' => [
        'longName' => 'Demo EnvAwareness App (VAGRANT)',
        'envFlagColor' => '#DD7777',
    ],
];
