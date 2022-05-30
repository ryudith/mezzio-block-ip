<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp\Storage
 */

declare(strict_types=1);

namespace Ryudith\MezzioBlockIp\Storage;

use Psr\Http\Message\ServerRequestInterface;

interface StorageInterface
{
    /**
     * Set server request object reference and request IP.
     * 
     * @param ServerRequestInterface $request Server request object.
     * @return void
     */
    public function setRequest (ServerRequestInterface $request) : void;

    /**
     * Check if request is in whitelist.
     * 
     * @return bool True if in whitelist else false.
     */
    public function isWhitelist () : bool;

    /**
     * Check key is blocked IP storage or not.
     * 
     * @return bool Return true if key is blocked IP storage else false.
     */
    public function isBlacklist () : bool;

    /**
     * Check if record data already exist.
     * 
     * @return bool True if exist else false.
     */
    public function isRecordExist () : bool;

    /**
     * Intialize record data.
     * 
     * @return ?array 
     */
    public function init () : ?array;

    /**
     * Check request is still valid request based record data.
     * 
     * @return bool True if still valid else false.
     */
    public function isValid () : bool;
}