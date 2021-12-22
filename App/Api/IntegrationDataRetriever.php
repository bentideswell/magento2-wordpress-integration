<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Api;

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
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->restRequestManager = $restRequestManager;
        $this->storeManager = $storeManager;
        $this->cache = $cache;
        $this->serializer = $serializer;
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
            throw new \FishPig\WordPress\App\Api\Exception\MissingApiDataException(
                'Unable to get ' . $key . ' from API data.'
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

        if ($data = $this->restRequestManager->getJson('/fishpig/v1/data')) {
            $this->cache->saveApiData(
                $this->serializer->serialize($data),
                $cacheKey,
                [self::CACHE_TAG]
            );
        }

        return $data;
    }
}
