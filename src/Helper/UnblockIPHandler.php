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
use Psr\Http\Server\RequestHandlerInterface;
use Ryudith\MezzioBlockIp\Storage\StorageInterface;

class UnblockIPHandler implements RequestHandlerInterface 
{
    private array $config;
    private StorageInterface $storage;

    public function __construct (array $config, StorageInterface $storage)
    {
        $this->config = $config;
        $this->storage = $storage;
    }

    /**
     * Helper in handler form to unblock IP or remove record from block list.
     * 
     * @param ServerRequestInterface $request Server request object
     * @return ResponseInterface Response with process message.
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $requestIp = $this->getRequestIP();
        if (! in_array($requestIp, $this->config['admin_whitelist_ip'])) 
        {
            return new TextResponse('', 403);
        }

        $paramKey = $this->config['helper_unblock_query_param_key'];
        $queryParams = $request->getQueryParams();
        if (! isset($queryParams[$paramKey]) || ! filter_var($queryParams[$paramKey], FILTER_VALIDATE_IP))
        {
            return new TextResponse('', 403);
        }

        $responseMessage = 'IP unblock success';
        $unblockIp = $queryParams[$paramKey];
        $filePath = $this->config['block_ip_data_dir'].'/'.md5($unblockIp);
        if (! file_exists($filePath)) 
        {
            $responseMessage = 'No IP in records.';
        }
        else if (! unlink($filePath)) 
        {
            $responseMessage = 'Delete record fail.';
        }

        return new TextResponse($responseMessage);
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