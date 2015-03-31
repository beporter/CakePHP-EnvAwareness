footer: Brian Porter, 2015 [CC BY-SA 4.0](http://creativecommons.org/licenses/by-sa/4.0/)
slidenumbers: true


## Running a CakePHP App in Different Operating Environments


### Brian Porter
### Project Lead & Web Developer at Loadsys


---
## Overview

* What do I mean by "operating environments"?
* How can the needs of an app change based on the environment?
* Review various methods for configuring behavior per environment.
* Requirements for an ideal env-aware config system.
* Examples.

^ All examples will reference Cake 3, but are easily applied to Cake 2.x and even 1.x.


---
## What are "operating environments"?

* Developer's workstation or Vagrant virtual machine.
* Continuous integration or testing server. (Travis, Jenkins)
* Quality assurance site.
* Staging site used for stakeholder review.
* Production instances.

^ An operating environment describes the unique server and resource setup under which your app is expected to run. Environments have different needs and serve different purposes. Your app may have to behave differently depending on where it is running.


---
## How are your app's needs different in each environment?

* Database connections.
* SSL availability or enforcement.
* Email delivery configuration.
* External API integration (Paypal, Stripe, Mailchimp, Google Analytics).

^ The most common example of an environment-specific configuration is your database connection. Typically the DB settings do not overlap between the developer's local working copy and the production server.

^ There are many examples of this and they almost always fall along the outer "edge" of your application where your app interfaces with external services, such as the database, email, or API services.

^ For example, when running your app in a quality assurance environment, you may want to override how email is delivered from your application so QA staff can verify it. Or to avoid HTTP connections to API services when running your unit test suite.


---
## The most basic example

Database connections.

Who hasn't needed to set different database connection values in development and production?

^ Almost every web application ever developed needs to use a different database connection when the developer is working on their local copy versus when it is running in production.


---
## `config/app.php` in development

```php
return [
    'Datasources' => [
        'default' => [
            // *snip*
            'host' => 'localhost',
            'database' => 'my_app',
            'username' => 'my_app',
            'password' => 'secret',
        ],
    ],
];
```


---
## `config/app.php` in production

```php
return [
    'Datasources' => [
        'default' => [
            // *snip*
            'host' => 'my-rds.123abc.us-east-1.rds.amazonaws.com',
            'database' => 'production_app',
            'username' => 'production_app',
            'password' => 'rw8d&FI.?:@2',
        ],
    ],
];
```


---
## How can we handle this difference?

Let's start with some bad examples...


---
## :x: Store individual config files in the repo :x:

```bash
# Access the server
$ ssh deploy@production-server.com
$ ls config/
app.prod.php
app.staging.php
app.qa.php
app.dev.php

# "Deploy" new code
$ git fetch origin master  

# Copy updated config into place.
$ cp config/app.staging.php config/app.php
```

^ This was **much** worse with Cake 2.x, where you might have a copy of `core.php`, `email.php` and `database.php` for every environment.


---
## Oops!

```bash
$ ssh deploy@production-server.com
#                ^
#                production server

$ cp config/app.staging.php config/app.php
#                ^
#                copied the wrong config!
```

---
## What's wrong with copying files?

* :heavy_plus_sign: Easy to understand.
* :heavy_plus_sign: Configs stored in repo.

* :heavy_minus_sign: Not DRY.
* :heavy_minus_sign: Fragile for devs _and_ sysadmins.
* :heavy_minus_sign: Potential security risk. 

^ It's good that you can read the `config/` directory and have a reasonable idea of what's happening.

^ Also good that changes to configs are tracked in the repo.

^ Bad because you have to maintain the FULL set of keys and values in EVERY file.

^ Developers may forget to update an "identical" setting in all versions of the file.

^ Without automatic tooling in place for deploys, a developer or sysadmin may forget to copy an updated config file into the correct place.

^ Storing production API keys and passwords in the repo might be bad.


---
## :x: Not storing configs in the repo at all :x:

```bash
$ cat .gitignore
tmp/
vendor/
config/app.php
#...
```


---
## What's wrong with excluding configs from the repo?

* :heavy_plus_sign: It's simple(?)
* :heavy_plus_sign: No sensitive info in the repo.

* :heavy_minus_sign: No backups or history.
* :heavy_minus_sign: Troubleshooting is harder.
* :heavy_minus_sign: Still not DRY.
* :heavy_minus_sign: Still fragile.

^ Can't track changes to the configs.

^ Can't double-check settings without logging into the appropriate server.

^ The config definitions don't live with the code where they are used. (Definitions live on running servers, usage lives in the codebase.)

^ Someone with access to each env must manually update configs when the change.


---
## :x: Not using Configure at all :x:

```php
// src/Template/Layout/default.ctp
<?php if ($_SERVER['SERVER_NAME'] === 'productionsite.com') {
	echo 'This is production';
} elseif ($_SERVER['SERVER_NAME'] === 'stagingsite.com') {
	echo 'This is staging';
} else {
	echo 'This is development';
} ?>
```


---
## What's wrong with that?

The list is too long to fit on this slide.

Let's just continue on...


---
## Concepts

What are the qualities of the ideal system for handling custom configurations per environment?


---
## A single "switch" **from** the environment defines it

Example using Apache's `SetEnv`:

```apache
# my_apache_vhost.conf
<VirtualHost *:80>
    ServerName stagingsite.com
    SetEnv APP_ENV stage
</VirtualHost>
```

and a command line env var:

```bash
# ~/.profile or ~/.bash_profile
# Make sure env is set for Cake Shells.
export APP_ENV=stage
```

^ This can really be anything that can be defined per-environment, but I prefer an environment variable.

^ In this case it's an environment variable named `APP_ENV`. The value of this variable will match the name of a given config file with the app.


---
## The environment switch should be "artificial"
![right](john_holm_choices.jpg)

The environment flag used must be maleable to adapt to changing circumstances.

_Define your own "independently controllable" environment switch to maintain control of your own destiny._

[^image]: John Holm (CC BY 2.0)<br>flickr.com/photos/29385617@N00/2366471410

^ [^image]

^ It might be tempting to use an "organic" value for the environment switch, such as a server hostname, or IP address.

^ But this binds your config naming to something outside of your control, limiting choices in the future.


---
## All non-sensitive configs are tracked

All environment configs (except those deemed "sensitive") must be tracked in the repo with the code that utilizes them.

Developers must have access to add or change config both where they are defined _and_ where they are used.

_Config changes must not require more than one role (dev + sysadmin) or be done in more than one place (repo + servers)._


---
## Convention is favored over configuration

The app must first be designed to function regardless of the environment.

In other words: Whenever possible, build so that it doesn't matter what environment you are running in.

_Adding environment-specific settings must be done only when there is no other choice._


---
## Env-specific settings are checked and loaded **once**

The app must check _"the thing that defines the environment"_ (environment var, Apache SetEnv, hostname, etc.)  **exactly once**.

The app must perform all environment-specific logic **at that point only**.

_If the environment detection needs to change in the future, there is only one place in the code to change._


---
## Production is the default case

The _most important environment_ your app runs in must be the master of all things.

The app must function correctly in that environment even without an explicit environment set.

_Protect the mission-critical environment from being effected by a missing or invalid environment setup._

^ If any environment is going to be misconfigured, let it be staging, qa or development.


---
## Only necessary keys are overridden (and minimally so)

Leverage the production valus as "defaults" as much as possible.

Keep the other configs DRY by overriding **only** what is different in each environment.

_Reduce the risk of "missing" a config that is defined in multiple places to the minimum possible._


---
## Support untracked overrides for testing and security

The app must allow for a developer to change a config while testing a feature locally without committing the "test" settings.

The app must support situations where sensitive configs must not be stored in the repo.

_Provide a fallback for situations where tracking **all** config files is a hinderance to get the best of both worlds._

^ Devs can change things in development for testing without accidentally COMMITTING those "test" settings by using an untracked file.

^ Allows a client to maintain privacy/secrecy of passwords/keys used by the app by letting them fill in a templated core_local.php file for production use.


---
## App is ignorant of its environment(s)

The app must never be "aware" of the different environments.

It should always be presented with a **single** set of configs to use.

_Provide the same collection of config keys to all environments. Change only their values per-environment._


---
## All environments read the same keys

The app must not wrap retrieved config values in conditionals.

Do **not** do different things depending on a config value: Do the same thing using the different values.

_Let the config bootstrapping process **be** the conditional statement by relocating values from the app into the configs._


---
## Remember this example?

```php
// src/Template/Layout/default.ctp
<?php if ($_SERVER['SERVER_NAME'] === 'productionsite.com') {
	echo 'This is production';
} elseif ($_SERVER['SERVER_NAME'] === 'stagingsite.com') {
	echo 'This is staging';
} else {
	echo 'This is development';
} ?>
```

---
## How about this instead?
![right](browser_envs.jpg)


```php
// src/Template/Layout/default.ctp
<?php echo Configure::read('EnvironmentMessage'); ?>
```

So how do we make that happen?


@TODO: Add an image of 3 browsers with corresponding URLs and the different messages displayed.


---
## Cake's `Configure` class

Cake stores runtime configuration using the `Configure` class.

```php
// Define a key in config/app.php:
Configure::write('MySection.MyKey', 'Cake is awesome');


// Recall the value anywhere else:
echo Configure::read('MySection.MyKey');
```


---
## Cake's `Configure` class

* Has an excellent API for setting, overriding and accessing values.
* Is accessible nearly everywhere in a Cake app.
* With Cake 3, it is also better unified.
	(DB, Email, Cache and App settings all in one place.)

This makes it an ideal mechanism for storing environment-specific settings.

^ In fact, as of Cake 3, all of the previously separate configurations have been unified into Configure and the core app skeleton already does almost all of the work for us.


---
## Leveraging the Configure class

* By default, Cake already loads all settings defined in `config/app.php`.

* This is now your "mission-critical" (default) config, typically production.

* We will define per-key override values in additional environment-specific config files.

* Which additional file is loaded will depend on the value of your environment flag.


---
## Environment reminder

Apache `SetEnv`:

```apache
# my_apache_vhost.conf
<VirtualHost *:80>
    ServerName stagingsite.com
    SetEnv APP_ENV stage
</VirtualHost>
```

and a command line environment variable:

```bash
# ~/.profile or ~/.bash_profile
export APP_ENV=stage
```


---
## Stock `config/bootstrap.php`

Cake 3 ships with the following code:

```php
// config/bootstrap.php
try {
    Configure::config('default', new PhpConfig());
    Configure::load('app', 'default', false);
} catch (\Exception $e) {
    // Failure to load the mission-critical
    // config is a fatal error.
    die($e->getMessage() . "\n");
}
```


---
## Loading additional env-specific configs

```php
// config/bootstrap.php

// After loading the stock config file,
// load the environment config file
// and the local config file (when present.)
try {
	$env = getenv('APP_ENV');
	Configure::load("app-{$env}", 'default');
	Configure::load('app-local', 'default');
} catch (\Exception $e) {
	// It is not an error if these files are missing.
}
```

---
## TODO: Dig into how this satisfies the mandates listed above.


---
## (Cake 2.x examples)

With a little extra effort, Cake 2.x and even 1.x can be adapted.

* Enable env config loading in `app/Config/core.php`.
* Convert `app/Config/database.php` to be env-aware.
* Convert `app/Config/email.php` to be env-aware.

@TODO: Set up a sample GitHub repo with these samples and kill the rest of this "slide".

<!--
```php
$env = getenv('APP_ENV');
if (is_readable(dirname(__FILE__) . "/core_{$env}.php")) {
	Configure::load("core_{$env}");
}
if (is_readable(dirname(__FILE__) . "/core_local.php")) {
	Configure::load("core_local");
}
```

```php
class DATABASE_CONFIG {
	public $default = null;
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
```

```php
class EmailConfig {
	public $default = array();
	public function __construct() {
		$emailConfigs = Configure::read('EmailTransport');
		if (!is_array($emailConfigs)) {
			throw new Exception('No `EmailTransport` key defined in core.php.');
		}

		foreach ($emailConfigs as $key => $config) {
			$this->{$key} = $config;
		}

		if (!property_exists($this, 'default') || !is_array($this->default)) {
			throw new Exception('No `EmailTransport.default` array defined in core.php.');
		}
	}
}
```
-->

---
## `config/app.php`

Back in Cake 3 land, our production config:

```php
return [
	'debug' => 0,
    'App' => [
    	'FancyName' => 'Wonderful Application',
    	'EnvSignalColor' => '#ffffff', // White admin background in production.
    ],
];
```


---
### `config/app-dev.php`

Development, overrides only:
 
```php
return [
	'debug' => 1, // Turn debug on in development environments.
    'App' => [
    	// (Note that we don't change the [FancyName] key.)
    	'EnvSignalColor' => '#77cccc', // Red admin background in development.
    ],
];
```


---
## What gets committed? @TODO


* `config/app.php`
* `config/app-*.php` (except `config/app-local.php`)
* (In Cake 2.x: `database.php` and `email.php` also get committed.)

Add `/config/app-local.php` to your `.gitignore` file.

(Each checked out copy of the repo can define its own overrides there.)


---
## Usage Examples @TODO

* Example: styleForEnv()-ish case where "naive" way is to `switch` on the actual value of the env var itself (bad cause code is coupled to the actual VALUES of the environment variable). Better way is to store the actual CSS changes in Configure and just fetch them (easier to adapt too!)

* Example: Passing an environment to Javascript (Ember) in default layout. (Put a "token" value in Configure to represent the environment and set that in a <meta> tag.)

* Using [loadsys/ConfigReadShell](https://github.com/loadsys/CakePHP-ConfigReadShell) to bring env-aware-vars to the command line.

* Env and Shells. AKA: Making sure your cron jobs execute with the correct set of configs.


---
## Other Random Points to work in @TODO

* Works all the way back in 1.2/1.3.
	* Mind how 1.3 loads overrides: Overwrites entire top-level keys!


---
## Questions?

Brian Porter
[@beporter](https://twitter.com/beporter)
<sub>_(although you shouldn't follow me, you'll be disappointed.)_</sub>

Project Lead and Web Developer
for Loadsys Web Strategies
[http://loadsys.com](http://loadsys.com)

[^slides]: [@TODO]()

[^markdown]: [https://gist.github.com/beporter/8134727ce3da27c8bdfa](https://gist.github.com/beporter/8134727ce3da27c8bdfa)

^ [^slides]

^ [^markdown]
