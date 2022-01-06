<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\User;

class View extends \FishPig\WordPress\Controller\Action
{
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \FishPig\WordPress\Controller\Action\Context $wpContext
     * @param \FishPig\WordPress\Model\PostRepository $postRepository,
     * @param \FishPig\WordPress\Api\Controller\Action\SeoMetaDataProviderInterface $seoMetaDataProvider
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \FishPig\WordPress\Controller\Action\Context $wpContext,
        \FishPig\WordPress\Model\UserRepository $userRepository,
        \FishPig\WordPress\Api\Controller\Action\SeoMetaDataProviderInterface $seoMetaDataProvider,
        \FishPig\WordPress\Api\Controller\Action\BreadcrumbsDataProviderInterface $breadcrumbsDataProvider
    ) {
        $this->userRepository = $userRepository;
        $this->seoMetaDataProvider = $seoMetaDataProvider;
        $this->breadcrumbsDataProvider = $breadcrumbsDataProvider;

        parent::__construct($context, $wpContext);
    }
    
    /**
     *
     */
    public function execute()
    {
        $request = $this->getRequest();

        try {
            $user = $this->userRepository->getByNicename($request->getParam('author'));
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $this->getNoRouteForward();
        }

        $this->registry->register($user::ENTITY, $user);

        // We got here, we must be good.
        $resultPage = $this->resultFactory->create(
            \Magento\Framework\Controller\ResultFactory::TYPE_PAGE
        );

        $this->addLayoutHandles($resultPage, ['wordpress_user_view']);

        $this->seoMetaDataProvider->addMetaData($resultPage, $user);
        
        $this->addBreadcrumbs(
            $this->breadcrumbsDataProvider->getData($user)
        );

        return $resultPage;
    }
}
