<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp
 */

declare(strict_types=1);

namespace Ryudith\MezzioBlockIp;

use Psr\Container\ContainerInterface;

class BlockIPFactory
{
    /**
     * Factory to create BlockIP object, Mezzio convention.
     * 
     * @param ContainerInterface $container Container reference from framework.
     * @return BlockIP BlockIP class instance.
     */
    public function __invoke(ContainerInterface $container) : BlockIP
    {
        $config = $container->get('config')['mezzio_block_ip'];
        $storage = $container->get($config['block_ip_storage_class']);

        return new BlockIP($config, $storage);
    }
}