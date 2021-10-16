<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use Magento\Framework\DataObject\IdentityInterface;
use FishPig\WordPress\Api\Data\Entity\ViewableInterface;

class Archive extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, ViewableInterface
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
        \FishPig\WordPress\App\Url $url,
        \FishPig\WordPress\Helper\Date $dateHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->url = $url;
        $this->dateHelper = $dateHelper;

        parent::__construct($context, $registry, $resource, $resourceCollection);
    }
    
    /**
     * @return
     */
    public function getName()
    {
        return $this->dateHelper->translateDate($this->_getData('name'));
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
     * Get the archive page URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url->getUrlWithFront($this->getId() . '/');
    }

    /**
     * @return bool
     */
    public function hasPosts()
    {
        return $this->hasData('post_count') ? $this->getPostCount() > 0 : count($this->getPostCollection()) > 0;
    }

    /**
     * Retrieve a collection of blog posts
     *
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getPostCollection()
    {
        if (!$this->hasPostCollection()) {
            $collection = parent::getPostCollection()
                ->addIsViewableFilter()
                ->addArchiveDateFilter($this->getId(), $this->getIsDaily())
                ->setOrderByPostDate();

            $this->setPostCollection($collection);
        }

        return $this->getData('post_collection');
    }

    /**
     * @retur array
     */
    public function getIdentities()
    {
        return [static::CACHE_TAG . '_' . $this->getId()];
    }
}
