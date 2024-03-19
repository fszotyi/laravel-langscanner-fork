# laravel-langscanner-fork

This repo is a fork of the `druc/laravel-langscanner` package.

## Installation

You can install the package via composer:

In `composer.json` add:
```
 "repositories": [
     {
         "type": "vcs",
         "url": "git@github.com:fszotyi/laravel-langscanner-fork.git"
     }
 ]
```


```bash
 composer require druc/laravel-langscanner:dev-master
```

## Usage

To get started with the package capabilities you can:

```
 php artisan langscanner --help
```


Scan your project for missing translations:

```
// outputs and writes translations for the specified language (dutch)
php artisan langscanner nl

// outputs and writes translations in the existing {language}.json files
php artisan langscanner

// to scan a module and generate the JSON files into the module (if --path= is provided it will generate the JSON inside the provided "path/resources/lang" directory)
php artisan langscanner en --path=app/Modules/Dashboard

//  to exclude a path from the scan (it will generate the output JSON inside the core app "app_root/lang" directory)
php artisan langscanner en --exclude-path=app/Modules/
``` 

## Credits

This package is based on [joedixon/laravel-translation](https://github.com/joedixon/laravel-translation) and [themsaid/laravel-langman-gui](https://github.com/themsaid/laravel-langman-gui)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
