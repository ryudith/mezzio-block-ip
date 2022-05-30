<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp\SimpleResponse
 */
declare(strict_types=1);

namespace Ryudith\MezzioBlockIp\SimpleResponse;

use Psr\Http\Message\ResponseInterface as MessageResponseInterface;
use Ryudith\MezzioBlockIp\Storage\StorageInterface;

interface SimpleResponseInterface 
{
    /**
     * Get response from storage status.
     * 
     * @param StorageInterface $storage Storage object reference.
     * @return ResponseInterface Return implementation of ResponseInterface.
     */
    public function getResponse(StorageInterface $storage) : MessageResponseInterface;
}