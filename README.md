# ServerRunner

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://poser.pugx.org/yuriy-chabaniuk/server-runner/downloads.png)](https://packagist.org/packages/yuriy-chabaniuk/ServerRunner)

Extension for Codeception to run server based on browser.

> documentation in progress

## Minimum Requirements

- Codeception 2.1.0
- PHP 5.4

## Installation using [Composer](https://getcomposer.org)

```bash
$ composer require ychabaniuk/server-runner
```

Be sure to enable the extension in `codeception.yml` as shown in
[configuration](#configuration) below.

## Configuration

### Enabling ServerRunner

```yaml
extensions:
    enabled:
        - Ychabaniuk\ServerRunner\ServerRunner
    config:
        Ychabaniuk\ServerRunner\ServerRunner:
            serverFolder: 'libs/server/'
            driverFolder: 'libs/driver/'
            serverStartTime: 5
            debug: false
```
