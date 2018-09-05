<?php
/*
 *
 */
namespace FishPig\WordPress\Helper;

/* Parent Class */
use Magento\Framework\App\Helper\AbstractHelper;

/* Constructor Args */
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\Dir as ModuleDir;
use FishPig\WordPress\Model\Factory;

class Core extends AbstractHelper
{
	/*
	 *
	 *
	 */
	protected $helper;

	/*
	 *
	 *
	 *
	 */
  public function __construct(Context $context, FullModuleList $fullModuleList, ModuleDir $moduleDir, Factory $factory)
  {
	  $this->fullModuleList = $fullModuleList;
	  $this->moduleDir      = $moduleDir;
	  $this->factory        = $factory;
	  
	  parent::__construct($context);
  }

	/*
	 *
	 *
	 *
	 */
  public function getHelper()
  {
	  if ($this->helper !== null) {
		  return $this->helper;
	  }
	  
	  $this->helper = false;
	  
		foreach($this->fullModuleList->getNames() as $moduleName) {
			if (strpos($moduleName, 'FishPig_WordPress_') !== 0) {
				continue;
			}

			$coreHelperFile = dirname($this->moduleDir->getDir($moduleName, ModuleDir::MODULE_ETC_DIR)) . '/Helper/Core.php';
			
			if (is_file($coreHelperFile)) {
				if ($coreHelper = $this->factory->get('FishPig\\' . str_replace('FishPig_', '', $moduleName) . '\\Helper\\Core\\Proxy')) {
					$this->helper = $coreHelper;
					break;
				}
			}
		}

		return $this->helper;
  }
}
