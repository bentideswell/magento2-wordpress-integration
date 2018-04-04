<?php
/**
 * @ 
**/
namespace FishPig\WordPress\Controller\Json;

use \Magento\Framework\App\Action\Context;
use \FishPig\WordPress\Model\App;

class View extends \Magento\Framework\App\Action\Action
{
	/*
	 * @var \FishPig\WordPress\Model\App
	 */
	protected $app;

	/*
	 *
	 * @param  Context $content
	 * @param  App $app
	 * @return void
	 */
  public function __construct(Context $context, App $app)
  {
	  $this->app = $app;
	  
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
		  if (!($coreHelper = $this->app->getCoreHelper())) {
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
