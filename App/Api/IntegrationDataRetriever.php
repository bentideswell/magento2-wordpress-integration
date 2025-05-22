<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Api;

use FishPig\WordPress\App\Api\Exception\MissingApiDataException;
use Magento\Framework\Lock\LockManagerInterface;

class IntegrationDataRetriever
{
    /**
     *
     */
    const LOCK_NAME = 'fishpig_wordpress_api_init';

    /**
     *
     */
    private $restRequestManager = null;

    /**
     *
     */
    private $storeManager = null;

    /**
     *
     */
    private $cache = null;

    /**
     *
     */
    private $serializer = null;

    /**
     *
     */
    private $url = null;

    /**
     *
     */
    private $isDataRequestEnabled = null;

    /**
     * @const string
     */
    const CACHE_TAG = 'fishpig-wordpress-api-data';

    /**
     * @var array
     */
    private $data = [];

    /**
     *
     */
    private $lockManager = null;

    /**
     * @param \FishPig\WordPress\App\Api\Rest\Client $apiClient
     */
    public function __construct(
        \FishPig\WordPress\App\Api\Rest\RequestManager $restRequestManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \FishPig\WordPress\App\Cache $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \FishPig\WordPress\Model\UrlInterface $url,
        LockManagerInterface $lockManager,
        bool $isDataRequestEnabled = false
    ) {
        $this->restRequestManager = $restRequestManager;
        $this->storeManager = $storeManager;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->url = $url;
        $this->lockManager = $lockManager;
        $this->isDataRequestEnabled = $isDataRequestEnabled;
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
        if ($this->isDataRequestEnabled === false) {
            return ['time' => time()];
        }

        $cacheKey = 'integration-data-' . $storeId . '-3';

        if ($data = $this->cache->load($cacheKey)) {
            // Data is already in cache
            return $this->serializer->unserialize($data);
        }

        if ($this->lockManager->isLocked(self::LOCK_NAME)) {
            // Another process is already getting this data so we wait
            $lockWait = 600;
            do {
                usleep(100000);
            }  while ($this->lockManager->isLocked(self::LOCK_NAME) && $lockWait-- > 0);

            if ($this->lockManager->isLocked(self::LOCK_NAME)) {
                throw new \RuntimeException(
                    'FishPig API data retrieval is locked by another process and the timeout was reached.'
                );
            }
        }

        if ($data = $this->cache->load($cacheKey)) {
            // After waiting for locking process to complete, we now have data
            // from the cache
            return $this->serializer->unserialize($data);
        }

        try {
            // We are first process to try and get cached data so try to get a lock
            if (!$this->lockManager->lock(self::LOCK_NAME, 30)) {
                // Lock failed so throw an exception
                throw new \RuntimeException(
                    sprintf(
                        'Unable to establish lock "%s"',
                        self::LOCK_NAME
                    )
                );
            }

            // Lock is established, so let's send those API requests.

            // This fires to check that API is actually available
            // This is required because data request has authentication so may fail
            // because of invalid auth token rather than api not being available
            $this->sayHello();

            // Now let's get the API data
            if ($data = $this->restRequestManager->getJson('/fishpig/v1/data')) {
                // Cache the API data. When we unlock, this will be available to
                // other processes requesting this data
                $this->cache->save(
                    $this->serializer->serialize($data),
                    $cacheKey
                );

                return $data;
            }
        } finally {
            // This unlocks the process
            $this->lockManager->unlock(self::LOCK_NAME);
        }

        return ['_error' => __METHOD__ . '::' . __LINE__];
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
