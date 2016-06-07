<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Sidebar\Widget;

class Archives extends AbstractWidget
{
	/**
	 * Cache for archive collection
	 *
	 * @var null|Varien_Data_Collection
	 */
	protected $_archiveCollection = null;

	/**
	 * Returns a collection of valid archive dates
	 *
	 * @return Varien_Data_Collection
	 */
	public function getArchives()
	{
		if (is_null($this->_archiveCollection)) {
			$table = Mage::helper('wordpress/app')->getTableName('posts');
			$sql = "SELECT COUNT(ID) AS post_count, CONCAT(SUBSTRING(post_date, 1, 4), '/', SUBSTRING(post_date, 6, 2)) as archive_date 
					FROM `" . $table . "` AS `main_table` WHERE (`main_table`.`post_type`='post') AND (`main_table`.`post_status` ='publish') 
					GROUP BY archive_date ORDER BY archive_date DESC";
					
			$dates = Mage::helper('wordpress/app')->getDbConnection()->fetchAll($sql);
			$collection  = new Varien_Data_Collection();
			
			foreach($dates as $date) {
				$obj = Mage::getModel('wordpress/archive')->load($date['archive_date']);
				$obj->setPostCount($date['post_count']);
				$collection->addItem($obj);
			}

			$this->_archiveCollection = $collection;
		}
		
		return $this->_archiveCollection;
	}
	
	/**
	 * Split a date by spaces and translate
	 *
	 * @param string $date
	 * @param string $splitter = ' '
	 * @return string
	 */
	public function translateDate($date, $splitter = ' ')
	{
		$dates = explode($splitter, $date);
		
		foreach($dates as $it => $part) {
			$dates[$it] = $this->__($part);
		}
		
		return implode($splitter, $dates);
	}
	
	/**
	 * Determine whether the archive is the current archive
	 *
	 * @param Fishpig_Wordpress_Model_Archive $archive
	 * @return bool
	 */
	public function isCurrentArchive($archive)
	{
		if ($this->getCurrentArchive()) {
			return $archive->getId() == $this->getCurrentArchive()->getId();
		}

		
		return false;
	}
	
	/**
	 * Retrieve the current archive
	 *
	 * @return Fishpig_Wordpress_Model_Archive
	 */
	public function getCurrentArchive()
	{
		if (!$this->hasCurrentArchive()) {
			$this->setCurrentArchive(Mage::registry('wordpress_archive'));
		}
		
		return $this->getData('current_archive');
	}
	
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return $this->__('Archives');
	}
}
