<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp\Storage
 */

declare(strict_types=1);

namespace Ryudith\MezzioBlockIp\Storage;

interface StorageInterface
{
    /**
     * Save record data.
     * 
     * @param string $key Record data key for save.
     * @param array $data Assoc array record data.
     * @return bool Return true if success or false if fail.
     */
    public function save (string $key, array $data) : bool;

    /**
     * Load record data as assoc array.
     * 
     * @param string $key Record data key to load.
     * @return array|null Return assoc array record data or null if no data.
     */
    public function load (string $key) : array|null;
}