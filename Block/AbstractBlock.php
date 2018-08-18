<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block;

/* Parent Class */
use Magento\Framework\View\Element\Template;
/* Constructor */
use Magento\Framework\View\Element\Template\Context as Context;
use FishPig\WordPress\Model\ShortcodeManager;
use FishPig\WordPress\Helper\View as ViewHelper;

abstract class AbstractBlock extends Template
{
	/*
	 * @var FilterHelper
	 */
	protected $viewHelper;

	protected $shortcodeManager;
	
  /*
   * Constructor
   *
   * @param Context $context
   * @param App
   * @param array $data
   */
  public function __construct(Context $context, ShortcodeManager $shortcodeManager, ViewHelper $viewHelper, array $data = [])
  {
    parent::__construct($context, $data);

		$this->shortcodeManager = $shortcodeManager;
    $this->viewHelper   = $viewHelper;    
  }

	/*
	 * Parse and render a shortcode
	 *
	 * @param  string $shortcode
	 * @param  mixed  $object = null
	 * @return string
	 */
  public function doShortcode($shortcode, $object = null)
  {
		return $this->shortcodeManager->renderShortcode($content, ['object' => $object]);
  }

	/*
	 * Generate the HTML for the block
	 *
	 * @return string
	 */
	public function toHtml()
	{
		try {
			return parent::toHtml();
		}
		catch (\Exception $e) {
			echo sprintf('<h1>%s</h1><pre>%s</pre>', $e->getMessage(), $e->getTraceAsString());
			exit;
			
			throw $e;
		}
	}
}
