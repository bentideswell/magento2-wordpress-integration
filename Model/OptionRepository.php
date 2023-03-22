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
     * @auto
     */
    protected $serializer = null;

    /**
     * @auto
     */
    protected $logger = null;

    /**
     * @var \FishPig\WordPress\App\Option
     */
    private $dataSource = null;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @param \FishPig\WordPress\App\Option $dataSource
     */
    public function __construct(
        \FishPig\WordPress\App\Option $dataSource,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \FishPig\WordPress\App\Logger $logger
    ) {
        $this->dataSource = $dataSource;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @param  string $key
     * @param  mixed  $default = null
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $this->dataSource->get($key) ?? $default;
        }

        return $this->cache[$key];
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->dataSource->set($key, $value);

        if (isset($this->cache[$key]) && $value === null) {
            unset($this->cache[$key]);
        } else {
            $this->cache[$key] = $value;
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
}
