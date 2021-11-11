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
        \Magento\Framework\HTTP\ClientFactory $httpClientFactory
    ) {
        $this->url = $url;
        $this->httpClientFactory = $httpClientFactory;
    }
    
    /**
     * @param  string $url = null
     * @return ClientInterface
     */
    public function get(string $url = null): ClientInterface
    {
        $cacheKey = md5(__METHOD__ . strtolower($url ?? '_current'));        
        
        if (!$this->isCacheEnabled() || !isset($this->cache[$cacheKey])) {
            $this->log(' GET: ' . $url);
            $this->cache[$cacheKey] = $client = $this->createHttpClient();
            $client->get($url);
        }
        
        return $this->cache[$cacheKey];
    }

    /**
     * @param  string $url = null
     * @param  array $data = []
     * @return ClientInterface
     */
    public function post(string $url = null, array $data = []): ClientInterface
    {
        $cacheKey = md5(__METHOD__ . strtolower($url ?? '_current'));        
        
        if (!$this->isCacheEnabled() || !isset($this->cache[$cacheKey])) {
            $this->log('POST: ' . $url);
            $this->cache[$cacheKey] = $client = $this->createHttpClient();
            $client->post($url, $data);
        }
        
        return $this->cache[$cacheKey];
    }
    
    /**
     * @return ClientInterface
     */
    protected function createHttpClient(): ClientInterface
    {
        $request = $this->httpClientFactory->create();

        $request->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $request->setOption(CURLOPT_SSL_VERIFYPEER, false);

        return $request;
    }

    /**
     * @return bool
     */
    protected function isCacheEnabled(): bool
    {
        return true;
    }

    /**
     * @param string $msg
     * @return void
     */
    private function log(string $msg): void
    {
//        $e = new \Exception('');        $msg .= "\n\n" . preg_replace('/\#6.*$/s', '', $e->getTraceAsString()) . PHP_EOL;

        file_put_contents(BP . '/var/log/wordpress-api-requests.log', $msg . PHP_EOL, FILE_APPEND);   
    }    
}
