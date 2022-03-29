<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Api;

use FishPig\WordPress\App\Api\Exception\MissingApiDataException;

class IntegrationDataRetriever
{
    /**
     * @const string
     */
    const CACHE_TAG = 'fishpig-wordpress-api-data';

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param \FishPig\WordPress\App\Api\Rest\Client $apiClient
     */
    public function __construct(
        \FishPig\WordPress\App\Api\Rest\RequestManager $restRequestManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \FishPig\WordPress\App\Cache $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \FishPig\WordPress\Model\UrlInterface $url
    ) {
        $this->restRequestManager = $restRequestManager;
        $this->storeManager = $storeManager;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->url = $url;
    }
    
    /**
     * @param $key = null
     * @return mixed
     */
    public function getData($key = null)
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (!isset($this->data[$storeId])) {
            $this->data[$storeId] = $this->loadData($storeId);
        }

        if ($key === null) {
            return $this->data[$storeId];
        }

        if (!isset($this->data[$storeId][$key])) {
            throw new MissingApiDataException(
                (string)__(
                    'Unable to get %1 from API data.',
                    $key
                )
            );
        }

        return $this->data[$storeId][$key];
    }
    
    /**
     * @param  int $storeId
     * @return array
     */
    private function loadData(int $storeId): array
    {
        $cacheKey = 'integration-data-' . $storeId;

        if ($data = $this->cache->load($cacheKey)) {
            return $this->serializer->unserialize($data);
        }

        // This fires to check that API is actually available
        // This is required because data request has authentication so may fail
        // because of invalid auth token rather than api not being available
        $this->sayHello();

        if ($data = $this->restRequestManager->getJson('/fishpig/v1/data')) {
            $this->cache->saveApiData(
                $this->serializer->serialize($data),
                $cacheKey,
                [self::CACHE_TAG]
            );
        }

        return $data;
    }
    
    /**
     * @return void
     */
    private function sayHello(): void
    {
        $helloEndpoint = '/fishpig/v1/hello';
        $helloData = $this->restRequestManager->getJson($helloEndpoint);

        if (!$helloData || !isset($helloData['status'])) {
            throw new MissingApiDataException(
                (string)__(
                    'WordPress API not available. Hello (%1) failed.',
                    $this->url->getRestUrl($helloEndpoint)
                )
            );
        }
    }
}
