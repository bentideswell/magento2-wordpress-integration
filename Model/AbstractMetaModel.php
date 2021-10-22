<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

abstract class AbstractMetaModel extends AbstractModel
{
    /**
     * @var \FishPig\WordPress\Api\Data\MetaDataProviderInterface
     */
    private $metaDataProvider = null;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \FishPig\WordPress\Model\Context $wpContext,
        \FishPig\WordPress\Api\Data\MetaDataProviderInterface $metaDataProvider,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->metaDataProvider = $metaDataProvider;
        parent::__construct($context, $registry, $wpContext, $resource, $resourceCollection, $data);
    }

    /**
     * @param  string $key
     * @param  mixed $default = null
     * @return mixed
     */
    public function getMetaValue(string $key, $default = null)
    {
        return $this->metaDataProvider->getValue($this, $key) ?? $default;
    }
}
