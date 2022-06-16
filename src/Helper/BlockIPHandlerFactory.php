<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp\Helper
 */
declare(strict_types=1);

namespace Ryudith\MezzioBlockIp\Helper;

use Psr\Container\ContainerInterface;
use Ryudith\MezzioBlockIp\Storage\FileSystemStorage;

/**
 * Block IP web helper handler factory.
 * 
 * Configuration key required for web helper :
 * 1. helper_url_param_op : URL query parameter name for operation, default 'op'.
 * 2. helper_url_param_ip : URL query parameter name for IP value, default 'ip'.
 * 3. blacklist_uri_path  : URL route for blacklist IP, default '/blockip/blacklist'.
 * 4. whitelist_uri_path  : URL route for whitelist IP, default '/blockip/whitelist'.
 */
class BlockIPHandlerFactory
{
    /**
     * Factory to create BlockIP object, Mezzio convention.
     * 
     * @param ContainerInterface $container Container reference from framework.
     * @return BlockIP BlockIP class instance.
     */
    public function __invoke (ContainerInterface $container) : BlockIPHandler
    {
        $config = $container->get('config')['mezzio_block_ip'];
        $storage = $container->get(FileSystemStorage::class);

        $config = array_merge_recursive([
            'helper_url_param_op' => 'op',
            'helper_url_param_ip' => 'ip',
            'blacklist_uri_path' => '/blockip/blacklist',
            'whitelist_uri_path' => '/blockip/whitelist',
        ], $config);

        return new BlockIPHandler($config, $storage);
    }
}