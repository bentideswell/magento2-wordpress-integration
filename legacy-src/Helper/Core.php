<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Helper;

class Core extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     *
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \FishPig\WordPress\App\Integration\Mode $integrationMode
    ) {
        $this->integrationMode = $integrationMode;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function hasHelper(): bool
    {
        return true;
    }
    
    /**
     * @return self
     */
    public function getHelper(): self
    {
        return $this;
    }
    
    /**
     * @param  \Closure $callback
     * @return mixed
     */
    public function simulatedCallback(\Closure $callback, array $params = [])
    {
        if (!$this->integrationMode->isLocalMode()) {
            throw new \FishPig\WordPress\App\Exception('Cannot run WP code unless in local mode.');
        }
        
        return null;
    }

    /**
     * @return string|false
     */
    public function getHtml()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return false;
    }
}
