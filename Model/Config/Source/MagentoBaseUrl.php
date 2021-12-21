<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Config\Source;

class MagentoBaseUrl
{
    /**
     * @var string
     */
    const URL_USE_DEFAULT = '';
    const URL_USE_BASE = 'base';
    
    /**
     *
     */
    private $options = [];

    /**
     * @return void
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        
        foreach ($this->getOptions() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        
        return $options;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        $store = $this->storeManager->getStore();
        $storeId = (int)$store->getId();
        
        if (!isset($this->options[$storeId])) {
            $baseUrl = trim($this->storeManager->getStore()->getBaseUrl(), '/');
            
            $this->options[$storeId] = [
                self::URL_USE_DEFAULT => $baseUrl
            ];
            
            if (($pos = strpos($baseUrl, '/', strlen('https://'))) !== false) {
                $this->options[$storeId][self::URL_USE_BASE] = substr($baseUrl, 0, $pos);
            }
        }

        return $this->options[$storeId];
    }
    
    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return count($this->options) > 1;
    }
}
