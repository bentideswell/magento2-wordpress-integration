<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP;

use Magento\Framework\HTTP\ClientInterface;
use FishPig\WordPress\App\HTTP\RequestManager\UrlModifierInterface;

class RequestManager
{
    /**
     * @const bool
     */
    const LOG_DATA = false;

    /**
     * Cache to limit HTTP requests.
     * Cached values only live per request and aren't stored
     *
     * @var array
     */
    private $cache = [];

    /**
     * @var array
     */
    private $urlModifiers = [];

    /**
     * @param \FishPig\WordPress\Model\UrlInterface $url
     */
    public function __construct(
        \FishPig\WordPress\Model\UrlInterface $url,
        \Magento\Framework\HTTP\ClientFactory $httpClientFactory,
        \FishPig\WordPress\App\HTTP\RequestManager\Logger $requestLogger,
        \FishPig\WordPress\App\HTTP\PhpErrorExtractor $phpErrorExtractor,
        array $urlModifiers = []
    ) {
        $this->url = $url;
        $this->httpClientFactory = $httpClientFactory;
        $this->requestLogger = $requestLogger;
        $this->phpErrorExtractor = $phpErrorExtractor;

        foreach ($urlModifiers as $key => $urlModifier) {
            if (false === ($urlModifier instanceof UrlModifierInterface)) {
                throw new \InvalidArgumentException(
                    get_class($urlModifier) . ' does not implement ' . UrlModifierInterface::class
                );
            }
            
            $this->urlModifiers[$key] = $urlModifier;
        }
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
        if (($url = (string)$this->modifyUrl($url)) === '') {
            throw new \FishPig\WordPress\App\Exception('Empty URL in ' . get_class($this) . '::' . $method);
        }

        // phpcs:ignore -- not cryptographic
        $cacheKey = md5($method . strtolower($url ?? '_current'));
        
        if (!isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = $client = $this->httpClientFactory->create();

            $logData = [
                'Date' => date('Y/m/d H:i:s'),
                'Method' => str_pad($method, 4),
                'Status' => '',
                'Error' => '',
                'URL' => $url,
                'IP' => $this->getRemoteAddress(),
                'Current' => $this->url->getCurrentUrl(true)
            ];

            try {
                if ($args === null) {
                    $client->$method($url);
                } else {
                    $client->$method($url, $args);
                }

                if (!in_array($client->getStatus(), $this->getAllowedStatusCodes())) {
                    // Invalid status code found
                    // The below method tries to extract helpful context and throws an exception
                    // If no context can be found, generic status code exception is thrown
                    throw $this->handleInvalidStatusCode($client, $method, $url, $args);
                }
            } catch (\Exception $e) {
                $logData['Error'] = $e->getMessage();
                throw $e;
            } finally {
                $logData['Status'] = $client->getStatus();

                if (self::LOG_DATA) {
                    $logData['Headers'] = $client->getRequestHeaders();
                    $logData['Response'] = $client->getHeaders();
                    $logData['Body'] = $client->getBody();
                }

                $this->requestLogger->logApiRequest($logData);
            }
        }

        return $this->cache[$cacheKey];
    }

    /**
     * @return string|false
     */
    private function getRemoteAddress()
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        ];

        // phpcs:disable -- this is only used debug logging
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                return $_SERVER[$header];
            }
        }
        // phpcs:enable

        return false;
    }
    
    /**
     * @param  string $url
     * @return ?string
     */
    protected function modifyUrl(string $url = null): ?string
    {
        foreach ($this->urlModifiers as $urlModifier) {
            $url = $urlModifier->modifyUrl($url);
        }
        
        return $url;
    }
    
    /**
     * @return array
     */
    protected function getAllowedStatusCodes(): array
    {
        return [
            200,
            404
        ];
    }
    
    /**
     *
     */
    protected function handleInvalidStatusCode(
        ClientInterface $client,
        string $method,
        string $url,
        ?array $args
    ): \Exception {
        if (is_string($client->getBody()) && ($pError = $this->phpErrorExtractor->getError($client->getBody()))) {
            $e = new \FishPig\WordPress\App\HTTP\InvalidStatusException(
                'WordPress Server ' . $client->getStatus() . ' Error: ' . $pError,
                $client->getStatus()
            );
        } else {
            $msg = '';
            if (in_array($client->getStatus(), [301, 302])) {
                $headers = $client->getHeaders();
                $location = $headers['location'] ?? $headers['Location'] ?? false;
                if ($location) {
                    $msg = __(
                        "Invalid HTTP status code %1 (redirect). Redirect URL was %2.",
                        $client->getStatus(),
                        $location
                    );
                }
            }
            
            $e = new \FishPig\WordPress\App\HTTP\InvalidStatusException((string)$msg, $client->getStatus());
        }
        
        return $e->setUrl($url);
    }
}
