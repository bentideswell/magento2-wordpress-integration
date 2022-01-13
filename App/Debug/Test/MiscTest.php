<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

use FishPig\WordPress\App\Debug\TestPool;

class MiscTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    public function __construct(
        \FishPig\WordPress\Model\Csp\WhitelistPolicyCollector $cspPolicyCollector,
        \Magento\Framework\View\Layout $layout,
        \FishPig\WordPress\Model\Config\Source\MagentoBaseUrl $magentoBaseUrlSelector,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\RequestInterface $request,
        \FishPig\WordPress\Model\Sitemap\ItemProvider $sitemapItemProvider
    ) {
        $this->cspPolicyCollector = $cspPolicyCollector;
        $this->layout = $layout;
        $this->magentoBaseUrlSelector = $magentoBaseUrlSelector;
        $this->appState = $appState;
        $this->request = $request;
        $this->sitemapItemProvider = $sitemapItemProvider;
    }

    /**
     *
     */
    public function run(array $options = []): void
    {
        $this->cspPolicyCollector->collect();
        $this->magentoBaseUrlSelector->toOptionArray();
    
        // Sitemap
        if (count($this->sitemapItemProvider->getItems(1)) === 0) {
            throw new \Exception('Sitemap items empty.');
        }

        if (isset($options[TestPool::RUN_BLOCK_TESTS]) && $options[TestPool::RUN_BLOCK_TESTS] === true) {
            $this->appState->emulateAreaCode(
                'adminhtml',
                function() {
                    $this->request->setParam('section', 'wordpress');
                    $this->layout->createBlock(
                        \FishPig\WordPress\Block\Adminhtml\System\Config\IntegrationStatus::class
                    )->toHtml();
                }
            );
        }
    }
}
