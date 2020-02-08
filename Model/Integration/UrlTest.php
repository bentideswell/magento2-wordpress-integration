<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Integration;

use FishPig\WordPress\Model\Theme;
use FishPig\WordPress\Model\Url;
use FishPig\WordPress\Model\Integration\IntegrationException;

class UrlTest
{
    /**
     * @var 
     */
    protected $theme;

    /**
     * @var 
     */
    protected $url;

    /**
     *
     */
    public function __construct(Theme $theme, Url $url)
    {
        $this->theme = $theme;
        $this->url = $url;
    }

    /**
     * @return 
     */
    public function runTest()
    {
        if (!$this->theme->isThemeIntegrated()) {
            return $this;
        }

        $magentoUrl = $this->url->getMagentoUrl();
        $homeUrl    = $this->url->getHomeUrl();
        $siteUrl    = $this->url->getSiteUrl();

        if ($homeUrl === $siteUrl) {
            IntegrationException::throwException(
                sprintf('Your WordPress Home URL matches your Site URL (%s). Your SiteURL should be the WordPress installation URL and the WordPress Home URL should be the integrated blog URL.', $siteUrl)
            );
        }

        if ($this->url->isRoot()) {
            if ($homeUrl !== $magentoUrl) {
                IntegrationException::throwException(
                    sprintf('Your home URL (%s) is incorrect and should match your Magento URL. Change to. %s', $homeUrl, $magentoUrl)
                );
            }
        }
        else {
            if (strpos($homeUrl, $magentoUrl) !== 0) {
                IntegrationException::throwException(
                    sprintf('Your home URL (%s) is invalid as it does not start with the Magento base URL (%s).', $homeUrl, $magentoUrl)
                );
            }

            if ($homeUrl === $magentoUrl) {
                IntegrationException::throwException('Your WordPress Home URL matches your Magento URL. Try changing your Home URL to something like ' . $magentoUrl . '/blog');
            }
        }

        return $this;
    }
}
