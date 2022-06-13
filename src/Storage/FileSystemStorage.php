<?php
/**
 * Assoc key :
 * 1. hit        : Counter hit/visit
 * 2. last_visit : unixtimestamp last visit
 * 3. ip         : request IP
 * 
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp\Storage
 */
declare(strict_types=1);

namespace Ryudith\MezzioBlockIp\Storage;

use Psr\Http\Message\ServerRequestInterface;

class FileSystemStorage implements StorageInterface
{
    /**
     * Flag the current storage is in blacklist or not.
     * 
     * @var bool $isBlacklistStorage
     */
    public bool $isBlacklistStorage = false;

    /**
     * Flag the current storage is in whitelist or not.
     */
    public bool $isWhitelistStorage = false;

    /**
     * Server request reference.
     * 
     * @var ?ServerRequestInterface $request
     */
    private ?ServerRequestInterface $request = null;

    /**
     * IP request.
     * 
     * @var ?string $ip
     */
    private ?string $ip = null;

    /**
     * Pre-generate key.
     * 
     * @var string $key
     */
    private string $pregenKey;

    /**
     * Set class properties.
     * 
     * @param array $config Configuration array reference.
     */
    public function __construct(
        /**
         * Application 'mezzio_block_ip' configuration.
         * 
         * @var string $config
         */
        private array $config
    ) {
        $this->config['ip_data_dir'] = rtrim($this->config['ip_data_dir'], '/').'/';
        $this->config['blacklist_data_dir'] = rtrim($this->config['blacklist_data_dir'], '/').'/';
        $this->config['whitelist_data_dir'] = rtrim($this->config['whitelist_data_dir'], '/').'/';
    }

    /**
     * {@inheritDoc}
     */
    public function setRequest (ServerRequestInterface $request) : void 
    {
        $this->request = $request;
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->pregenKey = $this->generateKey($this->ip);
    }

    /**
     * {@inheritDoc}
     */
    public function isWhitelist () : bool 
    {
        if (in_array($this->ip, $this->config['admin_whitelist_ip'], true)) 
        {
            return true;
        }

        $key = $this->generateKey($this->ip);
        $dir = $this->config['whitelist_data_dir'];
        if (file_exists($dir.$key)) 
        {
            $this->isWhitelistStorage = true;
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isBlacklist () : bool 
    {
        $filePath = $this->config['blacklist_data_dir'].$this->pregenKey;
        if (file_exists($filePath))
        {
            $this->isBlacklistStorage = true;
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isRecordExist () : bool 
    {
        $filePath = $this->config['ip_data_dir'].$this->pregenKey;
        if (file_exists($filePath)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function init () : ?array 
    {
        $data = $this->createRecordData();
        $result = $this->saveData($this->config['ip_data_dir'].$this->pregenKey, $data, $this->config['file_data_delimiter']);
        if ($result)
        {
            return $data;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid () : bool 
    {
        $filePath = $this->config['ip_data_dir'].$this->pregenKey;
        $data = $this->loadData($filePath, $this->config['file_data_delimiter']);
        $inDuration = (time() - $data['last_visit']) < $this->config['limit_duration'];
        $exceedLimit = $data['hit'] >= $this->config['limit_hit'];
        if ($inDuration && $exceedLimit) 
        {
            $this->saveAsBlacklist($this->pregenKey);
            $this->isBlacklistStorage = true;
            return false;
        }

        if ($exceedLimit) 
        {
            $data['hit'] = 1;
        }
        else 
        {
            $data['hit'] += 1;
        }
        
        $data['last_visit'] = time();
        $this->saveData(
            $filePath, 
            $data, 
            $this->config['file_data_delimiter']
        );

        return true;
    }

    /**
     * Change or set current IP value.
     * @param string $ip New IP value.
     * @return void
     */
    public function setIP (string $ip) : void 
    {
        $this->ip = $ip;
        $this->pregenKey = $this->generateKey($ip);
    }

    /**
     * Create new data to blacklist.
     * 
     * @return bool Create result status.
     */
    public function createBlacklistRecord () : bool 
    {
        $filePath = $this->config['blacklist_data_dir'].$this->pregenKey;
        $data = $this->createRecordData();
        return $this->saveData(
            $filePath, 
            $data, 
            $this->config['file_data_delimiter']
        );
    }

    /**
     * Delete data from blacklist.
     * 
     * @return bool Delete result status.
     */
    public function deleteBlacklistRecord () : bool 
    {
        $filePath = $this->config['blacklist_data_dir'].$this->pregenKey;
        if (file_exists($filePath) && unlink($filePath)) 
        {
            return true;
        }

        return false;
    }

    /**
     * Create new data to whitelist.
     * 
     * @return bool Create result status.
     */
    public function createWhitelistRecord () : bool 
    {
        $filePath = $this->config['whitelist_data_dir'].$this->pregenKey;
        $data = $this->createRecordData();
        return $this->saveData(
            $filePath, 
            $data, 
            $this->config['file_data_delimiter']
        );
    }

    /**
     * Delete data from whitelist.
     * 
     * @return bool Delete result status.
     */
    public function deleteWhitelistRecord () : bool 
    {
        $filePath = $this->config['whitelist_data_dir'].$this->pregenKey;
        if (file_exists($filePath) && unlink($filePath)) 
        {
            return true;
        }

        return false;
    }
    
    /**
     * Save as record data to blacklist data.
     * 
     * @param string $key Key to save as blacklist data.
     * @return bool True if process success else false.
     */
    private function saveAsBlacklist (string $key) : bool
    {
        if (! file_exists($this->config['blacklist_data_dir']) && ! mkdir($this->config['blacklist_data_dir'], 0755, true)) 
        {
            return false;
        }

        $blockFile = $this->config['blacklist_data_dir'].$key;
        $counterFile = $this->config['ip_data_dir'].$key;
        return rename($counterFile, $blockFile);
    }

    /**
     * Generate hash key string.
     * 
     * @param string $val String data to be hash.
     * @return string Hash string
     */
    private function generateKey (string $val) : string 
    {
        return sha1($val);
    }

    /**
     * Generate assoc array record data.
     * 
     * @return array Assoc array data.
     */
    private function createRecordData (?string $ip = null) : array 
    {
        return [
            'hit' => 1,
            'last_visit' => time(),
            'ip' => $ip ?? $this->ip,
        ];
    }

    /**
     * Actual function to save record data.
     * 
     * @param string $flename File name record data.
     * @param array $data Record data to save.
     * @param string $delimiter Delimiter data inside file.
     * @return bool True if save data success else false.
     */
    private function saveData (string $filename, array $data, string $delimiter) : bool 
    {
        $dirPath = dirname($filename);
        if (! file_exists($dirPath) && ! mkdir($dirPath, 0755, true)) 
        {
            return false;
        }

        $strData = $data['hit'].$delimiter.$data['last_visit'].$delimiter.$data['ip'];

        return (bool) file_put_contents($filename, $strData);
    }

    /**
     * Actual function to load record data.
     * 
     * @param string $filename File name record data.
     * @param string $delimiter File data delimiter.
     * @return ?array Assoc record data from file.
     */
    private function loadData (string $filename, string $delimiter) : ?array 
    {
        if (! file_exists($filename))
        {
            return null;
        }

        $tmpRecord = explode($delimiter, file_get_contents($filename));
        if (count($tmpRecord) < 2) return null;

        return [
            'hit' => $tmpRecord[0],
            'last_visit' => $tmpRecord[1],
            'ip' => $tmpRecord[2],
        ];
    }
}