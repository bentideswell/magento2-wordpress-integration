<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Api\Rest;

use FishPig\WordPress\App\Http\InvalidStatusException;
use FishPig\WordPress\App\Http\InvalidResponseBodyException;

class RequestManager extends \FishPig\WordPress\App\HTTP\RequestManager
{
    public function __construct(
        \FishPig\WordPress\Model\UrlInterface $url,
        \Magento\Framework\HTTP\ClientFactory $httpClientFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
        parent::__construct($url, $httpClientFactory);
    }
    
    /**
     * @param  string $url
     * @return \Magento\Framework\HTTP\ClientInterface
     */
    public function get($url = null): \Magento\Framework\HTTP\ClientInterface
    {
        if ($url === null) {
            throw new \Exception('Invalid URL given.');
        }

        return parent::get($this->url->getRestUrl($url));
    } 
    
    /**
     * @param  string $endpoint
     * @return []|false
     */
    public function getJson(string $endpoint)
    {
        $httpResponse = $this->get($endpoint);
  
        if ($httpResponse->getStatus() !== 200) {
            
            if ($data = $this->parseJson($httpResponse->getBody())) {
                if (isset($data['code'], $data['message'])) {
                    throw new InvalidStatusException(
                        (string)__(
                            '%1: %2 Status = %3. Endpoint = %4',
                            $data['code'],
                            $data['message'],
                            $httpResponse->getStatus(),
                            $endpoint
                        )
                    );
                }
            }
            
            throw new InvalidStatusException(
                (string)__(
                    'Error trying to get JSON endpoint %1. Status = %2',
                    $endpoint,
                    $httpResponse->getStatus()
                )
            );
        }

        return $this->parseJson($httpResponse->getBody());
    }
    
    /**
     * @param  string $str
     * @return []|false
     */
    private function parseJson($str)
    {
        try {
            return $this->serializer->unserialize($str);
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}
