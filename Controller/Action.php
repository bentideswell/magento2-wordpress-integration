<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller;

abstract class Action extends \Magento\Framework\App\Action\Action
{
    /**
     * @const string
     */
    const LAYOUT_HANDLE_DEFAULT = 'wordpress_default';

    /**
     * @const string
     */
    const ENTITY_CURRENT = 'wordpress_entity_current';

    /**
     * @var int
     */
    private $pageStorage = 0;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \FishPig\WordPress\Controller\Action\Context $wpContext
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \FishPig\WordPress\Controller\Action\Context $wpContext
    ) {
        $this->registry = $wpContext->getRegistry();
        $this->url = $wpContext->getUrl();
        
        parent::__construct($context);

        // Used to prevent some installations overwriting this
        // We will set it again in self::execute
        $this->pageStorage = (int)$this->getRequest()->getParam('page');
    }

    /**
     * @param Magento\Framework\App\RequestInterface $request
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (isset($this->pageStorage) && $this->pageStorage > 0) {
            $this->getRequest()->setParam('page', $this->pageStorage);
        }

        return parent::dispatch($request);
    }

    /**
     * @param  \Magento\Framework\View\Result\Page $resultPage
     * @param  array $handles
     * @return void
     */
    protected function addLayoutHandles(\Magento\Framework\View\Result\Page $resultPage, array $handles): void
    {
        // Remove the default action layout handle
        // This allows controller to add handles in chosen order
        $resultPage->getLayout()->getUpdate()->removeHandle(
            $resultPage->getDefaultLayoutHandle()
        );

        if (!in_array(self::LAYOUT_HANDLE_DEFAULT, $handles)) {
            array_unshift($handles, self::LAYOUT_HANDLE_DEFAULT);
        }

        foreach (array_unique($handles) as $handle) {
            $resultPage->addHandle($this->cleanLayoutHandle($handle));
        }
    }

    /**
     * @param  string $handle
     * @return string
     */
    private function cleanLayoutHandle($handle): string
    {
        return trim(
            str_replace(
                ['__', '__'],
                '_',
                preg_replace('/[^a-z0-9_]+/', '_', $handle)
            ),
            '_'
        );
    }

    /**
     * @param  array $crumbs
     * @return void
     */
    protected function addBreadcrumbs(array $crumbs): void
    {
        if (!($breadcrumbsBlock = $this->_view->getLayout()->getBlock('breadcrumbs'))) {
            return;
        }

        $crumbs = array_merge(
            $this->getBaseBreadcrumbs(),
            $crumbs
        );
        
        $eventTransport = new \Magento\Framework\DataObject(['breadcrumbs' => $crumbs]);
        
        $this->_eventManager->dispatch('wordpress_breadcrumbs', ['transport' => $eventTransport]);
        
        if ($crumbs = $eventTransport->getBreadcrumbs()) {
            if (count($crumbs) > 1) {
                foreach ($crumbs as $key => $crumb) {
                    if (!isset($crumb['title'])) {
                        $crumb['title'] = $crumb['label'];
                    }
    
                    $breadcrumbsBlock->addCrumb($key, $crumb);
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getBaseBreadcrumbs(): array
    {
        $crumbs = [
            'home' => [
                'label' => (string)__('Home'),
                'title' => (string)__('Go to Home Page'),
                'link' => $this->url->getMagentoUrl()
            ]
        ];

        if (!$this->url->isRoot()) {
            $crumbs['blog'] = [
                'label' => (string)__('Blog'),
                'link' => $this->url->getHomeUrl()
            ];
        }

        return $crumbs;
    }

    /**
     * @return \Magento\Framework\Controller\ResultForwardFactory
     */
    protected function getNoRouteForward()
    {
        return $this->resultFactory->create(
            \Magento\Framework\Controller\ResultFactory::TYPE_FORWARD
        )->setModule(
            'cms'
        )->setController(
            'noroute'
        )->forward(
            'index'
        );
    }
}
