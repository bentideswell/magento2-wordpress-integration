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
     * @param  string $url
     * @return \Magento\Framework\HTTP\ClientInterface
     */
    public function get($url = null): \Magento\Framework\HTTP\ClientInterface
    {
        try {
            return parent::get($url);
        } catch (CurlException $e) {
            throw new IntegrationFatalException(
                sprintf(
                    'Unable to connect to the WordPress API. Curl Error (%d): %s',
                    $e->getCode(),
                    $e->getMessage()
                )
            );
        }
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
        } catch (\InvalidArgumentException $e) {
            $this->throwPhpErrorMessageFromString($str);

            return false;
        } catch (\Exception $e) {
            $this->throwPhpErrorMessageFromString($str);
            throw $e;
        }
    }
    
    /**
     * @param  string $str
     * @return void
     */
    private function throwPhpErrorMessageFromString($str): void
    {
        if (preg_match('/<b>(Fatal error|Warning|Notice|Parse error)<\/b>:(.*)\n/Uis', $str, $m)) {
            throw new \FishPig\WordPress\App\Exception(trim($m[2]));
        }
    }
}
