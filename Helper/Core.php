<?php
/**
 *
 */
namespace FishPig\WordPress\Helper;

use FishPig\WordPress\Helper\CoreInterface;

class Core extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var CoreInterface
     */
    protected $helper;

    /**
     * @var string
     */
    protected $helperClassName;
    
    /**
     *
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context, 
        array $coreHelpers = [], 
        $preferentialModule = 'FishPig_WordPress_PluginShortcodeWidget'
    ) {
        parent::__construct($context);

        $this->helperClassName = false;
        
        if ($preferentialModule && isset($coreHelpers[$preferentialModule])) {
            $coreHelpers = [$coreHelpers[$preferentialModule]];
        }

        if ($coreHelpers) {
            $this->helperClassName = array_shift($coreHelpers);
            
            if (is_object($this->helperClassName)) {
                $this->helper = $this->helperClassName;
                $this->helperClassName = get_class($this->helperClassName);
            }
        }
    }

    /**
     *
     */
    public function hasHelper()
    {
        return $this->helperClassName !== false;
    }
    
    /**
     * @return CoreInterface|false
     */
    public function getHelper()
    {
        if ($this->helper === null) {
            $this->helper = false;
            
            if ($this->hasHelper()) {
                $this->helper = \Magento\Framework\App\ObjectManager::getInstance()->get($this->helperClassName);
            }
        }

        return $this->helper;
    }

    /**
     *
     */
    public function __call($name, $arguments)
    {
        if ($this->hasHelper()) {
            if ($coreHelper = $this->getHelper()) {
                return call_user_func_array([$coreHelper, $name], $arguments);
            }
        }

        throw new \Exception(sprintf('Call to undefined method %s::%s()', __CLASS__, $name));
    }
}
