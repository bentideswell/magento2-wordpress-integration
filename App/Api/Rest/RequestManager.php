<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Api\Rest;

use FishPig\WordPress\App\HTTP\InvalidStatusException;
use FishPig\WordPress\App\HTTP\CurlException;
use FishPig\WordPress\App\Integration\Exception\IntegrationFatalException;

class RequestManager extends \FishPig\WordPress\App\HTTP\RequestManager
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\UrlInterface $url,
        \Magento\Framework\HTTP\ClientFactory $httpClientFactory,
        \FishPig\WordPress\App\HTTP\RequestManager\Logger $requestLogger,
        \FishPig\WordPress\App\HTTP\PhpErrorExtractor $phpErrorExtractor,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \FishPig\WordPress\Model\NetworkInterface $network,
        \FishPig\WordPress\App\Cache $cache,
        array $urlModifiers = []
    ) {
        $this->network = $network;
        $this->serializer = $serializer;
        $this->cache = $cache;
        parent::__construct($url, $httpClientFactory, $requestLogger, $phpErrorExtractor, $urlModifiers);
    }

    public function getJsonCached(string $endpoint, string $cacheId)
    {
        $cacheId = $this->network->getBlogId() . '::' . $cacheId;

        if ($data = $this->cache->load($cacheId)) {
            $data = $this->serializer->unserialize($data);
        } else {
            $data = $this->getJson($endpoint);
            
            $this->cache->save($this->serializer->serialize($data), $cacheId);
        }
        
        return $data;
    }

    /**
     * @param  string $endpoint
     * @return []|false
     */
    public function getJson(string $endpoint)
    {
        return $this->makeJsonRequest('GET', $endpoint);
    }

    /**
     * @param  string $endpoint
     * @return []|false
     */
    public function postJson(string $endpoint, array $data = [])
    {
        return $this->makeJsonRequest('POST', $endpoint, $data);
    }

    /**
     * @param  string $endpoint
     * @return []|false
     */
    private function makeJsonRequest($method, string $endpoint, array $data = [])
    {
        if ($method === 'GET') {
            $httpResponse = $this->get($endpoint);
        } elseif ($method === 'POST') {
            $httpResponse = $this->post($endpoint, $data);
        } else {
            throw new \InvalidArgumentException('Unknown HTTP method (' . $method . ') specified.');
        }

        try {
            return $this->serializer->unserialize($httpResponse->getBody());
        } catch (\InvalidArgumentException $e) {
            $errorMessage = $this->extractPhpErrorMessage($httpResponse->getBody()) ?? $e->getMessage();
            throw new \FishPig\WordPress\App\Exception(
                'Unable to parse JSON response for ' . $this->modifyUrl($endpoint) . '. Error was: ' . $errorMessage
            );
        } catch (\Exception $e) {
            $errorMessage = $this->extractPhpErrorMessage($httpResponse->getBody()) ?? $e->getMessage();
            throw new \FishPig\WordPress\App\Exception(
                'Unable to parse JSON response for ' . $this->modifyUrl($endpoint) . '. Error was: ' . $errorMessage
            );
        }
    }

    /**
     * @inheritDoc
     */
    protected function handleInvalidStatusCode(
        \Magento\Framework\HTTP\ClientInterface $client,
        string $method,
        string $url,
        ?array $args
    ): \Exception {
        if ($client->getStatus() !== 401 || strpos($client->getBody(), '{') === false) {
            return parent::handleInvalidStatusCode($client, $method, $url, $args);
        }

        try {
            if ($json = $this->serializer->unserialize($client->getBody())) {
                if (!empty($json['message'])) {
                    return new \FishPig\WordPress\App\HTTP\InvalidStatusException(
                        'WP Rest Error: ' . $json['message'],
                        $client->getStatus()
                    );
                }
            }
        } catch (\InvalidArgumentException $e) {
            return parent::handleInvalidStatusCode($client, $method, $url, $args);
        } catch (\Exception $e) {
            return parent::handleInvalidStatusCode($client, $method, $url, $args);
        }
    }

    /**
     * @return array
     */
    protected function getAllowedStatusCodes(): array
    {
        return [200];
    }
}
