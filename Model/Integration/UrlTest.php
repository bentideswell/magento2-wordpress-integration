<?php
/*
 *
 */
namespace FishPig\WordPress\Model\Integration;

/* Constructor Args */
use FishPig\WordPress\Model\Theme;
use FishPig\WordPress\Model\Url;

/* Misc */
use FishPig\WordPress\Model\Integration\IntegrationException;

class UrlTest
{
	/*
	 *
	 *
	 */
	protected $theme;
	
	/*
	 *
	 *
	 */
	protected $url;

	/*
	 *
	 *
	 */
	public function __construct(Theme $theme, Url $url)
	{
		$this->theme = $theme;
		$this->url   = $url;
	}
	
	/*
	 *
	 *
	 */
	public function runTest()
	{
		if (!$this->theme->isThemeIntegrated()) {
			return $this;
		}

		$magentoUrl = $this->url->getMagentoUrl();

		if ($this->url->getHomeUrl() === $this->url->getSiteurl()) {
			IntegrationException::throwException(
				sprintf('Your WordPress Home URL matches your Site URL (%s). Your SiteURL should be the WordPress installation URL and the WordPress Home URL should be the integrated blog URL.', $this->url->getSiteurl())
			);
		}

		if ($this->url->isRoot()) {
			if ($this->url->getHomeUrl() !== $magentoUrl) {
				IntegrationException::throwException(
					sprintf('Your home URL is incorrect and should match your Magento URL. Change to. %s', $magentoUrl)
				);
			}
		}
		else {
			if (strpos($this->url->getHomeUrl(), $magentoUrl) !== 0) {
				IntegrationException::throwException(
					sprintf('Your home URL (%s) is invalid as it does not start with the Magento base URL (%s).', $this->url->getHomeUrl(), $magentoUrl)
				);
			}
			
			if ($this->url->getHomeUrl() === $magentoUrl) {
				IntegrationException::throwException('Your WordPress Home URL matches your Magento URL. Try changing your Home URL to something like ' . $magentoUrl . '/blog');
			}
		}
		
		return $this;
	}
}
