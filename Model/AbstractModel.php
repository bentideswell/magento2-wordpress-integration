<?php
/*
 * @category  Fishpig
 * @package		Fishpig_Wordpress
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 * @info			http://fishpig.co.uk/wordpress-integration.html
 */

namespace FishPig\WordPress\Model;

use Magento\Framework\DataObject\IdentityInterface;

/* Constructor Args */
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use FishPig\WordPress\Model\Url;
use FishPig\WordPress\Model\OptionManager;
use FishPig\WordPress\Model\PostFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
/* End of Constructor Args */

abstract class AbstractModel extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
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
	protected $postFactory;

	/*
	 *
	 */
	public function __construct(
	         Context $context, 
	        Registry $registry, 
	             Url $url, 
     OptionManager $optionManager,
       PostFactory $postFactory,
	AbstractResource $resource = null, 
	      AbstractDb $resourceCollection = null, 
	           array $data = []
  ) {
		parent::__construct($context, $registry, $resource, $resourceCollection);	
		
		$this->url           = $url;
		$this->optionManager = $optionManager;
		$this->postFactory   = $postFactory;
	}

	/*
	 *
	 */
	public function getIdentities()
  {
    return [self::CACHE_TAG . '_' . $this->getId()];
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
		return (int)$this->optionManager->getOption('blog_public') === 0
			? 'noindex,nofollow'
			: 'index,follow';
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
		return $this->postFactory->create()->getCollection();
	}
}
