<?php
/*
 *
 */
namespace FishPig\WordPress\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use FishPig\WordPress\Helper\CoreInterface;

class Core extends AbstractHelper
{
	/**
	 * @var array
	 */
	protected $coreProxies;

	/**
	 *
	 */
  public function __construct(Context $context, $coreProxies = [])
  {
	  parent::__construct($context);

	  if (count($coreProxies)) {
  	  $this->coreProxies = $coreProxies;
	  }
  }

	/**
	 * @reutrn CoreInterface|false
	 */
  public function getHelper()
  {
    if (count($this->coreProxies)) {
      return $this->coreProxies[key($this->coreProxies)];
    }
    
    return false;
  }
}
