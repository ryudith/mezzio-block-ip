<?php
/**
 * Assoc key :
 * 1. hit        : Counter hit/visit
 * 2. last_visit : unixtimestamp last visit
 * 3. ip         : Additional info, request IP
 * 
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp\Storage
 */
declare(strict_types=1);

namespace Ryudith\MezzioBlockIp\Storage;

class FileSystemStorage implements StorageInterface
{
    /**
     * Directory path to Block IP storage.
     * 
     * @var string $path
     */
    private string $path;

    /**
     * Data delimiter inside file.
     * 
     * @var string $delimiter
     */
    private string $delimiter;

    /**
     * Set class properties.
     * 
     * @param string $path
     * @param string $delimiter
     */
    public function __construct(string $path, string $delimiter)
    {
        $this->path = $path;
        $this->delimiter = $delimiter;
    }

    /**
     * Save record to file.
     * 
     * @param string $key Data record key (aka filename)
     * @param array $data Assoc array data record
     * @return bool True if success else false
     */
    public function save (string $key, array $data) : bool 
    {
        if (! file_exists($this->path) && ! mkdir($this->path, 0755, true)) 
        {
            return false;
        }

        $strData = $data['hit'].$this->delimiter.$data['last_visit'].$this->delimiter.$data['ip'];

        return (bool) file_put_contents($this->path.'/'.$key, $strData);
    }

    /**
     * Load record data from file to assoc array.
     * 
     * @param string $key 
     * @return ?array Assoc array record data
     */
    public function load (string $key) : ?array 
    {
        $filePath = $this->path.'/'.$key;
        if (! file_exists($filePath))
        {
            return null;
        }

        $tmpRecord = explode($this->delimiter, file_get_contents($filePath));
        if (count($tmpRecord) < 3) return null;

        return [
            'hit' => $tmpRecord[0],
            'last_visit' => $tmpRecord[1],
            'ip' => $tmpRecord[2],
        ];
    }
}