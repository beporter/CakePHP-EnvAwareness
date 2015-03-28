# CakePHP Application Skeleton


## Installation

1. Download [Composer](http://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
1. Run `php composer.phar create-project --prefer-dist cakephp/app [app_name]`.
1. Run `cd CakePHP-EnvAwareness/app`
1. Run `vagrant up puphpet/ubuntu1404-x64`
1. Visit [http://localhost:8080/](http://localhost:8080/)

If Composer is installed globally, run
```bash
composer create-project --prefer-dist cakephp/app [app_name]
```

You should now be able to visit the path to where you installed the app and see
the setup traffic lights.

## Configuration

Read and edit `config/app.php` and setup the 'Datasources' and any other
configuration relevant for your application.
