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
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $urlModifiers = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($url, $httpClientFactory, $requestLogger, $urlModifiers);
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
            throw new \FishPig\WordPress\App\Exception(
                'Unable to parse JSON response for ' . $this->modifyUrl($endpoint) . '. Error was: ' . $e->getMessage()
            );
        } catch (\Exception $e) {
            throw new \FishPig\WordPress\App\Exception(
                'Unable to parse JSON response for ' . $this->modifyUrl($endpoint) . '. Error was: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * @return array
     */
    protected function getAllowedStatusCodes(): array
    {
        return [
            200
        ];
    }
}
