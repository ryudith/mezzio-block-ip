<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp\SimpleResponse
 */
declare(strict_types=1);

namespace Ryudith\MezzioBlockIp\SimpleResponse;

use Psr\Container\ContainerInterface;

class SimpleResponseFactory 
{
    /**
     * Factory to create SimpleResponse object.
     * 
     * @param ContainerInterface $container Container reference from Mezzio framework.
     * @return SimpleResponse Response object.
     */
    public function __invoke (ContainerInterface $container) : SimpleResponse
    {
        return new SimpleResponse();
    }
}