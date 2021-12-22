<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Post;

class Attachment extends \FishPig\WordPress\Model\AbstractMetaModel
{
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
        $this->serializer = $wpContext->getSerializer();
        parent::__construct($context, $registry, $wpContext, $metaDataProvider, $resource, $resourceCollection, $data);
    }
    
    /**
     * @return void
     */
    public function _construct()
    {
        $this->setPostType('attachment');

        parent::_construct();
    }

    /**
     * @return self
     */
    protected function _afterLoad()
    {
        if ((int)$this->getId()) {
            if ($metaData =$this->getMetaValue('metadata')) {
                $this->addData(
                    $this->serializer->unserialize($metaData)
                );
            }
        }

        return parent::_afterLoad();
    }

    /**
     * @param  string $key
     * @param  mixed  $default = null
     * @return mixed
     */
    public function getMetaValue(string $key, $default = null)
    {
        return parent::getMetaValue(
            strpos($key, '_wp_attachment_') === false ? '_wp_attachment_' . $key : $key
        );
    }
}
