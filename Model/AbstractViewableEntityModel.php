<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use FishPig\WordPress\Api\Data\Entity\ViewableInterface;

abstract class AbstractViewableEntityModel extends AbstractModel implements IdentityInterface, ViewableInterface
{
    /**
     * @var \FishPig\WordPress\App\Url
     */
    protected $url;
    
    /**
     *
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \FishPig\WordPress\App\Url $url,
        \FishPig\WordPress\App\Option $option,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->url = $url;
        $this->option = $option;
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
     * @return string
     */
    public function getPageTitle()
    {
        return sprintf('%s | %s', $this->getName(), $this->getBlogName());
    }

    /**
     * @return false|string|FishPig\WordPress\Model\Image
     */
    public function getImage()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        if (($content = trim(strip_tags($this->getContent()))) !== '') {
            $max = 155;

            if (strlen($content) > $max) {
                $content = substr($content, 0, $max);
            }

            return $content;
        }

        return $this->getBlogDescription();
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getRobots()
    {
        return (int)$this->option->getOption('blog_public') === 0 ? 'noindex,nofollow' : 'index,follow';
    }

    /**
     * @return string
     */
    public function getCanonicalUrl()
    {
        return $this->getUrl();
    }

    /**
     * @return
     */
    public function getBlogName()
    {
        return $this->option->getOption('blogname');
    }

    /**
     * @return
     */
    public function getBlogDescription()
    {
        return $this->option->getOption('blogdescription');
    }
}
