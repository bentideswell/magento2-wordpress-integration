<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration;

use FishPig\WordPress\App\Integration\Exception\InvalidModeException;

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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return string
     */
    public function getMode(): ?string
    {
        return (string)$this->scopeConfig->getValue(
            'wordpress/setup/mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
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
        return false;
    }

    /**
     * @return $this
     */
    public function requireLocalMode($msg = null): self
    {
        if (!$this->isLocalMode()) {
            $mode = $this->getMode();
            throw new InvalidModeException(
                $msg ?? "Invalid mode. Current mode is $mode. Required mode is " . self::MODE_LOCAL . "."
            );
        }
        
        return $this;
    }

    /**
     * @return $this
     */
    public function requireExternalMode($msg = null): self
    {
        if (!$this->isExternalMode()) {
            $mode = $this->getMode();
            throw new InvalidModeException(
                $msg ?? "Invalid mode. Current mode is $mode. Required mode is " . self::MODE_EXTERNAL . "."
            );
        }

        return $this;
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
            self::MODE_EXTERNAL => __('External')
        ];
    }
}
