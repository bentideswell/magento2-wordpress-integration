<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class OptionRepository
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer = null;

    /**
     * @var \FishPig\WordPress\App\Logger
     */
    private $logger = null;

    /**
     * @var \FishPig\WordPress\App\Option
     */
    private $dataSource = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager = null;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var ?array
     */
    private $autoloadKeys = null;

    /**
     * @param \FishPig\WordPress\App\Option $dataSource
     */
    public function __construct(
        \FishPig\WordPress\App\Option $dataSource,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \FishPig\WordPress\App\Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $autoloadKeys = []
    ) {
        $this->dataSource = $dataSource;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->autoloadKeys = $this->prepareAutoloadKeys($autoloadKeys) ?: null;
    }

    /**
     * @param  string $key
     * @param  mixed  $default = null
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $this->autoload();

        $cacheKey = $this->getCacheKey($key);

        if (!array_key_exists($cacheKey, $this->cache)) {
            $this->cache[$cacheKey] = $this->dataSource->get($key) ?? $default;
        }

        return $this->cache[$cacheKey];
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->dataSource->set($key, $value);

        $cacheKey = $this->getCacheKey($key);

        if (isset($this->cache[$cacheKey]) && $value === null) {
            unset($this->cache[$cacheKey]);
        } else {
            $this->cache[$cacheKey] = $value;
        }
    }

    /**
     * @return []
     */
    public function getUnserialized($key): array
    {
        if ($data = $this->get($key, '')) {
            try {
                return $this->serializer->unserialize($data);
            } catch (\InvalidArgumentException $e) {
                $this->logger->error(
                    sprintf(
                        'WordPress option (option_name=\'%s\') error: %s',
                        $key,
                        $e->getMessage()
                    )
                );
            }
        }

        return [];
    }

    /**
     * @param  string $key
     * @return string
     */
    private function getCacheKey(string $key): string
    {
        return $this->storeManager->getStore()->getId() . '::' . $key;;
    }

    /**
     *
     */
    private function autoload(): void
    {
        if ($this->autoloadKeys === null) {
            return;
        }

        $defaults = array_combine(
            array_keys(array_flip($this->autoloadKeys)),
            array_fill(0, count($this->autoloadKeys), null)
        );

        $values = array_merge(
            $defaults,
            $this->dataSource->getMultiple($this->autoloadKeys)
        );

        // This stop this running again
        $this->autoloadKeys = null;

        foreach ($values as $key => $value) {
            $this->cache[$this->getCacheKey($key)] = $value;
        }
    }

    /**
     *
     */
    public function prepareAutoloadKeys(array $keys): ?array
    {
        $keys['auth_key'] = \FishPig\WordPress\App\HTTP\AuthorisationKey::KEY_OPTION_NAME;
        $keys['remote_hash_token'] = \FishPig\WordPress\App\Theme\Builder::TOKEN_REMOTE_HASH;
        return $keys;
    }
}
