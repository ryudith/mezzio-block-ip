<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp\Helper
 */
declare(strict_types=1);

namespace Ryudith\MezzioBlockIp\Helper;

use Psr\Container\ContainerInterface;

class UnblockIPHandlerFactory 
{
    /**
     * Factory UnblockIPHandler object.
     * 
     * @param ContainerInterface $container Container reference from Mezzio framework.
     * @return UnblockIPHandler Handler for unblock IP helper
     */
    public function __invoke (ContainerInterface $container) : UnblockIPHandler
    {
        $config = $container->get('config')['mezzio_block_ip'];
        $storage = $container->get($config['block_ip_storage_class']);

        return new UnblockIPHandler($config, $storage);
    }
}