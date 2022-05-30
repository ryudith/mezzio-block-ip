<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp
 */

declare(strict_types=1);

namespace Ryudith\MezzioBlockIp;

use Ryudith\MezzioBlockIp\Helper\UnblockIPHandler;
use Ryudith\MezzioBlockIp\Helper\UnblockIPHandlerFactory;
use Ryudith\MezzioBlockIp\SimpleResponse\SimpleResponse;
use Ryudith\MezzioBlockIp\SimpleResponse\SimpleResponseFactory;
use Ryudith\MezzioBlockIp\Storage\FileSystemStorage;
use Ryudith\MezzioBlockIp\Storage\FileSystemStorageFactory;

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
    }
}