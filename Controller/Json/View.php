<?php
/**
 * @ 
**/
namespace FishPig\WordPress\Controller\Json;

/* Parent Class */
use Magento\Framework\App\Action\Action;

/* Constructor Args */
use Magento\Framework\App\Action\Context;
use FishPig\WordPress\Helper\Core as CoreHelper;

class View extends Action
{
	/*
	 *
	 * @var CoreHelper
	 *
	 */
	protected $coreHelper;

	/*
	 *
	 *
	 *
	 *
	 */
  public function __construct(Context $context, CoreHelper $coreHelper)
  {
	  $this->coreHelper = $coreHelper;
	  
	  parent::__construct($context);
  }
   
  /**
   * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
   *
   * @return \Magento\Framework\View\Result\Page
   */
  public function execute()
  {
	  try {
		  if (!($coreHelper = $this->coreHelper->getHelper())) {
			  throw new \Exception("No core helpers defined.");
		  }

			if (!$coreHelper->isActive()) {
			  throw new \Exception("Core helper not active.");
			}

			exit;
  	}
  	catch (\Exception $e) {
			return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)
				->setModule('cms')
				->setController('noroute')
				->forward('index');
  	}
  }
}
