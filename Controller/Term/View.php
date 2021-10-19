<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Term;

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
        \FishPig\WordPress\Model\TermRepository $termRepository,
        \FishPig\WordPress\Api\Data\Entity\SeoMetaDataProviderInterface $seoMetaDataProvider
    ) {
        $this->termRepository = $termRepository;
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
        $term = $this->termRepository->getByNicename(
            (int)$request->getParam('id')
        );

        $this->registry->register($term::ENTITY, $term);

        // We got here, we must be good.
        $resultPage = $this->resultFactory->create(
            \Magento\Framework\Controller\ResultFactory::TYPE_PAGE
        );

        $this->addLayoutHandles(
            $resultPage,
            [
                'wordpress_term_view',
                'wordpress_' . $term->getTaxonomy() . '_view',
                'wordpress_' . $term->getTaxonomy() . '_view_' . $term->getId(),
            ]
        );

        $this->seoMetaDataProvider->addMetaData($resultPage, $term);

        return $resultPage;
    }

    /**
     * Get the blog breadcrumbs
     *
     * @return array
     */
    protected function _getBreadcrumbs()
    {
        $crumbs = parent::_getBreadcrumbs();
        $term = $this->_getEntity();

        if ($taxonomy = $term->getTaxonomyInstance()) {
            $postTypes = $this->factory->get('PostTypeManager')->getPostTypes();

            if (count($postTypes) > 2) {
                foreach ($postTypes as $postType) {
                    if ($postType->hasArchive() && $postType->getArchiveSlug() === $taxonomy->getSlug()) {
                        $crumbs['post_type_archive_' . $postType->getPostType()] = [
                            'label' => __($postType->getName()),
                            'title' => __($postType->getName()),
                            'link' => $postType->getUrl(),
                        ];

                        break;
                    }
                }
            }

            if ($taxonomy->isHierarchical()) {
                $buffer = $term;

                while ($buffer->getParentTerm()) {
                    $buffer = $buffer->getParentTerm();

                    $crumbs['term_' . $buffer->getId()] = [
                        'label' => __($buffer->getName()),
                        'title' => __($buffer->getName()),
                        'link' => $buffer->getUrl(),
                    ];
                }
            }
        }

        $crumbs['term'] = [
            'label' => __($term->getName()),
            'title' => __($term->getName())
        ];

        return $crumbs;
    }
}
