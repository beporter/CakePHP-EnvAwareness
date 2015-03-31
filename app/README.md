# CakePHP Environment Awareness Demo App (Cake 3 Edition)


## Installation (Vagrant)

A vagrant virtual machine is provided for experimentation with the Cake app.

1. Download and install [Vagrant](https://www.vagrantup.com/) and [VirtualBox](https://www.virtualbox.org/).
1. Download [Composer](http://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
1. Run `git clone https://github.com/beporter/CakePHP-EnvAwareness.git`.
1. Run `cd CakePHP-EnvAwareness/app`
1. Run `composer install`.
1. Run `vagrant up`.
1. Visit [http://localhost:8080/](http://localhost:8080/).


## Experimenting

The vagrant VM has an environment variable named `APP_ENV` set to `vagrant` by default.

1. Log into the VM `vagrant ssh`.
1. Move to the web root `cd /var/www/app`
1. Run `bin/cake config_read.config_read Defaults.longName`
1. Run `APP_ENV=prod bin/cake config_read.config_read Defaults.longName`
