<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioBlockIp\Helper
 */
declare(strict_types=1);

namespace Ryudith\MezzioBlockIp\Helper;

use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ryudith\MezzioBlockIp\Storage\FileSystemStorage;
use Ryudith\MezzioBlockIp\Storage\StorageInterface;

class BlockIPHandler 
{
    /**
     * Initialize by clean 'blacklist_data_dir' path value.
     * 
     * @param private array $config Configuration assoc array.
     */
    public function __construct (
        /**
         * Configuration array reference from ConfigProvider.
         * 
         * @var array $config
         */
        private array $config,

        /**
         * Storage reference.
         * 
         * @var FileSystemStorage $storage
         */
        private FileSystemStorage $storage
    ) {
        $this->config['ip_data_dir'] = rtrim($this->config['ip_data_dir'], '/').'/';
        $this->config['blacklist_data_dir'] = rtrim($this->config['blacklist_data_dir'], '/').'/';
        $this->config['whitelist_data_dir'] = rtrim($this->config['whitelist_data_dir'], '/').'/';
    }

    /**
     * Helper in handler form to unblock IP or remove record from block list.
     * 
     * @param ServerRequestInterface $request Server request object
     * @return ResponseInterface Response with process message.
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        if (! $this->storage->isWhitelist()) 
        {
            return new TextResponse('', 403);
        }

        // [begin] check URL query parameters
        $queryParams = $request->getQueryParams();
        $opParam = $this->config['helper_url_param_op'];
        $ipParam = $this->config['helper_url_param_ip'];
        $ip = isset($queryParams[$ipParam]) ? $queryParams[$ipParam] : null;
        $operation = isset($queryParams[$opParam]) ? $queryParams[$opParam] : null;
        if (
            $ip === null || ! filter_var($ip, FILTER_VALIDATE_IP) || 
            $operation === null || ! in_array($operation, ['add', 'delete'], true)
        ) {
            return new TextResponse('Invalid parameter');
        }
        // [end] check URL query parameters

        $uriPath = $request->getUri()->getPath();
        $isBlacklistUrl = $uriPath === $this->config['blacklist_uri_path'];
        $this->storage->setIP($ip);
        if ($isBlacklistUrl && $operation === 'add') 
        {
            return $this->addBlacklist($ip);
        }
        else if ($isBlacklistUrl && $operation === 'delete')
        {
            return $this->deleteBlacklist($ip);
        }

        //
        // Whitelist operations (default)
        //
        if ($operation === 'delete') 
        {
            return $this->deleteWhitelist($ip);
        }

        return $this->addWhitelist($ip);
    }

    private function addBlacklist () : ResponseInterface
    {
        $message = 'add ip to blacklist fail.';
        if (! $this->storage->isBlacklist() && $this->storage->createBlacklistRecord()) 
        {
            $message = 'add ip to blacklist success.';
        }

        return new TextResponse($message);
    }

    private function deleteBlacklist () : ResponseInterface
    {
        $message = 'delete ip from blacklist fail.';
        if ($this->storage->isBlacklist() && $this->storage->deleteBlacklistRecord()) 
        {
            $message = 'delete ip from blacklist success.';
        }

        return new TextResponse($message);
    }

    private function addWhitelist () : ResponseInterface 
    {
        $message = 'add ip to whitelist fail.';
        if (! $this->storage->isWhitelist() && $this->storage->createWhitelistRecord()) 
        {
            $message = 'add ip to whitelist success.';
        }

        return new TextResponse($message);
    }

    private function deleteWhitelist () : ResponseInterface 
    {
        $message = 'delete ip from whitelist fail.';
        if ($this->storage->isWhitelist() && $this->storage->deleteWhitelistRecord()) 
        {
            $message = 'delete ip from whitelist success.';
        }
        return new TextResponse($message);
    }
}