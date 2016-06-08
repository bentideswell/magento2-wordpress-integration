<?php
/**
 * @category		Fishpig
 * @package		Fishpig_Wordpress
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 * @info			http://fishpig.co.uk/wordpress-integration.html
 */

namespace FishPig\WordPress\Model;

use Magento\Framework\DataObject\IdentityInterface;

abstract class AbstractModel extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
	public function __construct(
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\FishPig\WordPress\Model\Context $wpContext,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		array $data = []
	) {
		parent::__construct($context, $registry, $resource, $resourceCollection);	
		
		$this->_app = $wpContext->getApp();
		$this->_wpUrlBuilder = $wpContext->getUrlBuilder();
		$this->_factory = $wpContext->getFactory();
		$this->_viewHelper = $wpContext->getViewHelper();
		$this->_filter = $wpContext->getFilterHelper();
	}

	public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
	
	/**
	 * Get a collection of posts
	 * Child class should filter posts accordingly
	 *
	 * @return Fishpig_Wordpress_Model_Resource_Post_Collection
	 */
	public function getPostCollection()
	{
		return $this->_factory->getFactory('Post')->create()->getCollection()->setFlag('source', $this);
	}
	
	/**
	 * Get the page title
	 *
	 * @return string
	**/
	public function getPageTitle()
	{
		return sprintf('%s | %s', $this->getName(), $this->_viewHelper->getBlogName());
	}
	
	/**
	 * Get the image
	 *
	 * @return false|string|FishPig\WordPress\Model\Image
	**/
	public function getImage()
	{
		return false;
	}
	
	/**
	 * Get the content
	 *
	 * @return string
	**/
	public function getContent()
	{
		return '';
	}
	
	/**
	 * Get the meta description
	 *
	 * @return string
	**/
	public function getMetaDescription()
	{
		if (($content = trim(strip_tags($this->getContent()))) !== '') {
			$max = 155;
			
			if (strlen($content) > $max) {
				$content = substr($content, 0, $max);
			}
			
			return $content;
		}
		
		return $this->_viewHelper->getBlogDescription();
	}
	
	/**
	 * Get the meta keywords
	 *
	 * @return string
	**/
	public function getMetaKeywords()
	{
		return '';
	}
	
	/**
	 * Get the robots meta value
	 *
	 * @return string
	**/
	public function getRobots()
	{
		return $this->_viewHelper->canDiscourageSearchEngines()
			? 'noindex,nofollow'
			: 'index,follow';
	}
	
	/**
	 * Get the canonical URL
	 *
	 * @return string
	**/
	public function getCanonicalUrl()
	{
		return $this->getUrl();
	}
}
