<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration;

class Mode
{
    /**#@+
     * Integration modes
     */
    const MODE_DISABLED = '';
    
    const MODE_LOCAL = 'local';

    const MODE_EXTERNAL = 'external';

    const MODE_API = 'api';

    /**#@-*/
    
    /**
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getMode(): ?string
    {
        return (string)$this->scopeConfig->getValue('wordpress/setup/mode');
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->getMode() === self::MODE_DISABLED;
    }

    /**
     * @return bool
     */
    public function isLocalMode(): bool
    {
        return $this->getMode() === self::MODE_LOCAL;
    }

    /**
     * @return bool
     */
    public function isExternalMode(): bool
    {
        return $this->getMode() === self::MODE_EXTERNAL;
    }

    /**
     * @return bool
     */
    public function isApiMode(): bool
    {
        return $this->getMode() === self::MODE_API;
    }

    /**
     * @return bool
     */
    public function isNoMode(): bool
    {
        return $this->getMode() === null;
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
        return [
            self::MODE_DISABLED => __('Disabled'),
            self::MODE_LOCAL => __('Local'),
            self::MODE_EXTERNAL => __('External'),
            self::MODE_API => __('API'),
        ];
    }
}
