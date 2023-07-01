## Pack
Tiny package generator, only creating the most bare-bone folder structure 
for a new (local only) package. 

### Installation
```
composer require herlea/pack
```

### Usage
```
php artisan make:package <package-name>
```

Creates the following files:
```
|- src/
|  |- PackageServiceProvider.php
|- tests/
|- composer.json
```

It also sets up the correct namespacing, and registers your package in your root `composer.json`. 

Using this tool you can create local packages for utility classes/functionality to be shared across domains within your application easily.