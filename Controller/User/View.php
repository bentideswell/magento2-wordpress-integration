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
     * @param \FishPig\WordPress\Api\Data\Entity\SeoMetaDataProviderInterface $seoMetaDataProvider
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \FishPig\WordPress\Controller\Action\Context $wpContext,
        \FishPig\WordPress\Model\UserRepository $userRepository,
        \FishPig\WordPress\Api\Data\Entity\SeoMetaDataProviderInterface $seoMetaDataProvider
    ) {
        $this->userRepository = $userRepository;
        $this->seoMetaDataProvider = $seoMetaDataProvider;

        parent::__construct($context, $wpContext);
    }
    
    /**
     *
     */
    public function execute()
    {
        $request = $this->getRequest();

        // This will throw Exception is post does not exist
        $user = $this->userRepository->getByNicename(
            $request->getParam('author')
        );

        $this->registry->register($user::ENTITY, $user);

        // We got here, we must be good.
        $resultPage = $this->resultFactory->create(
            \Magento\Framework\Controller\ResultFactory::TYPE_PAGE
        );

        $this->addLayoutHandles($resultPage, ['wordpress_user_view']);

        $this->seoMetaDataProvider->addMetaData($resultPage, $user);

        return $resultPage;
    }

    /**
     * Get the blog breadcrumbs
     *
     * @return array
     */
    protected function _getBreadcrumbs()
    {
        return array_merge(
            parent::_getBreadcrumbs(),
            [
                'archives' => [
                    'label' => __($this->_getEntity()->getName()),
                    'title' => __($this->_getEntity()->getName())
                ]
            ]
        );
    }
}
