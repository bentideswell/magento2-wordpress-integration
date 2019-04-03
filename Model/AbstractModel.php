<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model;

use Magento\Framework\DataObject\IdentityInterface;

/* Constructor Args */
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use FishPig\WordPress\Model\Context as WPContext;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
/* End of Constructor Args */

abstract class AbstractModel extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
	/*
	 *
	 */
	protected $wpContext;

	/*
	 *
	 */
	protected $url;
	
	/*
	 *
	 */
	protected $optionManager;
	
	/*
	 * @var PostFactory
	 */
	protected $factory;
	
	/*
	 * @var ShortcodeManager
	 */
	protected $shortcodeManager;
	
	/*
	 * @var DateHelper
	 */
	protected $dateHelper;
	
	/*
	 * @var PostTypeManager	 
	 */
	protected $postTypeManager;
	
	/*
	 * @var TaxonomyManager
	 */
	protected $taxonomyManager;

	/*
	 *
	 */
	public function __construct(
	         Context $context, 
	        Registry $registry, 
         WPContext $wpContext,
	AbstractResource $resource = null, 
	      AbstractDb $resourceCollection = null, 
	           array $data = []
  )
  {
	  $this->wpContext        = $wpContext;
		$this->url              = $wpContext->getUrl();
		$this->optionManager    = $wpContext->getOptionManager();
		$this->factory          = $wpContext->getFactory();
		$this->shortcodeManager = $wpContext->getShortcodeManager();
		$this->postTypeManager  = $wpContext->getPostTypeManager();
		$this->taxonomyManager  = $wpContext->getTaxonomyManager();

		parent::__construct($context, $registry, $resource, $resourceCollection);			
	}

	/*
	 *
	 */
	public function getIdentities()
  {
    return [static::CACHE_TAG . '_' . $this->getId()];
  }
	
	/*
	 * Get the page title
	 *
	 * @return string
	 */
	public function getPageTitle()
	{
		return sprintf('%s | %s', $this->getName(), $this->getBlogName());
	}
	
	/*
	 * Get the image
	 *
	 * @return false|string|FishPig\WordPress\Model\Image
	 */
	public function getImage()
	{
		return false;
	}
	
	/*
	 * Get the content
	 *
	 * @return string
	 */
	public function getContent()
	{
		return '';
	}
	
	/*
	 * Get the meta description
	 *
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
	
	/*
	 * Get the meta keywords
	 *
	 * @return string
	 */
	public function getMetaKeywords()
	{
		return '';
	}
	
	/*
	 * Get the robots meta value
	 *
	 * @return string
	 */
	public function getRobots()
	{
		return (int)$this->optionManager->getOption('blog_public') === 0 ? 'noindex,nofollow' : 'index,follow';
	}
	
	/*
	 * Get the canonical URL
	 *
	 * @return string
	 */
	public function getCanonicalUrl()
	{
		return $this->getUrl();
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getBlogName()
	{
		return $this->optionManager->getOption('blogname');
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getBlogDescription()
	{
		return $this->optionManager->getOption('blogdescription');
	}
	
	/*
	 *
	 *
	 */
	public function getPostCollection()
	{
		return $this->factory->create('Post')->getCollection();
	}
	
	/*
	 *
	 */
	public function applyPageConfigData($pageConfig)
	{
		if (!$pageConfig) {
			return $this;
		}
		
    $pageConfig->getTitle()->set($this->getPageTitle());
    $pageConfig->setDescription($this->getMetaDescription());	
    $pageConfig->setKeywords($this->getMetaKeywords());

		#TODO: Hook this up so it displays on page
		$pageConfig->setRobots($this->getRobots());

    if ($pageMainTitle = $this->wpContext->getLayout()->getBlock('page.main.title')) {
      $pageMainTitle->setPageTitle($this->getName());
    }
      
		if ($this->getCanonicalUrl()) {
			$pageConfig->addRemotePageAsset($this->getCanonicalUrl(), 'canonical', ['attributes' => ['rel' => 'canonical']]);
		}
	
    return $this;
	}
}
