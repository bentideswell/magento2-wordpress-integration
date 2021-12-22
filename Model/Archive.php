<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Api\Data\ViewableModelInterface;
use FishPig\WordPress\Api\Data\PostCollectionGeneratorInterface;

class Archive extends AbstractModel implements ViewableModelInterface, PostCollectionGeneratorInterface
{
    /**
     * @const string
     */
    const ENTITY = 'wordpress_archive';
    const CACHE_TAG = 'wordpress_archive';

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \FishPig\WordPress\Model\Context $wpContext,
        \FishPig\WordPress\Helper\Date $dateHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->postCollectionFactory = $wpContext->getPostCollectionFactory();
        $this->dateHelper = $dateHelper;
        parent::__construct($context, $registry, $wpContext, $resource, $resourceCollection, $data);
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->dateHelper->translateDate($this->_getData('name'));
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url->getUrlWithFront($this->getId() . '/');
    }
    
    /**
     * Load an archive model by it's YYYY/MM
     * EG: 2010/06
     *
     * @param string $value
     */
    public function load($modelId, $field = null)
    {
        $this->setId($modelId);
        $extra = '';

        while (strlen($modelId . $extra) < 10) {
            $extra .= '/01';
        }

        if (strlen($modelId) === 7) {
            $format = 'F Y';
        } elseif (strlen($modelId) === 4) {
            $format = 'Y';
        } else {
            $format = 'F j, Y';
            $this->setIsDaily(true);
        }

        $this->setName(date($format, strtotime($modelId . $extra . ' 01:01:01')));
        $this->setDateString(strtotime(str_replace('/', '-', $modelId . $extra) . ' 01:01:01'));

        return $this;
    }

    /**
     * Get a date formatted string
     *
     * @param  string $format
     * @return string
     */
    public function getDatePart($format)
    {
        return date($format, $this->getDateString());
    }

    /**
     * @return bool
     */
    public function hasPosts(): bool
    {
        return $this->hasData('post_count') ? $this->getPostCount() > 0 : count($this->getPostCollection()) > 0;
    }

    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getPostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection
    {
        return $this->postCollectionFactory->create()->addPostTypeFilter('post')->addArchiveDateFilter(
            $this->getId(),
            $this->getIsDaily()
        );
    }
    
    /**
     * @return string
     */
    public function getId(): string
    {
        return (string)$this->getData('ID');
    }
}
