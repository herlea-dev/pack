## Pack
Tiny package generator, only creating the most bare-bone folder structure 
for a new package. 

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

It also sets up the correct namespacing.