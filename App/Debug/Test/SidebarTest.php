<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

use FishPig\WordPress\App\Debug\TestPool;

class SidebarTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    public function __construct(
        \FishPig\WordPress\Model\WidgetRepository $widgetRepository,
        \Magento\Framework\View\Layout $layout
    ) {
        $this->widgetRepository = $widgetRepository;
        $this->layout = $layout;
    }

    /**
     *
     */
    public function run(array $options = []): void
    {
        if (!isset($options[TestPool::RUN_BLOCK_TESTS]) || $options[TestPool::RUN_BLOCK_TESTS] !== true) {
            return;
        }

        $this->layout->createBlock(\FishPig\WordPress\Block\Sidebar::class)->toHtml();    

        // Widget Blocks
        foreach ($this->widgetRepository->getAll() as $widgetName => $widgetClass) {
            $this->layout->createBlock($widgetClass)->setWidgetName($widgetName)->setWidgetIndex(1)->toHtml();
        }
    }
}
