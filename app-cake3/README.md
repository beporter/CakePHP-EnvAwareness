# CakePHP Environment Awareness Demo App (Cake 3 Edition)


## Installation (Vagrant)

A vagrant virtual machine is provided for experimentation with the Cake app.

1. Download and install [Vagrant](https://www.vagrantup.com/) and [VirtualBox](https://www.virtualbox.org/)
1. Download [Composer](http://getcomposer.org/doc/00-intro.md) or update `composer self-update`
1. Run `git clone https://github.com/beporter/CakePHP-EnvAwareness.git`
1. Run `cd CakePHP-EnvAwareness/app`
1. Run `composer install`
1. Run `vagrant up`


## Experimenting

The vagrant VM has an environment variable named `APP_ENV` set to `vagrant` by default in the Apache virtual host config and on the command line. Early in Cake's bootstrapping process, the main config file, `config/app.php` is loaded containing the "master" configs for the app.

In this demo, the `APP_ENV` environment variable is read and the value is used to look for a matching config file. In this case, `config/app-vagrant.php`. This file is loaded on top of the master config.

Any values defined in this second config will be merged into the master configs, allowing individual values to be overridden for specific values of `APP_ENV`.

A third config file named `config/app-local.php` may be created that will not be tracked by the git repository and can be used to further override values for local testing.


### Web Server

1. Visit [http://localhost:8080/](http://localhost:8080/).
1. Follow the instructions in the **Environment Experiments** section of the app's homepage.


### Command Line

1. Log into the VM: `vagrant ssh`.
1. Check that the environment variable is already set by the user's `.profile` file by running: `echo $APP_ENV`.
	* What value is displayed?
1. Move to the web root: `cd /var/www/app`
1. Run: `bin/cake config_read.config_read Defaults.longName`
	* Which config file contains the output value that was displayed?
1. Run: `APP_ENV=prod bin/cake config_read.config_read Defaults.longName`
	* Which config file contains the output value that was displayed this time?
	* Why was that value used?
1. Create `config/app-local.php` and define the `Defaults.longName` key in it with a value of your own choosing.
1. Run: `bin/cake config_read.config_read Defaults.longName` again.
1. Run: `APP_ENV=prod bin/cake config_read.config_read Defaults.longName` again.
	* Why is the output the same in both cases now?
