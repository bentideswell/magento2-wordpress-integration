<?php
/*
 *
 */
namespace FishPig\WordPress\Block;

/* Parent Class */
use Magento\Framework\View\Element\Template;

/* Constructor */
use Magento\Framework\View\Element\Template\Context as Context;
use FishPig\WordPress\Model\ShortcodeManager;
use FishPig\WordPress\Model\OptionManager;
use FishPig\WordPress\Model\Url;
use Magento\Framework\Registry;

abstract class AbstractBlock extends Template
{
	/*
	 * @var OptionManager
	 */
	protected $optionManager;
	
	/*
	 * @var ShortcodeManager
	 */
	protected $shortcodeManager;
	
	/*
	 * @var Registry
	 */
	protected $registry;
	
	/*
	 * @var Url
	 */
	protected $url;

  /*
   * Constructor
   *
   * @param Context $context
   * @param App
   * @param array $data
   */
  public function __construct(
	      /* Parent */
  	         Context $context,
  	     /* Local */
  	   OptionManager $optionManager,
    ShortcodeManager $shortcodeManager,
            Registry $registry,
                 Url $url,
			/* Optional */
  	           array $data = []
  )
  {
		$this->optionManager    = $optionManager; 
		$this->shortcodeManager = $shortcodeManager;
		$this->registry         = $registry;
		$this->url              = $url;

    parent::__construct($context, $data);
  }

	/*
	 * Parse and render a shortcode
	 *
	 * @param  string $shortcode
	 * @param  mixed  $object = null
	 * @return string
	 */
  public function renderShortcode($shortcode, $object = null)
  {
		return $this->shortcodeManager->renderShortcode($content, ['object' => $object]);
  }

	/*
	 *
	 */
  public function doShortcode($shortcode, $object = null)
  {
	  return $this->renderShortcode($shortcode, $object);
  }
  
	/*
	 *
	 */
	protected function applyPageConfigData($pageConfig, $entity)
	{
		if (!$pageConfig || !$entity) {
			return $this;
		}
		
    $pageConfig->getTitle()->set($entity->getPageTitle());
    $pageConfig->setDescription($entity->getMetaDescription());	
    $pageConfig->setKeywords($entity->getMetaKeywords());

		#TODO: Hook this up so it displays on page
		$pageConfig->setRobots($entity->getRobots());

    if ($pageMainTitle = $this->_layout->getBlock('page.main.title')) {
      $pageMainTitle->setPageTitle($entity->getName());
    }
      
		if ($entity->getCanonicalUrl()) {
			$pageConfig->addRemotePageAsset($entity->getCanonicalUrl(), 'canonical', ['attributes' => ['rel' => 'canonical']]);
		}
	
    return $this;
	}
}
