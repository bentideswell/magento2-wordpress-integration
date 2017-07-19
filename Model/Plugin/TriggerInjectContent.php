<?php
/**
 * Copyright © 2016 FishPig. All rights reserved. http://fishpig.co.uk/magento-2/wordpress-integration/
 */
namespace FishPig\WordPress\Model\Plugin;

/**
 * Class TriggerInjectContent
 */
class TriggerInjectContent
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registry
    ) {
        $this->eventManager = $eventManager;
        $this->registry = $registry;
    }

    /**
     * Trigger inject content if necessary
     * This only runs it on actual layouts, so won't run on non-block related AJAX
     * or any API requests
     *
     * @param \Magento\Framework\View\Result\Layout $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function aroundRenderResult(
        \Magento\Framework\View\Result\Layout $subject,
        callable $proceed,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $result = $proceed($response);

        // Did we run shortcodes that need InjectContent?
        // If not, we don't need to inject anything so don't do anything
        if (!$this->registry->registry('fishpig_wordpress_requires_injectcontent')) {
            return $result;
        }

        $transport = new \Magento\Framework\DataObject([
            'output' => $response->getBody(),
        ]);

        $this->eventManager->dispatch(
            'fishpig_wordpress_injectcontent',
            ['transport' => $transport]
        );

        $response->setBody($transport->getOutput());
        return $result;
    }
}
