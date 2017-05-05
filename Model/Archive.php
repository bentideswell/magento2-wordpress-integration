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
	/**
	 *
	**/
	const ENTITY = 'wordpress_archive';

	/**
	 * @const string
	*/
	const CACHE_TAG = 'wordpress_archive';

	/**
	 *
	**/
	public function _construct()
	{
		$this->_init('\FishPig\WordPress\Model\ResourceModel\Archive');
	}

	/**
	 *
	**/	
	public function getName()
	{
		return 'Archives: ' . $this->_getData('name');
		return $this->_app->translateDate($this->_getData('name'));
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
		$extra = '';

		while(strlen($modelId . $extra) < 10) {
			$extra .= '/01';
		}

		if (strlen($modelId) === 7) {
			$format = 'F Y';
		}
		else if (strlen($modelId) === 4) {
			$format = 'Y';
		}
		else {
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
	 * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
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
		return '';
	}
}
