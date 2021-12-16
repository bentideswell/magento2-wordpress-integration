<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Url;

class SiteUrlResolver implements \FishPig\WordPress\Api\App\Url\UrlInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\OptionRepository $optionRepository
    ) {
        $this->optionRepository = $optionRepository;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->optionRepository->get('siteurl');
    }
}
