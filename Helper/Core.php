<?php
/**
 *
 */
namespace FishPig\WordPress\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use FishPig\WordPress\Helper\CoreInterface;

class Core extends AbstractHelper
{
    /**
     * @var string
     */
    protected $preferentialCoreProxy;

    /**
     * @var array
     */
    protected $coreProxies;

    /**
     * @var CoreInterface
     */
    protected $selectedProxy;

    /**
     *
     */
    public function __construct(Context $context, array $coreProxies = [], $preferentialCoreProxy = 'FishPig_WordPress_PluginShortcodeWidget')
    {
        parent::__construct($context);

        $this->coreProxies = $coreProxies;
        $this->preferentialCoreProxy = $preferentialCoreProxy;
    }

    /**
     * @return CoreInterface|false
     */
    public function getHelper()
    {
        if (!isset($this->selectedProxy)) {
            $this->selectedProxy = false;

            if (count($this->coreProxies) > 0) {
                $this->selectedProxy = isset($this->coreProxies[$this->preferentialCoreProxy]) 
                    ? $this->coreProxies[$this->preferentialCoreProxy] 
                    : $this->coreProxies[key($this->coreProxies)];
            }
        }

        return $this->selectedProxy;
    }

    /**
     *
     */
    public function __call($name, $arguments)
    {
        if ($coreHelper = $this->getHelper()) {
            return call_user_func_array([$coreHelper, $name], $arguments);
        }

        throw new \Exception(sprintf('Call to undefined method %s::%s()', __CLASS__, $name));
    }
}
