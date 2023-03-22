<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use Magento\Framework\DataObject\IdentityInterface;

abstract class AbstractModel extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    /**
     * @auto
     */
    protected $wpContext = null;

    /**
     * @auto
     */
    protected $data = null;

    /**
     * This allows us to flush all WP cache by flushing this tag
     * @const string
     */
    const CACHE_TAG_WP = 'wordpress';

    /**
     * @var \FishPig\WordPress\Model\UrlInterface
     */
    protected $url;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \FishPig\WordPress\Model\Context $wpContext,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->url = $wpContext->getUrl();
        parent::__construct($context, $registry, $resource, $resourceCollection);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int)parent::getId();
    }

    /**
     * @retur array
     */
    public function getIdentities()
    {
        return [
              self::CACHE_TAG_WP,
            static::CACHE_TAG . '_' . $this->getId()
        ];
    }
}
