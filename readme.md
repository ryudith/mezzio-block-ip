# **mezzio-block-ip**

**`Ryudith\MezzioBlockIp`** is middleware to block IP based on request.

> Version 1.1.0 remove code auto register route when helper configuration is enable (`enable_helper`) also remove `enable_helper` from configuration and add CLI helper version.   
> So to enable helper please read [enable helper](#enable_helper) below.

## **Installation**

To install run command :

```sh

$ composer require ryudith/mezzio-block-ip

```


## **Usage**

#### **Add `Ryudith\MezzioBlockIp\ConfigProvider`** to **`config/config.php`**  

```php

...

$aggregator = new ConfigAggregator([
    ...
    \Laminas\Diactoros\ConfigProvider::class,

    \Ryudith\MezzioBlockIp\ConfigProvider::class,  // <= add this line

    // Swoole config to overwrite some services (if installed)
    class_exists(\Mezzio\Swoole\ConfigProvider::class)
        ? \Mezzio\Swoole\ConfigProvider::class
        : function (): array {
            return [];
        },

    // Default App module config
    App\ConfigProvider::class,

    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `global.php`
    //   - `*.global.php`
    //   - `local.php`
    //   - `*.local.php`
    new PhpFileProvider(realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php'),

    // Load development config if it exists
    new PhpFileProvider(realpath(__DIR__) . '/development.config.php'),
], $cacheConfig['config_cache_path']);

...

```  


#### Add **`Ryudith\MezzioBlockIp\BlockIPMiddleware`** to **`config/pipeline.php`**

```php

...

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    // The error handler should be the first (most outer) middleware to catch
    // all Exceptions.
    $app->pipe(ErrorHandler::class);
    $app->pipe(BlockIPMiddleware::class);  // <= add this line

    ...

};
```

> You can place `$app->pipe(BlockIPMiddleware::class)` before `$app->pipe(ErrorHandler::class)` if you want.  


## **Custom Configuration**

Configuration is locate in **`vendor/ryudith/mezzio-block-ip/ConfigProvider.php`** :

```php

...

return [
    'dependencies' => [
        'factories' => [
            FileSystemStorage::class => FileSystemStorageFactory::class,
            SimpleResponse::class => SimpleResponseFactory::class,
            BlockIPMiddleware::class => BlockIPMiddlewareFactory::class,
        ],
    ],
    'mezzio_block_ip' => [
        'limit_hit' => 100,
        'limit_duration' => 60,
        'request_real_ip_key' => 'REMOTE_ADDR',
        'ip_data_dir' => './data/blockip',
        'blacklist_data_dir' => './data/blacklistip',
        'whitelist_data_dir' => './data/whitelistip',
        'file_data_delimiter' => '||',
        'ip_storage_class' => FileSystemStorage::class,
        'ip_response_class' => SimpleResponse::class,
        'admin_whitelist_ip' => []
    ],
];

...

```

Detail :

1. **`limit_hit`**  
 is how many request hit per **limit_duration** before blocked.

2. **`limit_duration`**  
 is duration for **limit_hit** in second.

3. **`request_real_ip_key`**  
 is assoc key for get IP from $_SERVER or $_ENV variable.

3. **`ip_data_dir`**  
 is directory location to save record request IP, since the storage implementation is file system based.

4. **`blacklist_data_dir`**  
 is directory location to save blacklist file.

5. **`whitelist_data_dir`**  
 is directory location to save whitelist file.

6. **`file_data_delimiter`**  
 is data delimiter inside file.

7. **`ip_storage_class`**  
 is implementation class of **`Ryudith\MezzioBlockIp\Storage\StorageInterface`** that will do work to check, create, delete blacklist or whitelist data.

8. **`ip_response_class`**  
 is implementation class of **`Ryudith\MezzioBlockIp\SimpleResponse\SimpleResponseInterface`** that will give response from blacklist IP.

9. **`admin_whitelist_ip`**  
 is string array whitelist permanent IP for admin.

## <a name="enable_helper">Enable Helper</a>

To enable helper add the following items to `factories` configuration (usually in `config/dependencies.global.php`) : 

```php

...

'factories' => [
    
    ...
    
    // add this to enable web version helper
    Ryudith\MezzioBlockIp\Helper\BlockIPHandler::class => \Ryudith\MezzioBlockIp\Helper\BlockIPHandlerFactory::class,

    // add this to enable cli version helper
    Ryudith\MezzioBlockIp\Helper\BlockIPCli::class => \Ryudith\MezzioBlockIp\Helper\BlockIPCliFactory::class,

    ...
]

...

```

Then for web helper register helper to `routes.php`  
 
```php
$app->get('/blockip/blacklist', Ryudith\MezzioBlockIp\Helper\BlockIPHandler::class);
$app->get('/blockip/whitelist', Ryudith\MezzioBlockIp\Helper\BlockIPHandler::class);
```

> Make sure your `blacklist_uri_path` and `whitelist_uri_path` value is match to your route path.

For web helper you can change default configuration values as listed below by add array key to `mezzio_block_ip` configuration, also **don't forget** to add your IP to whitelist to be able access helper.

1. **`helper_url_param_op`**  
 is URL query parameter key for helper operation. Default '**op**' and the option value is '**add**' or '**delete**'.

2. **`helper_url_param_ip`**  
 is URL query parameter key for helper data IP, default value '**ip**'.

3. **`blacklist_uri_path`**  
 is URI path for blacklist helper operation.

4. **`whitelist_uri_path`**  
 is URI path for whitelist helper operation.

> ### Change default configuration web helper value  
> Create new file `config/autoload/blockip.local.php` and add :
> ```php
>
> <?php
>
> declare(strict_types=1);
>
> return [
>    'mezzio_block_ip' => [
>        'helper_url_param_op' => 'operation',
>        'helper_url_param_ip' => 'userip',
>        'blacklist_uri_path' => '/blockip/blacklist',
>        'whitelist_uri_path' > '/blockip/whitelist',
>    ],
> ];
>
> ```
> 
> Then you can access helper with URL **http://localhost:8080/blockip/blacklist?operation=add&ip=userip.168.0.12** with your whitelist IP.
  
  
For CLI helper you need to register helper command to `laminas-cli commands` configuration usually locate in file `config/autoload/mezzio.global.php` :

```php

...

'laminas-cli' => [
        'commands' => [
            ...
            'your:command' => Ryudith\MezzioBlockIp\Helper\BlockIPCli::class,
            ...
        ],
    ],

...

```

> Change `your:command` to your own choice command.

## Documentation

[API Documentation](https://github.com/ryudith/mezzio-block-ip/tree/master/docs/api/classes)

[Issues or Questions](https://github.com/ryudith/mezzio-block-ip/issues)