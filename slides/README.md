# Running a CakePHP App in Different Operating Environments

This repo is part of a talk I am presenting at [CakeFest](http://cakefest.org) 2015 in New York.



## Slides

* [env-aware-cake-apps.md](env-aware-cake-apps.md). ([PDF]() @TODO)
* Presented using [Deckset](http://www.decksetapp.com/).
* Early notes lived in [this Gist](https://gist.github.com/beporter/8134727ce3da27c8bdfa) before moving here.



## Talk Summary

Web applications have to run in multiple environments. Those environments may include developers' local instances, in a staging or quality assurance environment and in production. Even the application's most basic dependencies, such as a database connection, tend to be different in each of these environments. Managing the settings for each of them can become tedious and error prone, especially when source control is involved.

This talk covers how to make your CakePHP app adapt its behavior to the environment in a clean way. The configurations for multiple environments can coexist and inherit from a base config, the configs can all be committed to source control and determining which set to activate can be dependent on whatever environment indicator works best for your app's situation. Also covered are code examples for how to keep the code in your app clean and independent of environment-specific logic, instead encapsulating everything unique about each environment into the configurations.



## Info

Brian Porter
[@beporter](https://twitter.com/beporter)
<sub>_(although you shouldn't follow me, you'll be disappointed.)_</sub>

Project Lead and Web Developer
for Loadsys Web Strategies
[http://loadsys.com](http://loadsys.com)

[posted slides]() @TODO

[env-aware-cake-apps.md](env-aware-cake-apps.md)

[https://github.com/beporter/CakePHP-EnvAwareness](https://github.com/beporter/CakePHP-EnvAwareness)



## Resources

* http://www.decksetapp.com/cheatsheet/
* https://www.flickr.com/photos/29385617@N00/2366471410
* https://speakerdeck.com/jtyost2/cakephp-3-intro
