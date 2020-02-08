<?php
/**
 *
 */
namespace FishPig\WordPress\Controller;

use Magento\Framework\App\Action\Action as ParentAction;
use Magento\Framework\App\Action\Context;
use FishPig\WordPress\Model\Context as WPContext;

abstract class Action extends ParentAction
{
    /**
     * @var 
     */
    protected $wpContext;

    /**
     * @var 
     */
    protected $registry;

    /**
     * @var 
     */    
    protected $entity;

    /**
     * @var 
     */    
    protected $resultPage;

    /**
     * @var
     */
    protected $url;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @return 
     */
    abstract protected function _getEntity();

    /**
     * @param Context   $context
     * @param WPContext $wpContext
     */
    public function __construct(Context $context, WPContext $wpContext)
    {
        $this->wpContext = $wpContext;
        $this->registry = $wpContext->getRegistry();
        $this->url = $wpContext->getUrl();
        $this->factory = $wpContext->getFactory();

        parent::__construct($context);
    }    

    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->_beforeExecute() === false) {
            return $this->_getNoRouteForward();
        }

        if ($forward = $this->_getForwardForPreview()) {
            return $forward;
        }

        if ($forward = $this->_getForward()) {
            return $forward;
        }

        $this->checkForAmp();

        $this->_initLayout();

        $this->_afterExecute();

        return $this->getPage();
    }

    /**
     *
     */
    protected function _getForward()
    {
        return false;
    }

    /**
     *
     */
    protected function _beforeExecute()
    {
        if (($entity = $this->_getEntity()) === false) {
            return false;
        }

        if ($entity !== null) {
            $this->registry->register($entity::ENTITY, $entity);
        }

        return $this;
    }

    /**
     *
     */
    protected function _initLayout()
    {
        // Remove the default action layout handle
        // This allows controller to add handles in chosen order
        $this->getPage()->getLayout()->getUpdate()->removeHandle($this->getPage()->getDefaultLayoutHandle());

        if ($handles = $this->getLayoutHandles()) {
            foreach($handles as $handle) {
                if ($handle = $this->cleanLayoutHandle($handle)) {
                    if (!is_array($handle)) {
                        $handle = [$handle];
                    }

                    foreach($handle as $h) {
                        $this->getPage()->addHandle($h);
                    }
                }
            }
        }

        $this->getPage()->getConfig()->addBodyClass('is-blog');

        if ($breadcrumbsBlock = $this->_view->getLayout()->getBlock('breadcrumbs')) {        
            if ($crumbs = $this->_getBreadcrumbs()) {
                foreach($crumbs as $key => $crumb) {
                    $breadcrumbsBlock->addCrumb($key, $crumb);
                }
            }
        }

        return $this;
    }

    /**
     * @param  string $handle
     * @return array
     */
    protected function cleanLayoutHandle($handle)
    {
        return [
            $handle, // Legacy handle. Please use cleaned handle below
            trim(str_replace(['__', '__'], '_', preg_replace('/[^a-z0-9_]+/', '_', $handle)), '_')
        ];
    }

    /**
     * Get an array of extra layout handles to apply
     *
     * @return array
     */
    public function getLayoutHandles()
    {
        return ['wordpress_default'];
    }

    /**
     * Get the breadcrumbs
     *
     * @return array
     */
    protected function _getBreadcrumbs()
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
     *
     */
    protected function _afterExecute()
    {
        return $this;
    }

    /**
     * @return
     */
    public function getPage()
    {
        if ($this->resultPage === null) {
            $this->resultPage = $this->resultFactory->create(
                \Magento\Framework\Controller\ResultFactory::TYPE_PAGE
            );
        }

        return $this->resultPage;
    }

    /**
     *
     *
     */
    public function getEntityObject()
    {
        if ($this->entity !== null) {
            return $this->entity;
        }

        return $this->entity = $this->_getEntity();
    }

    /**
     *
     *
     * @return bool
     */
    protected function _canPreview()
    {
        return false;
    }

    /**
     *
     *
     */
    protected function _getForwardForPreview()
    {
        if (!$this->_canPreview()) {
            return false;
        }

        if ($this->getRequest()->getParam('preview') !== 'true') {
            return false;
        }

        $previewId = 0;

        if ($entity = $this->_getEntity()) {
            $previewId = (int)$entity->getId();
            $this->registry->unregister($entity::ENTITY);
        }

        foreach(['preview_id', 'p', 'page_id'] as $previewIdKey) {
            if (0 !== (int)$this->getRequest()->getParam($previewIdKey))    {
                $previewId = (int)$this->getRequest()->getParam($previewIdKey);

                break;
            }
        }

        if ($previewId) {
            return $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)
                ->setModule('wordpress')
                ->setController('post')
                ->setParams(['preview_id' => $previewId])
                ->forward('preview');
        }

        return false;
    }

    /**
     * @return bool
     */
    public function checkForAmp()
    {
        return false;
    }

    /**
     * @return \Magento\Framework\Controller\ResultForwardFactory
     */
    protected function _getNoRouteForward()
    {
        return $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)
            ->setModule('cms')
            ->setController('noroute')
            ->forward('index');
    }
}
