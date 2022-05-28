<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp
 */

declare(strict_types=1);

namespace Ryudith\MezzioBlockIp;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Server\MiddlewareInterface;
use Ryudith\MezzioBlockIp\Storage\StorageInterface;

class BlockIP implements MiddlewareInterface
{
    /**
     * Application configuration for Block IP.
     * 
     * @var array $config
     */
    private array $config;

    /**
     * Storage reference.
     * 
     * @var StorageInterface $storage
     */
    private StorageInterface $storage;

    /**
     * Assign class properties.
     * 
     * @param array $config Array configuration from application.
     * @param StorageInterface $storage StorageInterface implementation object.
     */
    public function __construct(array $config, StorageInterface $storage)
    {
        $this->config = $config;
        $this->storage = $storage;
    }

    /**
     * Check if IP in 'admin_whitelist_ip' then pass, else check limit before block the IP.
     * 
     * @param ServerRequestInterface $request Server request object
     * @param RequestHandlerInterface $handle Server handle object
     * @return ResponseInterface
     */
    public function process (ServerRequestInterface $request, RequestHandlerInterface $handle) : ResponseInterface
    {
        $ip = $this->getRequestIP();
        if (in_array($ip, $this->config['admin_whitelist_ip'])) 
        {
            return $handle->handle($request);
        }

        $key = md5($ip);
        $data = $this->storage->load($key);
        if ($data === null)
        {
            $data = [
                'ip' => $ip,
                'last_visit' => time(),
                'hit' => 1,
            ];
            $this->storage->save($key, $data);
        }

        $diffTime = time() - $data['last_visit'];
        if ($diffTime < 60 && $data['hit'] >= $this->config['limit_per_minute']) {
            return new TextResponse('', 403);
        }

        // just reset the hit/visit counter
        if ($data['hit'] >= $this->config['limit_per_minute']) 
        {
            $data['hit'] = 1;
        }
        else 
        {
            $data['hit'] += 1;
        }
        $data['last_visit'] = time();
        $this->storage->save($key, $data);

        return $handle->handle($request);
    }
    
    /**
     * Extract IP from string (without port).
     * 
     * @return string String IP from request.
     */
    private function getRequestIP () : string
    {
        $realIpKey = $this->config['request_real_ip_key'];
        $realIp = getenv($realIpKey);
        if ($realIp === false) 
        {
            $realIp = $_SERVER[$realIpKey];
        }

        $ip = explode(']:', $realIp);
        if (count($ip) > 1) 
        {
            return trim($ip[0], '[');
        }

        $ip = explode(':', $realIp);
        return $ip[0];
    }
}