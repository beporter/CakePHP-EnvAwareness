footer: Brian Porter, 2015 [CC BY-SA 4.0](http://creativecommons.org/licenses/by-sa/4.0/)
slidenumbers: true


## Running a CakePHP App in Different Operating Environments


### Brian Porter
### Project Lead & Web Developer at Loadsys


---
## Overview

* What do I mean by "operating environments"?
* How can an app's needs change based on the environment?
* Various methods for configuring Cake per-environment.
* Properties of an ideal env-aware config system.
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

Who hasn't needed to set different database connection values in development and production?

![inline](http://imgs.xkcd.com/comics/exploits_of_a_mom.png)

[^xkcd]: Exploits of a Mom http://xkcd.com/327/

^ Almost every web application ever developed needs to use a different database connection when the developer is working on their local copy versus when it is running in production.

^ [^xkcd]


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
            'host' => 'mine.us-east-1.rds.amazonaws.com',
            'database' => 'production_app',
            'username' => 'production_app',
            'password' => 'rw8d&FI.?:@2',
        ],
    ],
];
```

^ The conflict is that the same config keys need to exist in the same file, but with different values under different conditions.


---
## How can we handle this difference?

* There are many approaches.
* They all have different complexity and tradeoffs.
* Let's start with some common ones...


---
## :x: Not using Configure at all :x:

```php
// src/Template/Layout/default.ctp
<?php
if ($_SERVER['SERVER_NAME'] === 'www.site.com') {
	echo 'This is production';
} elseif ($_SERVER['SERVER_NAME'] === 'stage.site.com') {
	echo 'This is staging';
} else {
	echo 'This is development';
}
?>
```

^ It's one thing when it's a "simple" display string...


---
## :x: Not using Configure at all :x:

```php
<?php
if ($_SERVER['SERVER_NAME'] === 'www.site.com') {
	Configure::write('Datasources.default', [
		'className' => 'Cake\Database\Connection',
		'driver' => 'Cake\Database\Driver\Mysql',
		'persistent' => false,
		'host' => 'prod-db-server.amazonaws.com',
        //'port' => 'nonstandard_port_number',
		'username' => 'rdsuser',
		'password' => 'O*&tITbVfr^%CU',
		'database' => 'productionsite',
		'encoding' => 'utf8',
		'timezone' => 'UTC',
		'cacheMetadata' => true,
        'quoteIdentifiers' => false,
	];
} elseif ($_SERVER['SERVER_NAME'] === 'stage.site.com') {
	Configure::write('Datasources.default', [
		'className' => 'Cake\Database\Connection',
		'driver' => 'Cake\Database\Driver\Mysql',
		'persistent' => false,
		'host' => 'staging-db-server',
        'port' => '3307',
		'username' => 'common',
		'password' => 'password',
		'database' => 'staging',
		'encoding' => 'utf8',
		'timezone' => 'UTC',
		'cacheMetadata' => true,
        'quoteIdentifiers' => false,
	];
} else {
	Configure::write('Datasources.default', [
		'className' => 'Cake\Database\Connection',
		'driver' => 'Cake\Database\Driver\Mysql',
		'persistent' => false,
		'host' => 'localhost',
		'username' => 'root',
		'password' => 'root',
		'database' => 'default',
		'encoding' => 'utf8',
		'timezone' => 'UTC',
		'cacheMetadata' => true,
        'quoteIdentifiers' => false,
	];
}
?>
```

^ It's another thing when you're having to repeat big, slightly-different chunks of code all over your app to control behavior.


---
## What's wrong with that?

* :heavy_minus_sign: Unnecessarily verbose.
* :heavy_minus_sign: Code must change if domain names change.
* :heavy_minus_sign: Hardcoded to 3 specific environments.
* :heavy_minus_sign: The env flag being checked is duplicated in the code.


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

* :heavy_plus_sign: It's straightforward(?)
* :heavy_plus_sign: No sensitive info in the repo.

* :heavy_minus_sign: No backups or history.
* :heavy_minus_sign: Troubleshooting is harder.
* :heavy_minus_sign: Still not DRY.
* :heavy_minus_sign: Still fragile.

^ Can't track changes to the configs.

^ Can't double-check settings without logging into the appropriate server.

^ The config definitions don't live with the code where they are used. (Definitions live on running servers, usage lives in the codebase.)

^ Someone with access to each env must manually update configs when they change. If a dev adds a new key in the code, each server needs to be updated to define it.


---
## :x: Store individual config files in the repo :x:

```bash
# Access the server
$ ssh deploy@production-server.com
$ ls config/app*
app.prod.php
app.staging.php
app.qa.php
app.dev.php

# "Deploy" new code
$ git pull origin master

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
## Concepts

What are the properties of the _ideal_ system for handling custom configurations per environment?


---
## A single "switch" **from** the environment defines it

Example using Apache's `SetEnv`:

```apache
# my_apache_vhost.conf
<VirtualHost *:80>
    ServerName stagingsite.com
    SetEnv APP_ENV staging
</VirtualHost>
```

and a command line env var:

```bash
# ~/.profile or ~/.bash_profile
# Make sure env is set for Cake Shells.
export APP_ENV=staging
```

^ This can really be anything that can be defined per-environment, but I prefer an environment variable.

^ In this case it's an environment variable named `APP_ENV`.

^ This will end up being the value the Cake app uses to load additional configs.


---
## The environment switch should be "artificial"
![right 340%](http://farm3.staticflickr.com/2304/2366471410_56b6da9e71_o_d.jpg)

The environment flag used must be maleable to adapt to changing circumstances.

_Define your own "independently controllable" environment switch to maintain control of your own destiny._

[^choices]: John Holm (CC BY 2.0)<br>flickr.com/photos/29385617@N00/2366471410

^ [^choices]

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

The app must perform logic for loading configs for that environment **at that point**.

_If the environment detection needs to change in the future, there is only one place in the code to change._

^ The app should check for the environment at one point, and fully set up the configs for that environment at the same time. From then on, the app will run with that single collection of configs.


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

^ Good example of this is `debug`. We want it to default to `false` for production, and only selectively turn on in development, testing and review environments.


---
## Support untracked overrides for testing and security

The app must allow for a developer to change a config while testing a feature locally without committing the "test" settings.

The app must support situations where sensitive configs must not be stored in the repo.

_Provide a fallback for situations where tracking **all** config files is a hinderance to get the best of both worlds._

^ Devs can change things in development for testing without accidentally COMMITTING those "test" settings. Achieved by using an untracked file.

^ Allows a client to maintain privacy/secrecy of passwords/keys used by the app by letting them fill in a templated app-local.php file for production use.


---
## App is ignorant of its environment(s)

The app itself must never be "aware" of the different environments.

It should always be presented with a **single** set of configs to use.

_Provide the same collection of config keys to all environments. Change only their values per-environment._

^ You'll know you've broken this rule if you find yourself referring to the value of the environment switch directly in your code. If I'm using the static string `'staging'` anywhere else in my code, that's a smell.


---
## All environments read the same keys

The app must not wrap retrieved config values in conditional statements. (`if/else`)

Do **not** do different things depending on a config value: Do the same thing using the different values.

_Let the config bootstrapping process **be** the conditional statement by relocating values from the app into the configs._


---
## Remember this example?

```php
// src/Template/Layout/default.ctp
<?php
if ($_SERVER['SERVER_NAME'] === 'www.site.com') {
	echo 'This is production';
} elseif ($_SERVER['SERVER_NAME'] === 'stage.site.com') {
	echo 'This is staging';
} else {
	echo 'This is development';
}
?>
```


---
## How about this instead?
![right fit](browser_envs.png)


```php
// src/Template/Layout/default.ctp
<?php
	echo Configure::read('Env.Message');
?>
```

How do we make that happen?


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
    SetEnv APP_ENV staging
</VirtualHost>
```

and a command line environment variable:

```bash
# ~/.profile or ~/.bash_profile
export APP_ENV=staging
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
## `config/app.php`

```php
return [
	'debug' => false,
    'Env' => [
    	'FancyName' => 'Wonderful Application',
    	'SignalColor' => '#ffffff', // White admin bg in production.
    ],
];
```


---
### `config/app-staging.php`

Development, overrides only:
 
```php
return [
	'debug' => true, // Turn debug on in development environments.
    'Env' => [
    	// (Note that we don't change the [FancyName] key.)
    	'SignalColor' => '#77cccc', // Red admin bg in staging.
    ],
];
```


---
## What gets committed?

* `config/app.php`
* `config/app-*.php`
	* _except `config/app-local.php`_
* Add `/config/app-local.php` to `.gitignore`.

^ (Each checked out copy of the repo can define its own overrides there.)


---
## How well does this meet the ideal requirements?

:white_check_mark: Uses a single switch from the environment.

:white_check_mark: The environment switch is artificial.

```apache
<VirtualHost *:80>
	SetEnv APP_ENV staging
</VirtualHost>
```


---
## How well does this meet the ideal requirements?

:white_check_mark: All non-sensitive configs are tracked.

```bash
$ ls config/app*
app.php
app-staging.php
app-quality.php
app-vagrant.php
app-local.php
```

^ Anything that **is** sensitive can be defined in `app-local.php` on each server.


---
## How well does this meet the ideal requirements?

:white_check_mark: Env-specific settings are checked and loaded once.

:white_check_mark: Production is the default case.

:white_check_mark: Untracked overrides supported for testing/security.

```php
try {
	$env = getenv('APP_ENV');
	Configure::load("app-{$env}", 'default');
	Configure::load('app-local', 'default');
} catch (\Exception $e) {}
```


---
## How well does this meet the ideal requirements?

:white_check_mark: App is ignorant of its environment(s).

:white_check_mark: All environments read the same keys.

```php
<?php
	// We don't need to use `getenv()`
	// anywhere in our code.
	echo Configure::read('Env.Message');
?>
```


---
## How well does this meet the ideal requirements?

:white_check_mark: Convention is favored over configuration.

:white_check_mark: Only necessary keys are overridden.

```php
// config/app-staging.php
return [
	'debug' => true,
    'Env' => [
    	'SignalColor' => '#77cccc',
    ],
];
```


---
## Example Project

[github.com/beporter/CakePHP-EnvAwareness](https://github.com/beporter/CakePHP-EnvAwareness)

* A demo Cake 3 app with vagrant.
* Includes [loadsys/ConfigReadShell](https://github.com/loadsys/CakePHP-ConfigReadShell) for command line access.
* Switch app background color based on env.


---
## Other random points

* Works in 2.x via `Config/core.php`.
	* Requires a boilerplate `database.php` and `email.php` that load their configs from `Configure` instead of defining static class properties.
	* @TODO: Examples to come in the demo repo.

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
## Other random points

* Even works all the way back in 1.2/1.3.
	* **Mind `Configure::load()` in 1.x**: It overwrites entire keys instead of merging.
	* No examples _(get away from 1.x please)_, but you can ask me about it.

* Make sure your cron jobs execute with the correct environment set.


---
@TODO:

* Example: styleForEnv()-ish case

^ "naive" way is to `switch()` on the actual value of the env var itself (bad cause code is coupled to the actual VALUES of the environment variable). Better way is to store the actual CSS changes in Configure and just fetch them (easier to adapt too!)

* Example: Passing an environment to Javascript (Ember) in default layout.

^ (Put a "token" value in Configure to represent the environment and set that in a <meta> tag.)


---
## Questions?

Brian Porter
[@beporter](https://twitter.com/beporter)
<sub>_(although you shouldn't follow me, you'll be disappointed.)_</sub>

Project Lead & Web Developer at Loadsys
[loadsys.com](http://loadsys.com)

Slides, Sample Project
[github.com/beporter/CakePHP-EnvAwareness](https://github.com/beporter/CakePHP-EnvAwareness)

