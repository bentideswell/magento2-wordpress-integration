<?php
/**
 *
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Integration;

use FishPig\WordPress\Model\Integration\IntegrationException;

class UrlTest
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\Theme $theme, 
        \FishPig\WordPress\Model\Url $url,
        \Magento\Framework\App\Route\Config $routeConfig
    ) {
        $this->theme = $theme;
        $this->url = $url;
        $this->routeConfig = $routeConfig;
    }

    /**
     * @return
     */
    public function runTest()
    {
        if (!$this->theme->isThemeIntegrated()) {
            return $this;
        }

        $magentoUrl = $this->getMagentoUrl();
        $homeUrl = $this->getHomeUrl();
        $siteUrl = $this->getSiteUrl();

        if ($homeUrl === $siteUrl) {
            IntegrationException::throwException(
                sprintf(
                    'Your WordPress Home URL matches your Site URL (%s). Your SiteURL should be the WordPress installation URL and the WordPress Home URL should be the integrated blog URL.', 
                    $siteUrl
                )
            );
        }

        if ($this->isRoot()) {
            if ($homeUrl !== $magentoUrl) {
                IntegrationException::throwException(
                    sprintf('Your home URL (%s) is incorrect and should match your Magento URL. Change to. %s', $homeUrl, $magentoUrl)
                );
            }
        } else {
            if (strpos($homeUrl, $magentoUrl) !== 0) {
                IntegrationException::throwException(
                    sprintf('Your home URL (%s) is invalid as it does not start with the Magento base URL (%s).', $homeUrl, $magentoUrl)
                );
            }

            if ($homeUrl === $magentoUrl) {
                IntegrationException::throwException(
                    'Your WordPress Home URL matches your Magento URL. Try changing your Home URL to something like ' . $magentoUrl . '/blog'
                );
            }

            $this->validateBlogRouteAgainstFrontNames($magentoUrl, $homeUrl);
        }

        return $this;
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

    /**
     * @return bool
     */
    public function isRoot(): bool
    {
        return $this->url->isRoot();
    }
    
    /**
     * @return string
     */
    public function getHomeUrl(): string
    {
        return $this->url->getHomeUrl();
    }

    /**
     * @return string
     */
    public function getSiteUrl(): string
    {
        return $this->url->getSiteUrl();
    }

    /**
     * @return string
     */
    public function getMagentoUrl(): string
    {
        return $this->url->getMagentoUrl();
    }
}
