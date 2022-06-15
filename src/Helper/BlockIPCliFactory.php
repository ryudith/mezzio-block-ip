<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp\Helper
 */
declare(strict_types=1);

namespace Ryudith\MezzioBlockIp\Helper;

use Psr\Container\ContainerInterface;

/**
 * BlockIPCli factory class.
 */
class BlockIPCliFactory
{
    /**
     * Factory to create BlockIPCli instance.
     * 
     * @param ContainerInterface $container Container reference from framework.
     * @return BlockIPCli BlockIPCli class instance.
     */
    public function __invoke(ContainerInterface $container) : BlockIPCli
    {
        $config = $container->get('config')['mezzio_block_ip'];
        $storage = $container->get($config['ip_storage_class']);

        return new BlockIPCli($config, $storage);
    }
}