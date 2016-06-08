<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model;

use \FishPig\WordPress\Api\Data\Entity\ViewableInterface;

class Archive extends \FishPig\WordPress\Model\AbstractModel implements ViewableInterface
{
	public function _construct()
	{
		$this->_init('\FishPig\WordPress\Model\ResourceModel\Archive');
	}
	
	public function getName()
	{
		return $this->_getData('name');
		return $this->getApp()->translateDate($this->_getData('name'));
	}
	
	/**
	 * Load an archive model by it's YYYY/MM
	 * EG: 2010/06
	 *
	 * @param string $value
	 */
	public function load($modelId, $field = NULL)
	{
		$this->setId($modelId);
		
		if (strlen($modelId) == 7) {
			$this->setName(date('F Y', strtotime($modelId.'/01 01:01:01')));
			$this->setDateString(strtotime(str_replace('/', '-', $modelId) . ' 01:01:01'));
		}
		else if (strlen($modelId) === 4) {
			$this->setName(date('Y', strtotime($modelId.'-01-01 01:01:01')));
			$this->setDateString(strtotime(str_replace('/', '-', $modelId) . '-01-01 01:01:01'));
			
		}
		else {
			$this->setName(date('F j, Y', strtotime($modelId.' 01:01:01')));
			$this->setDateString(strtotime(str_replace('/', '-', $modelId) . '-01 01:01:01'));
			$this->setIsDaily(true);
		}
		
		return $this;
	}

	/**
	 * Get a date formatted string
	 *
	 * @param string $format
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
		return rtrim($this->_wpUrlBuilder->getUrl($this->getId()), '/') . '/';
	}
	
	/**
	 * Determine whether posts exist for this archive
	 *
	 * @return bool
	 */
	public function hasPosts()
	{
		if ($this->hasData('post_count')) {
			return $this->getPostCount() > 0;
		}

		return $this->getPostCollection()->count() > 0;
	}
	
	/**
	 * Retrieve a collection of blog posts
	 *
	 * @return \FishPig_WordPress\Model\ResourceModel\Post\Collection
	 */
	public function getPostCollection()
	{
		if (!$this->hasPostCollection()) {
			$collection = $this->getPostCollection()
				->addIsViewableFilter()
				->addArchiveDateFilter($this->getId(), $this->getIsDaily())
				->setOrderByPostDate();

			$this->setPostCollection($collection);
		}
		
		return $this->getData('post_collection');
	}
	
	/**
	 *
	 *
	 * @return  string
	**/
	public function getContent()
	{
		if (!$this->hasContent()) {
			$this->setContent($this->_config->getOption('blogdescription'));
		}
		
		return $this->_getData('content');
	}
	
	/**
	 *
	 *
	 * @return \FishPig\WordPress\Model\Image
	**/
	public function getImage()
	{
		return false;
	}
	
	/**
	 *
	 *
	 * @return  string
	**/
	public function getPageTitle()
	{
		return $this->getName();
	}

	/**
	 *
	 *
	 * @return  string
	**/	
	public function getMetaDescription()
	{
		return 'homepage meta description';
	}

	/**
	 *
	 *
	 * @return  string
	**/
	public function getMetaKeywords()
	{
		return 'blog,homepage,wordpress,fishpig';
	}
	
	/**
	 *
	 *
	 * @return  string
	**/
	public function getRobots()
	{
		return 'index,follow';
	}
	
	/**
	 *
	 *
	 * @return  string
	**/
	public function getCanonicalUrl()
	{
		return $this->getUrl();
	}
}
