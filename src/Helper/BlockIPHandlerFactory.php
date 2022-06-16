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
        $config = $container->get('config');
        $storage = $container->get(FileSystemStorage::class);

        return new BlockIPHandler($config, $storage);
    }
}