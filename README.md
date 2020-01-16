# LaravelQueryHelper

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

**Note:** Replace ```Babak Nodoust``` ```https://github.com/Alive2212``` ```http://babakn.com``` ```alive2212@yahoo.com``` ```Alive2212``` ```LaravelQueryHelper``` ```Laravel Query Helper``` with their correct values in [README.md](README.md), [CHANGELOG.md](CHANGELOG.md), [CONTRIBUTING.md](CONTRIBUTING.md), [LICENSE.md](LICENSE.md) and [composer.json](composer.json) files, then delete this line. You can run `$ php prefill.php` in the command line to make all replacements at once. Delete the file prefill.php as well.

This Package is smart deep where condition helper. 
you can deep where condition with many relation with just "." between your model and get your results.

## Structure


```
bin/        
config/
src/
tests/
vendor/
```


## Install

Via Composer

``` bash
$   composer require alive2212/laravel-query-helper
```

## Usage

for using just send array filter like following
``` php
    protected $filters = [ // or where condition every thing here
        [ // and  where condition every thing here
            ['id', '=', 1],
            ['title', '=', 'test'],
            ['folan.id', '=', 2],
            ['folan.bahman.id', '=', 3],
            ['folan.title', '=', 'test'],
            ['folan.bahman.amount', '>', 1000],
        ],
        [
            ['id', '=', 4],
            ['folan.id', '=', 5],
            ['folan.title', '=', 'test123'],
            ['folan.bahman.id', '=', 1],
            ['folan.bahman.title', '=', 'test2'],
        ],
        [
            ['id', '=', 7],
            ['folan.id', '=', 8],
            ['folan.title', '=', 'test1234'],
            ['folan.bahman.id', '=', 9],
            ['folan.bahman.title', '=', 'test21'],
        ]
    ];
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email alive2212@yahoo.com instead of using the issue tracker.

## Credits

- [Babak Nodoust][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/Alive2212/LaravelQueryHelper.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/Alive2212/LaravelQueryHelper/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/Alive2212/LaravelQueryHelper.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/Alive2212/LaravelQueryHelper.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/Alive2212/LaravelQueryHelper.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/Alive2212/LaravelQueryHelper
[link-travis]: https://travis-ci.org/Alive2212/LaravelQueryHelper
[link-scrutinizer]: https://scrutinizer-ci.com/g/Alive2212/LaravelQueryHelper/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/Alive2212/LaravelQueryHelper
[link-downloads]: https://packagist.org/packages/Alive2212/LaravelQueryHelper
[link-author]: https://github.com/https://github.com/Alive2212
[link-contributors]: ../../contributors
