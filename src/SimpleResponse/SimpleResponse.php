<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp\SimpleResponse
 */
declare(strict_types=1);

namespace Ryudith\MezzioBlockIp\SimpleResponse;

use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Ryudith\MezzioBlockIp\Storage\StorageInterface;

class SimpleResponse implements SimpleResponseInterface 
{
    /**
     * {@inheritDoc}
     */
    public function getResponse(StorageInterface $storage): ResponseInterface
    {
        if ($storage->isBlacklistStorage) 
        {
            return new TextResponse('', 403);
        }
        
        return new TextResponse('test');
    }
}