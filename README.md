# Multiline Formatter for Monolog (beta)

## Requirements

- PHP 7.1 or greater
- Monolog 2.9 or greater 
- Json extension

## Installation

Installation is possible using Composer.

```shell
composer require azay/multiline-formatter
```

## Usage
```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Azay\Monolog\Formatter\MultiLineFormatter;

$logger = new Logger('Multiline');
$handler = new StreamHandler('php://stdout', Logger::DEBUG);
$handler->setFormatter(
    new MultiLineFormatter()
);
$logger->pushHandler($handler);

```

### Example
```php
...
$logger->info( 'Some event', [ 'first' => 'This is first value' ] );
...
$logger->info( 'Another event', [ 'a' => 'AAAA', 'b' => 'BBBB', 'XYZ', 1000, true ] );
...
```
### Output
```
2023-05-24T17:12:16+03:00 [INFO] Some event
first: This is first value

2023-05-24T17:12:16+03:00 [INFO] Another event
a: AAAA
b: BBBB
XYZ
1000
1

```