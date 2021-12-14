<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP;

use Magento\Framework\HTTP\ClientInterface;

class RequestManager
{
    /**
     * Cache to limit HTTP requests.
     * Cached values only live per request and aren't stored
     *
     * @var array
     */
    private $cache = [];

    /**
     * @param \FishPig\WordPress\Model\UrlInterface $url
     */
    public function __construct(
        \FishPig\WordPress\Model\UrlInterface $url,      
        \Magento\Framework\HTTP\ClientFactory $httpClientFactory,
        \FishPig\WordPress\App\HTTP\RequestManager\Logger $requestLogger
    ) {
        $this->url = $url;
        $this->httpClientFactory = $httpClientFactory;
        $this->requestLogger = $requestLogger;
    }

    /**
     * @param  string $url = null
     * @return ClientInterface
     */
    public function get(string $url = null): ClientInterface
    {
        return $this->makeRequest('GET', $url);
    }

    /**
     * @param  string $url = null
     * @param  array $data = []
     * @return ClientInterface
     */
    public function post(string $url = null, array $data = []): ClientInterface
    {
        return $this->makeRequest('POST', $url, $data);
    }
    
    /**
     * @param  string $url = null
     * @return ClientInterface
     */
    public function head(string $url = null): ClientInterface
    {
        return $this->makeRequest('HEAD', $url);
    }

    /**
     * @param  string $method = null
     * @param  string $url    = null
     * @param  array $data    = null
     * @return ClientInterface
     */
    protected function makeRequest(string $method, string $url = null, $args = null): ClientInterface
    {
        $cacheKey = md5($method . strtolower($url ?? '_current'));        
        
        if (!isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = $client = $this->httpClientFactory->create();
            
            $logData = [
                'method' => str_pad($method, 4),
                'status' => '',
                'url' => $url
            ];
            
            try {
                if ($args === null) {
                    $client->$method($url);
                } else {
                    $client->$method($url, $args);
                }

                $logData['status'] = $client->getStatus();
                $this->requestLogger->logApiRequest($logData);
            } catch (\Exception $e) {
                $logData['status'] = $client->getStatus();
                $logData[] = $e->getMessage();
                
                $this->requestLogger->logApiRequest($logData);
                
                throw $e;
            }
        }
        
        
        return $this->cache[$cacheKey];
    }
}
