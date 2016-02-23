# typo3-configuration-manager

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]


CLI tools for interacting with Typo3 configuration file LocalConfiguration.php. Can be used to update some serialized extension configs during deployment processes.

## Install

Via Composer

``` bash
$ composer require ironshark/typo3-configuration-manager
```

## Usage


### config:list

Display all configurations as flat key / value list

``` bash
php vendor/bin/typo3-configuration-manager config:list --source-file="path/to/LocalConfiguration.php"

BE.IPmaskList => '*'
BE.compressionLevel => '0'
BE.debug => '1'
...
XT.extConf.sfpmedialibrary.imageCachePath => 'typo3temp/sfpmedialibrary/'
EXT.extConf.sfpmedialibrary.imageQualityDownload => '90'
EXT.extConf.sfpmedialibrary.imageQualityPreview => '70'
EXT.extConf.sfpmedialibrary.complexImageAsJpegFormat => '400'
EXT.extConf.sfpmedialibrary.thumbnailSizes => '400x300,300x300,200x200'
...
```

#### Options

```
php vendor/bin/typo3-configuration-manager config:list -h

  -s, --source-file[=SOURCE-FILE]  Source config-file path [default: "/var/www/typo3conf/LocalConfiguration.php"]
  -h, --help                       Display this help message
  -q, --quiet                      Do not output any message
  -V, --version                    Display this application version
      --ansi                       Force ANSI output
      --no-ansi                    Disable ANSI output
  -n, --no-interaction             Do not ask any interactive question
  -v|vv|vvv, --verbose             Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

### config:update

Replace values in configuration file

#### Examples

Set single configuration

``` bash
php vendor/bin/typo3-configuration-manager config:update --path="SYS.UTF8filesystem" --value="ls"

Replace value at path: SYS.UTF8filesystem: null => ls
```

Set multiple configurations from *JSON* object

``` bash
php vendor/bin/typo3-configuration-manager config:update --value-json={\"SYS.UTF8filesystem\":\"test\"}
Replace value at path: SYS.UTF8filesystem: 1 => test
...
```

Set multiple configurations from *JSON* object using custom source and destination

``` bash
php vendor/bin/typo3-configuration-manager config:update --source-file="/var/www/typo3conf/LocalConfiguration.php" --destination-file="/var/www/typo3conf/LocalConfiguration.php.test" --value-json={\"SYS.binSetup\":\"test\"}

Replace value at path: SYS.binSetup: perl=/usr/bin/perl,unzip=/usr/bin/unzip => test
...
```

##### Options

```
php vendor/bin/typo3-configuration-manager config:update -h

  -s, --source-file[=SOURCE-FILE]            Source config-file path [default: "/var/www/typo3conf/LocalConfiguration.php"]
  -d, --destination-file[=DESTINATION-FILE]  Destination config-file path, source file will be overwritten if no destination provided
  -f, --value-file[=VALUE-FILE]              Path to file with new values
  -j, --value-json[=VALUE-JSON]              New values as JSON
  -p, --path[=PATH]                          Path for single element configuration e.g EXT.extConf.sfpmedialibrary.apiUrl
      --value[=VALUE]                        Value for single element configuration e.g https://api.tld
  -h, --help                                 Display this help message
  -q, --quiet                                Do not output any message
  -V, --version                              Display this application version
      --ansi                                 Force ANSI output
      --no-ansi                              Disable ANSI output
  -n, --no-interaction                       Do not ask any interactive question
  -v|vv|vvv, --verbose                       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

### list

List all available commands

``` bash
php vendor/bin/typo3-configuration-manager list
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email pauli@ironshark.de instead of using the issue tracker.

## Credits

- [Anton Pauli][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/ironshark/typo3-configuration-manager.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/ironshark/typo3-configuration-manager/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/ironshark/typo3-configuration-manager.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/ironshark/typo3-configuration-manager.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ironshark/typo3-configuration-manager.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/ironshark/typo3-configuration-manager
[link-travis]: https://travis-ci.org/ironshark/typo3-configuration-manager
[link-scrutinizer]: https://scrutinizer-ci.com/g/ironshark/typo3-configuration-manager/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/ironshark/typo3-configuration-manager
[link-downloads]: https://packagist.org/packages/ironshark/typo3-configuration-manager
[link-author]: https://github.com/TUNER88
[link-contributors]: ../../contributors
