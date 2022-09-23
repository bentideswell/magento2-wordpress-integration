<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Repository;

abstract class AbstractRepository
{
    /**
     *
     */
    abstract public function get($id): \Magento\Framework\DataObject;

    /**
     *
     */
    protected $objectFactory = null;

    /**
     *
     */
    protected function getObjectFactory()
    {
        if ($this->objectFactory === null) {
            throw new \Magento\Framework\Exception\RuntimeException(
                __('Object factory not set in %1.', get_class($this))
            );
        }

        return $this->objectFactory;
    }
}
