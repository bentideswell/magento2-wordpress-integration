<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

use FishPig\WordPress\App\Debug\TestPool;

class PostTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    public function __construct(
        \FishPig\WordPress\Model\PostTypeRepository $postTypeRepository,
        \FishPig\WordPress\Model\PostRepository $postRepository,
        \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \FishPig\WordPress\Model\ResourceModel\Post\Permalink $permalinkResource,
        \FishPig\WordPress\Model\UrlInterface $url,
        \Magento\Framework\View\Layout $layout
    ) {
        $this->postTypeRepository = $postTypeRepository;
        $this->postRepository = $postRepository;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->permalinkResource = $permalinkResource;
        $this->url = $url;
        $this->layout = $layout;
    }
    
    public function run(array $options = []): void
    {
        foreach ($this->postTypeRepository->getAll() as $postType) {
            $posts = $this->postCollectionFactory->create()
                ->addIsViewableFilter()
                ->addPostTypeFilter($postType->getPostType())
                ->setPageSize($options[TestPool::ENTITY_LIMIT] ?: 0)
                ->load();

            $this->permalinkResource->getPermalinkSqlColumn($postType->getPostType());
    
            foreach ($posts as $post) {
                $post = $this->postRepository->get($post->getId());
                $taxonomy = $post->getSupportedTaxonomy('category') ?: $post->getSupportedTaxonomy(null);
                
                $post->getId();
                $post->getName();
                $post->isType($post->getPostType());
                $post->isType('random_incorrect');
                $post->getTypeInstance();
                $post->getGuid();
                $post->getExcerpt();
                $post->hasMoreTag();
                $post->getParentTerm($taxonomy ? $taxonomy->getTaxonomy() : 'category');
                $post->getTermCollectionAsString($taxonomy ? $taxonomy->getTaxonomy() : 'category');
                $post->getPreviousPost();
                $post->getNextPost();
                $post->getContent();
                $post->getComments();
                $post->getImage();
                if ((int)$post->getUserId() !== 0) {
                    $post->getUser();
                }
                $post->getPostDate();
                $post->isViewableForVisitor();
                $post->getUrl();
                $post->getParentPost();
                $post->getChildrenPosts();
                $post->hasChildrenPosts();
                $post->getParentPage();
                $post->isFrontPage();
                $post->isPostsPage();
                $post->getPostFormat();
                $post->getLatestRevision();
                $post->isPublic();
                $post->getMetaValue('_wp_page_template');
                $post->getMetaValue('_does_not_exist');
                $post->getMetaValue('does_not_exist');
                
                if ($post->isPublic() && !$post->isFrontPage()) {
                    if ($pathInfo = str_replace($this->url->getHomeUrl(), '', $post->getUrl())) {
                        if (0 === (int)$this->permalinkResource->getPostIdByPathInfo($pathInfo)) {
                            throw new \Exception(
                                sprintf(
                                    'Unable to find post ID from path \'%s\' on post #%d %s.',
                                    $pathInfo,
                                    $post->getId(),
                                    $post->getName()
                                )   
                            );
                        }
                    }
                }
                
                $this->permalinkResource->getParentTermId(
                    $post->getId(),
                    $taxonomy ? $taxonomy->getTaxonomy() : 'category'
                );
                
                if (isset($options[TestPool::RUN_BLOCK_TESTS]) && $options[TestPool::RUN_BLOCK_TESTS] === true) {
                    $this->layout->createBlock(\FishPig\WordPress\Block\Post\View::class)->setPost($post)->toHtml();
                }
            }
        }
    
        // Only needs to be called for 1 post
        $post->getResource();
        $post->getResource()->getPostsOnDayByYearMonth($post->getPostDate('Y/m/d'));
        $post->getCollection()->addStickyPostsToCollection()->load();
        $post->getCollection()->addPostTypeFilter($post->getPostType());
        $post->getCollection()->addPostTypeFilter('*');
        $post->getCollection()->addPostTypeFilter(['*', $post->getPostType()]);
        $post->getCollection()->addPostTypeFilter(['post', 'page']);
        $post->getCollection()->addPostTypeFilter(['post', 'page', 'does_not_Exist']);
    
        $this->permalinkResource->getPermalinkSqlColumn(
            array_keys($this->postTypeRepository->getAll())
        );
    }
}
