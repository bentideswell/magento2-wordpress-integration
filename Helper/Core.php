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
  public function __construct(Context $context, array $coreProxies = [])
  {
	  parent::__construct($context);

	  $this->coreProxies = $coreProxies;
  }

	/**
	 * @reutrn CoreInterface|false
	 */
  public function getHelper()
  {
    return count($this->coreProxies) ? $this->coreProxies[key($this->coreProxies)] : false;
  }
}
