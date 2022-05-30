<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp
 */

declare(strict_types=1);

namespace Ryudith\MezzioBlockIp;

use Psr\Container\ContainerInterface;
use Mezzio\Application;
use Psr\Http\Message\ServerRequestInterface;
use Ryudith\MezzioBlockIp\Helper\BlockIPHandler;

class BlockIPMiddlewareFactory
{
    /**
     * Factory to create BlockIP object, Mezzio convention.
     * 
     * @param ContainerInterface $container Container reference from framework.
     * @return BlockIP BlockIP class instance.
     */
    public function __invoke(ContainerInterface $container) : BlockIPMiddleware
    {
        $config = $container->get('config')['mezzio_block_ip'];
        $storage = $container->get($config['ip_storage_class']);
        $response = $container->get($config['ip_response_class']);

        if ($config['enable_helper'])
        {
            $app = $container->get(Application::class);
            $helper = new BlockIPHandler($config, $storage);
            $app->any($config['blacklist_uri_path'], function (ServerRequestInterface $request) use ($helper) {
                return $helper->handle($request);
            });
            $app->any($config['whitelist_uri_path'], function (ServerRequestInterface $request) use ($helper) {
                return $helper->handle($request);
            });
        }

        return new BlockIPMiddleware($config, $storage, $response);
    }
}