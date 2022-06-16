<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp
 */

declare(strict_types=1);

namespace Ryudith\MezzioBlockIp;

use Ryudith\MezzioBlockIp\SimpleResponse\SimpleResponse;
use Ryudith\MezzioBlockIp\SimpleResponse\SimpleResponseFactory;
use Ryudith\MezzioBlockIp\Storage\FileSystemStorage;
use Ryudith\MezzioBlockIp\Storage\FileSystemStorageFactory;


/**
 * Config provider class.
 * 
 * Library specific configuration description (mezzio_block_ip) :
 * 1. limit_hit            : Define how many limit hit per 'limit_duration' value.
 * 2. limit_duration       : Duration for limit, value in second (default 60 or 1 minute).
 * 3. request_real_ip_key  : 
 * 4. ip_data_dir          : Location for block IP data counter (files) to save.
 * 5. blacklist_data_dir   : Location for blacklist IP data to save.
 * 6. whitelist_data_dir   : Location for whitelist IP data to save.
 * 7. file_data_delimiter  : Char or string delimiter for each data in metadata counter or blacklist or whitelist.
 * 8. ip_storage_class     : Class name for custom storage implementation.
 * 9. ip_response_class    : Class name for custom response implementation.
 * 10. admin_whitelist_ip  : Permanent whitelist IP for administration or testing use.
 */
class ConfigProvider
{
    public function __invoke() : array
    {
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
                'admin_whitelist_ip' => [],
            ],
        ];
    }
}