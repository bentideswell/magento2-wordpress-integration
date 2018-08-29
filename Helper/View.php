<?php
/*
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Helper;

/* Parent Class */
use Magento\Framework\App\Helper\AbstractHelper;

/* Constructor Args */
use Magento\Framework\App\Helper\Context;
use FishPig\WordPress\Model\ShortcodeManager;
use FishPig\WordPress\Model\OptionManager;

class View extends AbstractHelper
{
	/*
	 * @Var OptionManager
	 */
	protected $optionManager;
	
	/*
	 * @var ShortcodeManager
	 */
	protected $shortcodeManager;

	/*
	 *
	 */
	public function __construct(Context $context, ShortcodeManager $shortcodeManager, OptionManager $optionManager)
	{
		parent::__construct($context);
		
		$this->shortcodeManager = $shortcodeManager;
		$this->optionManager    = $optionManager;
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function canDiscourageSearchEngines()
	{
		return (int)$this->optionManager->getOption('blog_public') === 0;
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
	 * @return 
	 */
	public function renderShortcode($shortcode, $object = null)
	{
		return $this->shortcodeManager->renderShortcode($content, ['object' => $object]);
	}
	
	/*
	 * Formats a Wordpress date string
	 *
	 * @return
	 */
	public function formatDate($date, $format = null, $f = false)
	{
		if ($format == null) {
			$format = $this->getDefaultDateFormat();
		}
		
		/**
		 * This allows you to translate month names rather than whole date strings
		 * eg. "March","Mars"
		 *
		 */
		$len = strlen($format);
		$out = '';
		
		for( $i = 0; $i < $len; $i++) {	
			$out .= __(date($format[$i], strtotime($date)));
		}
		
		return $out;
	}
	
	/*
	 * Formats a Wordpress date string
	 *
	 */
	public function formatTime($time, $format = null)
	{
		if ($format == null) {
			$format = $this->getDefaultTimeFormat();
		}
		
		return $this->formatDate($time, $format);
	}
	
	/*
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
	
	/*
	 * Return the default date formatting
	 *
	 */
	public function getDefaultDateFormat()
	{
		if ($format = $this->_config->getOption('date_format')) {
			return $format;
		}
		
		return 'F jS, Y';
	}
	
	/*
	 * Return the default time formatting
	 *
	 */
	public function getDefaultTimeFormat()
	{
		if ($format = $this->_config->getOption('time_format')) {
			return $format;
		}

		return 'g:ia';
	}
	
	/*
	 * Get the search term
	 *
	 * @return string
	 */
	public function getSearchTerm()
	{
		return $this->_request->getParam('s');
	}
	
	
	/*
	 * If a page is set as a custom homepage, get it's ID
	 *
	 * @return false|int
	 */
	public function getHomepagePageId()
	{
		if ($this->optionManager->getOption('show_on_front') === 'page') {
			if ($pageId = $this->optionManager->getOption('page_on_front')) {
				return $pageId;
			}
		}
		
		return false;
	}
	
	/*
	 * If a page is set as a custom homepage, get it's ID
	 *
	 * @return false|int
	 */
	public function getBlogPageId()
	{
		if ($this->optionManager->getOption('show_on_front') === 'page') {
			if ($pageId = $this->optionManager->getOption('page_for_posts')) {
				return $pageId;
			}
		}
		
		return false;
	}
}
