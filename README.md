# Trace Log for Laravel/Lumen

<a href="https://packagist.org/packages/forecho/laravel-trace-log"><img src="https://poser.pugx.org/forecho/laravel-trace-log/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/forecho/laravel-trace-log"><img src="https://poser.pugx.org/forecho/laravel-trace-log/v/unstable.svg" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/forecho/laravel-trace-log"><img src="https://poser.pugx.org/forecho/laravel-trace-log/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/forecho/laravel-trace-log"><img src="https://poser.pugx.org/forecho/laravel-trace-log/license" alt="License"></a>

## Install

```shell
composer require forecho/laravel-trace-log
```

if you use Lumen, you need add service provider to your `bootstrap/app.php` file.

```php
$app->register(Forecho\LaravelTraceLog\TraceLogServiceProvider::class);
```

## Configuration

This step is optional

```shell
php artisan vendor:publish --provider="Forecho\LaravelTraceLog\TraceLogServiceProvider"
```

if you use Lumen, you need add config file to your `config` directory.

```shell
cp vendor/forecho/laravel-trace-log/config/tracelog.php config/
```

**Note**

- The number of bits of the value corresponding to the key in `log_filter_half_hide_keys` must be greater than 2,
  otherwise all data is hidden.
- If the configuration file causes an error, the filtering function will be invalid, the error message can be searched
  for `request_params_filter_key_config_error` to view the log.

## Usage

### Middleware(Optional)

change `App\Http\Kernel.php` file to add `TraceLogMiddleware` middleware.

```php
use Forecho\LaravelTraceLog\Middleware\TraceLogMiddleware;

protected $middlewareGroups = [
    // ...

    'api' => [
        // ...        
        'request.tracelog'
    ],
];


protected $routeMiddleware = [
    // ...    
    'request.tracelog' => TraceLogMiddleware::class
];
```

### Logging

```php

use Forecho\LaravelTraceLog\TraceLog;

TraceLog::warning('This is a warning message.', ['foo' => 'bar']);
TraceLog::error('This is an error message.', ['foo' => 'bar']);
TraceLog::info('This is an info message.', ['foo' => 'bar']);
TraceLog::debug('This is a debug message.', ['foo' => 'bar']);
```

### Get Trace ID

```php
use Forecho\LaravelTraceLog\TraceLog;

TraceLog::getTraceId();
```

### Curl Request

if you want next system use the same trace_id, you need add `trace_id` to your `header`

```php
use Forecho\LaravelTraceLog\TraceLog;

$key = config('tracelog.trace_id_header_key');
$headers = [
  "$key: " . TraceLog::getTraceId(),
]
```
