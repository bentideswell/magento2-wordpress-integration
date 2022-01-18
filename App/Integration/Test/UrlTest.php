<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Test;

use FishPig\WordPress\App\Integration\Exception\IntegrationRecoverableException;
use FishPig\WordPress\App\Integration\Exception\IntegrationFatalException;

class UrlTest implements \FishPig\WordPress\Api\App\Integration\TestInterface
{
    /**
     * @param \FishPig\WordPress\App\Url $url
     */
    public function __construct(
        \FishPig\WordPress\App\Url $url,
        \Magento\Framework\App\Route\Config $routeConfig,
        \FishPig\WordPress\Model\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->url = $url;
        $this->routeConfig = $routeConfig;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * @return void
     */
    public function runTest(): void
    {
        if (!$this->config->isThemeIntegrationEnabled()) {
            return;
        }

        $magentoUrl = $this->url->getMagentoUrl();
        $homeUrl = rtrim($this->url->getHomeUrl(), '/'); // Trimmed incase WP configured to add trailing slash to home
        $siteUrl = $this->url->getSiteUrl();
        $isRoot = $this->url->isRoot();
        $storeId = (int)$this->storeManager->getStore()->getId();

        if ($homeUrl === $siteUrl) {
            if ($isRoot) {
                throw new IntegrationFatalException(
                    'Invalid WordPress home URL. '
                    . PHP_EOL . 'Home URL should match Magento base URL (' . $magentoUrl . '). '
                    . PHP_EOL . 'Fix with: ' . "\n\n" . $this->getUpdateUrlCommand($magentoUrl)
                );
            }

            throw new IntegrationFatalException(
                'Your home URL matches your site URL. Change your home URL to something like ' . $magentoUrl . '/blog'
            );
        }

        if ($isRoot) {
            if ($homeUrl !== $magentoUrl) {
                throw new IntegrationFatalException(
                    sprintf(
                        'Your home URL (%s) is incorrect and should match your Magento URL. Change to. %s',
                        $homeUrl,
                        $magentoUrl
                    )
                );
            }
        } else {
            if (strpos($homeUrl, $magentoUrl) !== 0) {
                throw new IntegrationFatalException(
                    sprintf(
                        'Your home URL (%s) is invalid as it does not start with the Magento base URL (%s).',
                        $homeUrl,
                        $magentoUrl
                    )
                );
            }

            if ($homeUrl === $magentoUrl) {
                throw new IntegrationFatalException(
                    'Your WordPress Home URL matches your Magento URL.'
                    . ' Try changing your Home URL to something like ' . $magentoUrl . '/blog'
                );
            }

            $this->validateBlogRouteAgainstFrontNames($magentoUrl, $homeUrl);
        }

        if (!$this->url->doUrlProtocolsMatch($magentoUrl, $homeUrl, $siteUrl)) {
            throw new IntegrationFatalException(
                'URL Protocol Mismatch. Your WordPress URLs do not use the same URL protocol as Magento.'
                . ' It is recommended to use https:// for all URLs but what ever you choose, all Magento and'
                . ' WordPress URLs must use the same protocol.'
            );
        }
    }

    /**
     * @return void
     */
    private function validateBlogRouteAgainstFrontNames($magentoUrl, $homeUrl): void
    {
        if (!($blogRoute = trim(substr($homeUrl, strlen($magentoUrl)), '/'))) {
            return;
        }

        // Check for slashes and get first part
        if (($pos = strpos($blogRoute, '/')) !== false) {
            $blogRoute = trim(substr($blogRoute, 0, $pos));
        }

        if (!$blogRoute) {
            return;
        }

        if ($modules = $this->routeConfig->getModulesByFrontName($blogRoute, 'frontend')) {
            if (count($modules) === 1) {
                $module = array_shift($modules);

                throw new IntegrationFatalException(
                    "The $module module uses '$blogRoute' as it's frontName."
                    . " Either fully disable this module or change your WordPress Home URL."
                );
            } else {
                $moduleString = implode(
                    ', ',
                    array_slice($modules, 0, -1)
                ) . ' and ' . implode('', array_slice($modules, -1));

                throw new IntegrationFatalException(
                    "The modules $moduleString use '$blogRoute' as their frontName."
                    . " Either fully disable these modules or change your WordPress Home URL."
                );
            }
        }
    }
    
    private function getUpdateUrlCommand($url, $optionName = 'home'): string
    {
        return sprintf(
            '<strong>bin/magento %s --option %s --value "%s" --store %d</strong>',
            'fishpig:wordpress:set-option',
            $optionName,
            $url,
            (int)$this->storeManager->getStore()->getId()
        );
    }
}
