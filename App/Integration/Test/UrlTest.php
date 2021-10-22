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
        \Magento\Framework\App\Route\Config $routeConfig
    ) {
        $this->url = $url;
        $this->routeConfig = $routeConfig;
    }

    /**
     * @return void
     */
    public function runTest(): void
    {
        /*
        if (!$this->theme->isThemeIntegrated()) {
            return $this;
        }*/

        $magentoUrl = $this->url->getMagentoUrl();
        $homeUrl = $this->url->getHomeUrl();
        $siteUrl = $this->url->getSiteUrl();
        $isRoot = $this->url->isRoot();

        if ($homeUrl === $siteUrl) {
            throw new IntegrationFatalException(
                sprintf(
                    'Your WordPress Home URL matches your Site URL (%s). Your SiteURL should be the WordPress installation URL and the WordPress Home URL should be the integrated blog URL.', 
                    $siteUrl
                )
            );
        }

        if ($isRoot) {
            if ($homeUrl !== $magentoUrl) {
                throw new IntegrationFatalException(
                    sprintf('Your home URL (%s) is incorrect and should match your Magento URL. Change to. %s', $homeUrl, $magentoUrl)
                );
            }
        } else {
            if (strpos($homeUrl, $magentoUrl) !== 0) {
                throw new IntegrationFatalException(
                    sprintf('Your home URL (%s) is invalid as it does not start with the Magento base URL (%s).', $homeUrl, $magentoUrl)
                );
            }

            if ($homeUrl === $magentoUrl) {
                throw new IntegrationFatalException(
                    'Your WordPress Home URL matches your Magento URL. Try changing your Home URL to something like ' . $magentoUrl . '/blog'
                );
            }

            $this->validateBlogRouteAgainstFrontNames($magentoUrl, $homeUrl);
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

                IntegrationException::throwException(
                    'The ' . $module . ' module uses \'' . $blogRoute . '\' as it\'s frontName. Either fully disable this module or change your WordPress Home URL.'
                );
            } else {
                $moduleString = implode(', ', array_slice($modules, 0, -1)). ' and ' . implode('', array_slice($modules, -1));

                IntegrationException::throwException(
                    'The modules ' . $moduleString . ' use \'' . $blogRoute . '\' as their frontName. Either fully disable these modules or change your WordPress Home URL.'
                );
            }
        }
    }
}
