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
	 * @var CoreInterface
	 */
	protected $helper;

	/**
	 *
	 */
  public function __construct(Context $context, CoreInterface $helper = null)
  {
	  parent::__construct($context);
	  
	  if ($helper) {
  	  $this->helper = $helper;
	  }
  }

	/**
	 * @reutrn CoreInterface|false
	 */
  public function getHelper()
  {
    return isset($this->helper) ? $this->helper : false;
  }
}
