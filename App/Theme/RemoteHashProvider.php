<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

class RemoteHashProvider implements \FishPig\WordPress\Api\App\Theme\HashProviderInterface
{
    /**
     * @param \FishPig\WordPress\Model\OptionRepository $optionRepository
     */
    public function __construct(
        \FishPig\WordPress\Model\OptionRepository $optionRepository
    ) {
        $this->optionRepository = $optionRepository;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->optionRepository->get('fishpig-theme-hash', '');
    }
}
