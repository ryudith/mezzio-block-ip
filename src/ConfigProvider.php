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
 * Library specific configuration description (mezzio_custom_log) :
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
 * 11. enable_helper       : Flag to enable blacklist or whitelist helper.
 * 12. helper_url_param_op : Define field query parameter for operation helper, 
 *                           the avaiable operations is 'add' and 'delete' for both blacklist and whitelist helper.
 *                           ('op' part in 'http://localhost:8080/blockip/blacklist?op=add&ip=192.168.12.12').
 * 13. helper_url_param_ip : Define field query parameter for IP data, the value must a valid IP data format.
 *                           ('ip' part in 'http://localhost:8080/blockip/blacklist?op=add&ip=192.168.12.12').
 * 14. blacklist_uri_path  : URI path access for blacklist helper from browser (don't forget whitelist the IP to access this).
 * 15. whitelist_uri_path  : URI path access for whitelist helper from browser (don't forget whitelist the IP to access this).
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
                
                'enable_helper' => false,
                'helper_url_param_op' => 'op',
                'helper_url_param_ip' => 'ip',
                'blacklist_uri_path' => '/blockip/blacklist',
                'whitelist_uri_path' => '/blockip/whitelist',
            ],
        ];
    }
}