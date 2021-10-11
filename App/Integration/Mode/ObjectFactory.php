<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Mode;

class ObjectFactory
{
    /**
     * @param  \FishPig\WordPress\App\Integration\Mode $appMode
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $objects = [],
        boolean $throwExceptionIfObjectNotSet = true
    ) {
        $this->appMode = $appMode;
        $this->objectManager = $objectManager;
        $this->objects = $objects;
        $this->throwExceptionIfObjectNotSet = $throwExceptionIfObjectNotSet;
    }

    /**
     * @return Object|false
     */
    public function getObject()
    {
        $mode = $this->appMode->getMode();

        if (!isset($this->objects[$mode])) {
            if ($this->throwExceptionIfObjectNotSet) {
                throw new \Exception('Unable to find object in ' . get_class($this) . '::getObject(' . $mode . ')');
            }
            
            return false;
        }

        return $this->objectManager->get($this->objects[$mode]);
    }
}
