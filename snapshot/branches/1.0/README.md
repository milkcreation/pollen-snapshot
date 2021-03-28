# Pollen Snapshot Component

[![Latest Version](https://img.shields.io/badge/release-1.0.0-blue?style=for-the-badge)](https://www.presstify.com/pollen-solutions/snapshot/)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-green?style=for-the-badge)](LICENSE.md)
[![PHP Supported Versions](https://img.shields.io/badge/PHP->=7.4-8892BF?style=for-the-badge&logo=php)](https://www.php.net/supported-versions.php)

Pollen **Snapshot** Component - A Website screenshot generator.

## Installation

```bash
composer require pollen-solutions/snapshot
```

By Default Pollen Snapshot use Puppeteer. NodeJs is required on your system and Puppeteer must be installed as dependencies.

```bash
npm i puppeter --save
```

More simply you can add pollen-solutions/snapshot to the list of dependencies in your package.json file ...

```json
{
  "dependencies": {
    "@pollen-solutions/snapshot": "file:./vendor/pollen-solutions/snapshot/"
  }
}
```
... and launch packages install.

```bash
npm install
```

A next version using WkHTMLtoPDF is in progress.

## Basic Usage

### Minimal configuration

#### Image (jpg|png)

```php
use Pollen\Snapshot\Snapshot;

$snap = new Snapshot();
$snap->img('https://example.com');

var_dump($snap->get());
exit;
```

#### PDF

```php
use Pollen\Snapshot\Snapshot;

$snap = new Snapshot();
$snap->pdf('https://example.com');

var_dump($snap->get());
exit;
```

#### Named Stack

```php
use Pollen\Snapshot\Snapshot;

$snap = new Snapshot();
$snap->img('https://example.com', 'example.com.jpg');
$snap->img('https://example.com', 'example.com.png');
$snap->pdf('https://example.com', 'example.com.pdf');

var_dump($snap->all());
// or
var_dump($snap->get('example.com.jpg'));
exit;
```

### Custom configuration

#### Disabling Overwrite

From the second call, the captures are settled from the files generated from the first call.

```php
use Pollen\Snapshot\Snapshot;

$snap = new Snapshot();
$snap->setOverwrite(false);
$snap->img('https://example.com', 'example.com.jpg');

var_dump($snap->get());
exit;
```

#### Change output dir

```php
use Pollen\Snapshot\Snapshot;

$snap = new Snapshot();
$snap->setOutputDir('/your/custom/output/path');
$snap->img('https://example.com');

var_dump($snap->get());
exit;
```

### Pollen Framework Setup

#### Declaration

```php
// config/app.php
use Pollen\Snapshot\SnapshotServiceProvider;

return [
      //...
      'providers' => [
          //...
          SnapshotServiceProvider::class,
          //...
      ]
      // ...
];
```

#### Configuration

```php
// config/gdpr.php
// @see /vendor/pollen-solutions/snapshot/config/snapshot.php
return [
      //...

      // ...
];
```

## HTTP Response Usage

The following examples use Laminas Sapi Emitter, it would be easy to adapt them to a routing system through a
controller.

### Display HTTP Response

```php
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Pollen\Snapshot\Snapshot;

$snap = new Snapshot();
$snap->img('https://example.com');

$response = $snap->displayResponse();

(new SapiEmitter())->emit($response->psr());
exit;
```

### Download HTTP Response

```php
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Pollen\Snapshot\Snapshot;

$snap = new Snapshot();
$snap->img('https://example.com');

$response = $snap->downloadResponse();

(new SapiEmitter())->emit($response->psr());
exit;
```

### Response for particular capture from named stack

```php
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Pollen\Snapshot\Snapshot;

$snap = new Snapshot();
$snap->img('https://example.com', 'example.com.jpg');
$snap->img('https://example.com', 'example.com.png');
$snap->pdf('https://example.com', 'example.com.pdf');

$response = $snap->displayResponse('example.com.jpg');

(new SapiEmitter())->emit($response->psr());
exit;
```
