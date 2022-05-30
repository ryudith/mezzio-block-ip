<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp\Storage
 */
declare(strict_types=1);

namespace Ryudith\MezzioBlockIp\Storage;

use Psr\Container\ContainerInterface;

class FileSystemStorageFactory 
{
    /**
     * Factory to create FileSystemStorage object.
     * 
     * @param ContainerInterface $container Container reference from Mezzio framework.
     * @return FileSystemStorage Storage reference to save/load record data.
     */
    public function __invoke(ContainerInterface $container) : FileSystemStorage
    {
        $config = $container->get('config')['mezzio_block_ip'];

        return new FileSystemStorage($config);
    }
}