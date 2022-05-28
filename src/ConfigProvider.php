<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp
 */

declare(strict_types=1);

namespace Ryudith\MezzioBlockIp;

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
                    BlockIP::class => BlockIPFactory::class,
                ],
            ],
            'mezzio_block_ip' => [
                'limit_per_minute' => 1000,
                'request_real_ip_key' => 'REMOTE_ADDR',  // key for $_ENV or $_SERVER to get request real ip
                'block_ip_data_dir' => './data/blockip',
                'file_data_delimiter' => '||',
                'block_ip_storage_class' => FileSystemStorage::class,
                'admin_whitelist_ip' => [],  // whitelist IPs for admin access to unblock IP

                'helper_unblock_query_param_key' => null,  // URL param key for unblock IP, default null to disable it
            ],
        ];
    }
}