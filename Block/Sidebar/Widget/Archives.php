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
			$dates = $this->_factory->getFactory('Archive')->create()->getResource()->getDatesForWidget();
			$archiveCollection = array();
			
			foreach($dates as $date) {
				$archiveCollection[] = $this->_factory->getFactory('Archive')->create()->load($date['archive_date'])->setPostCount($date['post_count']);
			}

			$this->_archiveCollection = $archiveCollection;
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
			$dates[$it] = __($part);
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
		return $this->_registry->registry('wordpress_archive');
	}
	
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return __('Archives');
	}
	
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar/widget/archives.phtml');
		}
		
		return parent::_beforeToHtml();
	}

}
