<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

/* Interface */
use FishPig\WordPress\Api\Data\Entity\ViewableInterface;

/* Constructor Args */
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use FishPig\WordPress\Model\Url;
use FishPig\WordPress\Model\OptionManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
/* End of Constructor Args */

class Search extends AbstractModel implements ViewableInterface
{
	/*
	 * @const string
	 */
	const ENTITY = 'wordpress_search';
	
	/*
	 * @const string
	 */
	const VAR_NAME = 's';

	/*
	 * @const string
	 */
	const VAR_NAME_POST_TYPE = 'post_type';
	
	/*
	 * @var RequestInterface
	 */
	protected $request;
	
	/*
	 *
	 */
	public function __construct(
	         Context $context, 
	        Registry $registry, 
	             Url $url, 
     OptionManager $optionManager,
  RequestInterface $requestInterface,
	AbstractResource $resource = null, 
	      AbstractDb $resourceCollection = null, 
	           array $data = []
  ) {
		parent::__construct($context, $registry, $url, $optionManager, $resource, $resourceCollection);	

		$this->request = $requestInterface;
	}
	
	/*
	 * Get the search term
	 *
	 * @return  string
	 */
  public function getSearchTerm()
  {
		return $this->request->getParam(self::VAR_NAME);
  }

	/*
	 * Get the name of the search
	 *
	 * @return  string
	 */
	public function getName()
	{
		return 'Search results for ' . $this->getSearchTerm();
	}

	/*
	 * Get an array of post types
	 *
	 * @return array
	 */
	public function getPostTypes()
	{
		return $this->request->getParam(self::VAR_NAME_POST_TYPE);	
	}
	
	/*
	 *
	 *
	 * @return  string
	 */
	public function getUrl()
	{
		$extra = '';
		
		if ($postTypes = $this->getPostTypes()) {
			foreach($postTypes as $postType) {
				$extra .= self::VAR_NAME_POST_TYPE . '[]=' . urlencode($postType) . '&';
			}
			
			$extra = '?' . rtrim($extra, '&');
		}
		
		return $this->url->getUrl('search/' . urlencode($this->getSearchTerm()) . '/' . $extra);
	}
}
