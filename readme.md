# **mezzio-block-ip**

**`Ryudith\MezzioBlockIp`** is middleware to block IP based on request limit.


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
        'limit_duration' => 60,  // value in second, 60 mean limit_hit in 60 second
        'request_real_ip_key' => 'REMOTE_ADDR',  // key for $_ENV or $_SERVER to get request real ip
        'ip_data_dir' => './data/blockip',
        'blacklist_data_dir' => './data/blacklistip',
        'whitelist_data_dir' => './data/whitelistip',
        'file_data_delimiter' => '||',
        'ip_storage_class' => FileSystemStorage::class,
        'ip_response_class' => SimpleResponse::class,
        'admin_whitelist_ip' => [],  // whitelist IPs for admin access to unblock IP
        
        'enable_helper' => false,
        'helper_url_param_op' => 'op',  // URL param key for operation helper
        'helper_url_param_ip' => 'ip',  // URL param key for IP data
        'blacklist_uri_path' => '/blockip/blacklist',
        'whitelist_uri_path' => '/blockip/whitelist',
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

10. **`enable_helper`**  
 is flag to enable helper locate inside **`Ryudith\MezzioBlockIp\Helper`**.

8. **`helper_url_param_op`**  
 is URL query parameter key for helper operation. Default '**op**' and the options is '**add**' or '**delete**'.

9. **`helper_url_param_ip`**  
 is URL query parameter key for helper data IP, default value '**ip**'.

10. **`blacklist_uri_path`**  
 is URI path for blacklist helper operation.

11. **`whitelist_uri_path`**  
 is URI path for whitelist helper operation.

> For example if **enable_helper** is enable (value 'true') you can use helper from URL **http://localhost:8080/blockip/blacklist?op=add&ip=192.168.0.12** to add IP '192.168.0.12' to blacklist.  


## Documentation

[API Documentation](https://github.com/ryudith/mezzio-block-ip/tree/master/docs/api/classes)

[Issues or Questions](https://github.com/ryudith/mezzio-block-ip/issues)