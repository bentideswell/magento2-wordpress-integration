<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Mode\External;

class Config implements \FishPig\WordPress\Api\App\ResourceConnection\ConfigRetrieverInterface
{
    /**
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @return array
     */
    public function getDatabaseConfig(): array
    {
        if ($config = (array)$this->scopeConfig->getValue('wordpress/mode_external_db')) {
            if (!empty($config['password'])) {
                $config['password'] = $this->encryptor->decrypt($config['password']);
            }

            return $config;
        }

        return [];
    }
}
