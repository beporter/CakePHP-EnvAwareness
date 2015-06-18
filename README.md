# CakePHP Environment Awareness Demo

A sample CakePHP application that will load configuration information based on an environment flag value.

It is meant to accompany this presentation: [Running a CakePHP App in Different Operating Environments](slides/)

The Cake 3 demo app lives in [app-cake3/](app-cake3/).


## The Short Version

A web app most likely has to connect to a different database when being developed on a developer's workstation or virtual machine compared to the production server(s). This repo demonstrates how to set up a CakePHP project to load different configuration values based on a value unique to each operating environment, such as an environment variable.


In Apache, this can be accomplished using `SetEnv`:

```apache
# my_apache_vhost.conf
<VirtualHost *:80>
    ServerName stagingsite.com
    SetEnv APP_ENV staging
</VirtualHost>
```


On the command line you can export an environment variable (for use with Cake Shells):

```bash
# ~/.profile or ~/.bash_profile
export APP_ENV=staging
```


With Cake 3, you can load additional configuration files quickly in `config/bootstrap.php`:

```php
// config/bootstrap.php

// After loading the stock config file,
// load the environment config file
// and the local config file (when present.)
try {
	$env = getenv('APP_ENV');
	Configure::load("app-{$env}", 'default');
} catch (\Exception $e) {
	// It is not an error if this file is missing.
}
try {
	Configure::load('app-local', 'default');
} catch (\Exception $e) {
	// It is not an error if this file is missing.
}
```


The additional code above will load values from the file `config/app-staging.php`.

Assuming the config files contained the following:

```php
// config/app.php
return [
	'debug' => false,
    'App' => [
    	'FancyName' => 'Wonderful Application',
    	'EnvSignalColor' => '#ffffff', // White admin background in production.
    ],
];
```

```php
// config/app-stage.php
return [
	'debug' => true, // Turn debug on in the staging environment.
    'App' => [
    	// (Note that we don't change the [FancyName] key.)
    	'EnvSignalColor' => '#77cccc', // Red admin background in staging.
    ],
];
```


In your app, you can access a consistently named key and obtain a value appropriate for the current environment. Take `src/Template/Layout/default.ctp` for example:

```html
<!-- src/Template/Layout/default.ctp -->
<head>
	<style>
		.navBackgroundColor {
			background-color: <?php echo Configure::read('App.EnvSignalColor'); ?>;
		}
	</style>
</head>
```


That's a much better alternative than this:

```php
<!-- src/Template/Layout/default.ctp -->
<?php if ($_SERVER['SERVER_NAME'] === 'productionsite.com') {
	$bgColor = '#ffffff'; // white in production
} elseif ($_SERVER['SERVER_NAME'] === 'stagingsite.com') {
	$bgColor = '#cccc77'; // yellow in staging
} else {
	$bgColor = '#77cccc'; // red in development
} ?>
<head>
	<style>
		.navBackgroundColor {
			background-color: <?php echo $bgColor; ?>;
		}
	</style>
</head>
```


## CakePHP 2.x and 1.x

This same principle can be applied to Cake 2.x and 1.x apps, although there are some things to keep in mind.

* Where Cake 3 unifies all configurations into a single file, `config/app.php`. Cakes 1 & 2 uses multiple config files such as `Config/database.php`, `Config/email.php` and `Config/core.php`. This repo has an `app-cake2/` folder that demonstrates how to adapt the Email and Database configurations to load from `Configure`, making them automatically "environment-aware" and bringing them in-line with Cake 3.
* **`Configure::load()` behaves very differently in Cake 1.** It will overwrite keys wholesale, whereas Cakes 2 and 3 will merge keys deeply using `Hash::merge()`. This can have unexpected results in both cases.

There is a [Cake 2.x](app-cake2/) demo app available that demonstrates the necessary changes.


## Feedback?

This is GitHub: Feel free to [open an issue](https://github.com/beporter/CakePHP-EnvAwareness/issues).


## License

Brian Porter, 2015

[CC BY-SA 4.0](http://creativecommons.org/licenses/by-sa/4.0/)

Code released under the [MIT license](LICENSE.md).
