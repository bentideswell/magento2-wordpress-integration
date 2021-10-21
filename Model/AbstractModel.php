<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use Magento\Framework\DataObject\IdentityInterface;
use FishPig\WordPress\Api\Data\ViewableInterface;

abstract class AbstractModel extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, ViewableInterface
{
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
     * @retur array
     */
    public function getIdentities()
    {
        return [static::CACHE_TAG . '_' . $this->getId()];
    }
    
    /**
     * @param  string $key
     * @param  mixed $default = null
     * @return mixed
     */
    public function getMetaValue(string $key, $default = null)
    {
        return $this->getResource()->getMetaValue($this, $key, $default);
    }
}
