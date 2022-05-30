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
use Psr\Http\Server\MiddlewareInterface;
use Ryudith\MezzioBlockIp\SimpleResponse\SimpleResponseInterface;
use Ryudith\MezzioBlockIp\Storage\StorageInterface;

class BlockIPMiddleware implements MiddlewareInterface
{
    /**
     * Assign class properties.
     * 
     * @param array $config Array configuration from application.
     * @param StorageInterface $storage StorageInterface implementation object.
     * @param SimpleResponseInterface $response SimpleResponseInterface implementation object.
     */
    public function __construct(
        /**
         * Application configuration for Block IP.
         * 
         * @var array $config
         */
        private array $config,

        /**
         * Storage implementation reference.
         * 
         * @var StorageInterface $storage
         */
        private StorageInterface $storage,

        /**
         * SimpleResponse implementation reference.
         * 
         * @var SimpleResponseInterface $response
         */
        private SimpleResponseInterface $response
    ) {
    }

    /**
     * Check request IP is in whitelist if yes then pass, 
     * next check is in blacklist if yes then give blacklist response.
     * Last check is new request, valid request and update counter data.
     * 
     * @param ServerRequestInterface $request Server request object
     * @param RequestHandlerInterface $handle Server handle object
     * @return ResponseInterface
     */
    public function process (ServerRequestInterface $request, RequestHandlerInterface $handle) : ResponseInterface
    {
        $this->storage->setRequest($request);

        if ($this->storage->isWhitelist()) 
        {
            return $handle->handle($request);
        }

        if ($this->storage->isBlacklist())
        {
            return $this->response->getResponse($this->storage);
        }

        if (! $this->storage->isRecordExist()) {
            $this->storage->init();
            return $handle->handle($request);
        }

        if (! $this->storage->isValid()) 
        {
            return $this->response->getResponse($this->storage);
        }

        return $handle->handle($request);
    }
}