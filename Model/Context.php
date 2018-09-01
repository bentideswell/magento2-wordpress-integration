<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

/* Constructor Args */
use FishPig\WordPress\Model\OptionManager;
use FishPig\WordPress\Model\ShortcodeManager;
use FishPig\WordPress\Model\PostTypeManager;
use FishPig\WordPress\Model\TaxonomyManager;
use FishPig\WordPress\Model\Url;
use FishPig\WordPress\Model\Factory;
use FishPig\WordPress\Helper\Date as DateHelper;
use Magento\Framework\Registry;

class Context
{
	/*
	 *
	 * @var 
	 *
	 */
	protected $optionManager;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $shortcodeManager;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $postTypeManager;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $taxonomyManager;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $url;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $factory;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $dateHelper;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $registry;
	
	/*
	 *
	 *
	 *
	 */
	public function __construct(
  	   OptionManager $optionManager,
    ShortcodeManager $shortcodeManager,
     PostTypeManager $postTypeManager,
     TaxonomyManager $taxonomyManager,
                 Url $url,
             Factory $factory,
          DateHelper $dateHelper,
            Registry $registry
	)
	{
		$this->optionManager    = $optionManager;
		$this->shortcodeManager = $shortcodeManager;
		$this->postTypeManager  = $postTypeManager;
		$this->taxonomyManager  = $taxonomyManager;
		$this->url              = $url;
		$this->factory          = $factory;
		$this->dateHelper       = $dateHelper;
		$this->registry         = $registry;
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getOptionManager()
	{
		return $this->optionManager;
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getShortcodeManager()
	{
		return $this->shortcodeManager;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getTaxonomyManager()
	{
		return $this->taxonomyManager;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getPostTypeManager()
	{
		return $this->postTypeManager;
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getUrl()
	{
		return $this->url;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getFactory()
	{
		return $this->factory;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getDateHelper()
	{
		return $this->dateHelper;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getRegistry()
	{
		return $this->registry;
	}
}
