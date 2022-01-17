# Trace Log for Laravel/Lumen

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
\Forecho\LaravelTraceLog\TraceLog::warning('This is a warning message.', ['foo' => 'bar']);
```


