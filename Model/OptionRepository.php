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
     * @var \FishPig\WordPress\Model\Option
     */
    private $dataSource = null;

    /**
     * @param  \FishPig\WordPress\Model\Option $dataSource
     */
    public function __construct(
        \FishPig\WordPress\App\Option $dataSource
    ) {
        $this->dataSource = $dataSource;
    }

    /**
     * @param  int $id
     * @param  array|string $taxonomies
     * @return FishPig\WordPress\Model\Term
     */
    public function get($key, $default = null)
    {
        return $this->dataSource->get($key, $default);
    }

    /**
     * @return []
     */
    public function getUnserialized($key): array
    {
        if ($data = $this->get($key)) {
            return unserialize($data, [false]);
        }

        return [];
    }

    /**
     * @deprecated since version 3.0
     */
    public function getOption($key, $default = null)
    {
        return $this->get($key, $default);
    }
}
