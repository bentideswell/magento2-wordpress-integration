<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\Meta\AbstractMeta;

use \FishPig\WordPress\Api\Data\Entity\ViewableInterface;

class Post extends AbstractMeta implements ViewableInterface
{
    /**
     * @const string
     */
    const ENTITY = 'wordpress_post';

    /**
     * @const string
     */
    const CACHE_TAG = 'wordpress_post';

    /**
     * Event data
     *
     * @var string
     */
    protected $_eventPrefix = 'wordpress_post';
    protected $_eventObject = 'post';

    /**
     *
     */
    public function _construct()
    {
        $this->_init('FishPig\WordPress\Model\ResourceModel\Post');

        return parent::_construct();
    }

    /**
     *
     */
    public function getName()
    {
        return $this->_getData('post_title');
    }

    /**
     *
     */
    public function getMetaDescription()
    {
        if ($this->hasPostExcerpt()) {
            return $this->getData('post_excerpt');
        }

        if ($teaser = $this->_getPostTeaser(false)) {
            return $teaser;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return sprintf('%s | %s', $this->getName(), $this->getBlogName());
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getRobots()
    {
        return (int)$this->optionManager->getOption('blog_public') === 0
            ? 'noindex,nofollow'
            : 'index,follow';
    }

    /**
     * @return string
     */
    public function getCanonicalUrl()
    {
        return $this->getUrl();
    }

    /**
     *
     */
    public function isType($type)
    {
        return $this->getPostType() === $type;
    }

    /**
     *
     */
    public function getTypeInstance()
    {
        if (!$this->hasTypeInstance() && $this->getPostType()) {
            if ($this->getPostType() === 'revision') {
                if ($this->getParentPost()) {
                    $this->setTypeInstance(
                        $this->getParentPost()->getTypeInstance()
                    );
                }
            }
            else if ($typeInstance = $this->postTypeManager->getPostType($this->getPostType())) {
                $this->setTypeInstance($typeInstance);
            }
            else {
                $this->setTypeInstance($this->postTypeManager->getPostType('post'));
            }
        }

        return $this->_getData('type_instance');
    }

    /**
     * Set the categories after loading
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $this->getResource()->preparePosts(array($this));

        return $this;
    }

    /**
     * @return string
     */    
    public function getGuid()
    {
        if ($this->getPostType() === 'page') {
            return $this->url->getUrl() . '?page_id=' . $this->getId();
        }
        else if ($this->getPostType() === 'post') {
            return $this->url->getUrl() . '?p=' . $this->getId();
        }

        return $this->url->getUrl() . '?p=' . $this->getId() . '&post_type=' . $this->getPostType();
    }

    /**
     * Retrieve the post excerpt
     * If no excerpt, try to shorten the post_content field
     *
     * @return string
     */
    public function getExcerpt($maxWords = 0)
    {
        if ($excerpt = trim($this->getData('post_excerpt'))) {
            return $this->getData('post_excerpt');
        }

        if ($excerpt = $this->_getPostTeaser(true)) {
            $this->setData('post_excerpt', $excerpt);

            return $excerpt;
        }

        if ((int)$maxWords > 1) {
            $excerpt = trim(strip_tags(str_replace(array("\n", '  ', '  '), ' ', $this->_getData('post_content'))));
            $excerpt = preg_replace('/\[[\/]{0,1}[^\]]{1,}\]/', '', $excerpt);
            $excerpt = preg_replace('/[\s]{1,}/', " ", $excerpt);
            $excerpt = explode(' ', $excerpt);

            if (count($excerpt) > $maxWords) {
                $excerpt = rtrim(implode(' ', array_slice($excerpt, 0, $maxWords)), "!@£$%^&*()_-+=[{]};:'\",<.>/? ") . '...';
            }
            else {
                $excerpt = implode(' ', $excerpt);
            }

            return $excerpt;
        }

        return $this->getContent('excerpt');
    }

    /**
     * @deprecated use self::getExcerpt($maxWords)
     *
     */
    public function getPostExcerpt($maxWords = 0)
    {
        return $this->getExcerpt($maxWords);
    }

    /**
     * Determine twhether the post has a more tag in it's content field
     *
     * @return bool
     */
    public function hasMoreTag()
    {
        return strpos($this->getData('post_content'), '<!--more') !== false;
    }

    /**
     * Retrieve the post teaser
     * This is the data from the post_content field upto to the MORE_TAG
     *
     * @return string
     */
    protected function _getPostTeaser($includeSuffix = true)
    {
        if ($this->hasMoreTag()) {
            $content = $this->getData('post_content');

            if (preg_match('/<!--more (.*)-->/', $content, $matches)) {
                $anchor = $matches[1];
                $split = $matches[0];
            }
            else {
                $split = '<!--more-->';
                $anchor = $this->_getTeaserAnchor();
            }

            $excerpt = (trim(substr($content, 0, strpos($content, $split))));

            if ($excerpt !== '' && $includeSuffix && $anchor) {
                $excerpt .= sprintf(' <a href="%s" class="read-more">%s</a>', $this->getUrl(), $anchor);
            }

            $excerpt = strip_tags($this->formatContentString($excerpt), '<a><img><strong>');

            return $excerpt;
        }

        return null;
    }

    /**
     * Retrieve the read more anchor text
     *
     * @return string|false
     */
    protected function _getTeaserAnchor()
    {
        // Allows translation
        return stripslashes(__('Continue reading <span class=\"meta-nav\">&rarr;</span>'));
    }

    /**
     * Get the parent term
     * This is the term with the taxonomy as $taxonomy with the lowest term_id
     *
     * @param string $taxonomy
     * @return \FishPig\WordPress\Model\Term
     */
    public function getParentTerm($taxonomy)
    {
        $terms = $this->getTermCollection($taxonomy)
            ->setPageSize(1)
            ->setCurPage(1)
            ->load();

        return count($terms) > 0 ? $terms->getFirstItem() : false;
    }

    /**
     * Get a collection of terms by the taxonomy
     *
     * @param string $taxonomy
     * @return \FishPig\WordPress\Model\ResourceModel\Term\Collection
     */
    public function getTermCollection($taxonomy)
    {
        return $this->factory->create('FishPig\WordPress\Model\Term')
          ->getCollection()
              ->addTaxonomyFilter($taxonomy)
              ->addPostIdFilter($this->getId());
    }

    /**
     *
     */
    public function getTermCollectionAsString($taxonomy)
    {
        $key = 'term_collection_as_string_' . $taxonomy;

        if (!$this->hasData($key)) {
            $string = array();
            $terms = $this->getTermCollection($taxonomy);

            foreach($terms as $term) {
                $string[] = sprintf('<a href="%s">%s</a>', $term->getUrl(), $term->getName());
            }

            $itemCount = count($string);

            if ($itemCount === 0) {
                $this->setData($key, false);
            }
            else if ($itemCount === 1) {
                $this->setData($key, $string[0]);
            }
            else {
                $lastItem = array_pop($string);

                $this->setData($key, implode(', ', $string) . ' &amp; ' . $lastItem);
            }
        }    

        return $this->_getData($key);
    }

    /**
     * Retrieve the previous post
     *
     * @return false|\FishPig\WordPress\Model\Post
     */
    public function getPreviousPost()
    {
        if (!$this->hasPreviousPost()) {
            $this->setPreviousPost(false);

            if ($collection = $this->getCollection()) {
                $collection->addIsViewableFilter()
                    ->addPostTypeFilter($this->getPostType())
                    ->addPostDateFilter(array('lt' => $this->_getData('post_date')))
                    ->setPageSize(1)
                    ->setCurPage(1)
                    ->setOrderByPostDate()
                    ->load();

                if ($collection->count() > 0) {
                    $this->setPreviousPost($collection->getFirstItem());
                }
            }
        }

        return $this->_getData('previous_post');
    }

    /**
     * Retrieve the next post
     *
     * @return false|\FishPig\WordPress\Model\Post
     */
    public function getNextPost()
    {
        if (!$this->hasNextPost()) {
            $this->setNextPost(false);

            $collection = $this->getCollection()
                ->addIsViewableFilter()
                ->addPostTypeFilter($this->getPostType())
                ->addPostDateFilter(array('gt' => $this->_getData('post_date')))
                ->setPageSize(1)
                ->setCurPage(1)
                ->setOrderByPostDate('asc')
                ->load();

            if ($collection->count() > 0) {
                $this->setNextPost($collection->getFirstItem());
            }
        }

        return $this->_getData('next_post');
    }

    /**
     * Retrieve the URL for the comments feed
     *
     * @return string
     */
    public function getCommentFeedUrl()
    {
        return rtrim($this->getUrl(), '/') . '/feed/';
    }

    /**
     * Wrapper for self::getContent
     *
     * @return string
     */
    public function getPostContent()
    {
        return $this->getContent();
    }

    /**
     * Gets the post content
     *
     * @return string
     */
    public function getContent()
    {
        $content = $this->getData('post_content');

        if (strpos($content, '<!-- wp:') !== false || strpos($content, 'wp-block-embed') !== false) {
            if ($renderedContent = $this->getMetaValue('_post_content_rendered')) {
                if (strpos($renderedContent, '[') !== false) {
                    $renderedContent = $this->shortcodeManager->renderShortcode($renderedContent, $this);
                }

                return $renderedContent;
            }
        }

        $key = '__processed_post_content';

        if (!$this->hasData($key)) {
            $content = $this->formatContentString($content);

            $this->setData($key, $content);
        }

        return $this->getData($key);
    }

    /**
     *
     */
    protected function formatContentString($postContent)
    {
        $postContent = $this->shortcodeManager->addParagraphTagsToString($postContent);
        $postContent = $this->shortcodeManager->renderShortcode($postContent, $this);

        return $postContent;
    }

    /**
     * Returns a collection of comments for this post
     *
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Comment\Collection
     */
    public function getComments()
    {
        if (!$this->hasData('comments')) {
            $this->setData('comments', $this->getResource()->getPostComments($this));
        }

        return $this->getData('comments');
    }

    /**
     * Returns a collection of images for this post
     * 
     * @return \FishPig\WordPress\Model\ResourceModel\Image\Collection
     *
     * NB. This function has not been thoroughly tested
     *        Please report any bugs
     */
    public function getImages()
    {
        if (!$this->hasData('images')) {
            $this->setImages(
                $this->factory->create('Image')->getCollection()->setParent($this->getData('ID'))
            );
        }

        return $this->getData('images');
    }

    /**
     * Returns the featured image for the post
     *
     * This image must be uploaded and assigned in the WP Admin
     *
     * @return \FishPig\WordPress\Model\Image
     */
    public function getImage()
    {
        return $this->getResource()->getFeaturedImage($this);
    }

    /**
     * Get the featured image
     *
     * @return \FishPig\WordPress\Model\Image
     * @deprecated 1.0.0.0
     * @replace self::getImage()
     */
    public function getFeaturedImage()
    {
        return $this->getImage();
    }

    /**
     * Get the model for the author of this post
     *
     * @return \FishPig\WordPress\Model\User
     */
    public function getUser()
    {
        if (!$this->hasUser()) {
            $this->setUser(
                $this->factory->create('User')->load($this->getUserId())
            );
        }

        return $this->_getData('user');
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return (int)$this->getPostAuthor();
    }

    /**
     * Returns the post date formatted
     * If not format is supplied, the format specified in your Magento config will be used
     *
     * @return string
     */
    public function getPostDate($format = null)
    {
        if (($date = $this->getData('post_date_gmt')) === '0000-00-00 00:00:00' || $date === '') {
            $date = date('Y-m-d H:i:s');
        }

        return $this->wpContext->getDateHelper()->formatDate($date, $format);
    }

    /**
     * Returns the post date formatted
     * If not format is supplied, the format specified in your Magento config will be used
     *
     * @return string
     */
    public function getPostModifiedDate($format = null)
    {
        if (($date = $this->getData('post_modified_gmt')) === '0000-00-00 00:00:00' || $date === '') {
            $date = date('Y-m-d H:i:s');
        }

        return $this->wpContext->getDateHelper()->formatDate($date, $format);
    }

    /**
     * Returns the post time formatted
     * If not format is supplied, the format specified in your Magento config will be used
     *
     * @return string
     */
    public function getPostTime($format = null)
    {
        if (($date = $this->getData('post_date_gmt')) === '0000-00-00 00:00:00' || $date === '') {
            $date = date('Y-m-d H:i:s');
        }

        return $this->wpContext->getDateHelper()->formatDate($date, $format);
    }

    /**
     * Determine whether the post has been published
     *
     * @return bool
     */
    public function isPublished()
    {
        return $this->getPostStatus() == 'publish';
    }

    /**
     * Determine whether the post has been published
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->getPostStatus() == 'pending';
    }

    /**
     * Retrieve the preview URL
     *
     * @return string
     */
    public function getPreviewUrl()
    {
        if ($this->isPending()) {
            return $this->url->getUrl('?p=' . $this->getId() . '&preview=1');
        }

        return '';
    }

    /**
     * Determine whether the current user can view the post/page
     * If visibility is protected and user has supplied wrong password, return false
     *
     * @return bool
     */
    public function isViewableForVisitor()
    {
        return true;
    }

    /**
     * Determine whether the post is a sticky post
     * This only works if the post collection has been loaded with addStickyPostsToCollection
     *
     * @return bool
     */    
    public function isSticky()
    {
        return $this->_getData('is_sticky');
    }

    /**
     * Determine whether a post object can be viewed
     *
     * @return string
     */
    public function canBeViewed()
    {
        return $this->isPublished() || ($this->getPostStatus() === 'private' && $this->_app->getConfig()->isLoggedIn());
    }

    /**
     * Wrapper for self::getPermalink()
     *
     * @return string
     */
    public function getUrl()
    {
        if (!$this->hasUrl()) {
            $this->setUrl($this->getGuid());

            if ($this->isFrontPage()) {
                $this->setUrl($this->url->getUrl());
            }
            else if ($this->hasPermalink()) {
                $this->setUrl($this->url->getUrl(
                    $this->_urlEncode($this->_getData('permalink'))
                ));
            }
            else if ($this->getTypeInstance() && $this->getTypeInstance()->isHierarchical()) {
                if ($uris = $this->getTypeInstance()->getAllRoutes()) {
                    if (isset($uris[$this->getId()])) {
                        $this->setUrl($this->url->getUrl($uris[$this->getId()] . '/'));
                    }
                }
            }
        }

        return $this->_getData('url');
    }

    /**
     * Encode the URL, ignoring '/' character
     *
     * @param string $url
     * @return string
     */
    protected function _urlEncode($url)
    {
        if (strpos($url, '/') !== false) {
            $parts = explode('/', $url);

            foreach($parts as $key => $value) {
                $parts[$key] = urlencode($value);
            }

            return implode('/', $parts);
        }

        return urlencode($url);
    }

    /**
     * Get the parent ID of the post
     *
     * @return int
     */
    public function getParentId()
    {
        return (int)$this->_getData('post_parent');
    }

    /**
     * Retrieve the parent page
     *
     * @return false|\FishPig\WordPress\Model\Post
     */
    public function getParentPost()
    {
        if (!$this->hasParentPost()) {
            $this->setParentPost(false);

            if ($this->getParentId()) {
                $parent = $this->factory->create('Post')
                    ->setPostType($this->getPostType() === 'revision' ? '*' : $this->getPostType())
                    ->load($this->getParentId());

                if ($parent->getId()) {
                    $this->setParentPost($parent);
                }
            }
        }

        return $this->_getData('parent_post');
    }

    /**
     * Retrieve the page's children pages
     *
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getChildrenPosts()
    {
        return $this->getCollection()->addPostParentIdFilter($this->getId());
    }

    /**
     * Determine whether children exist
     *
     * @return bool
     */
    public function hasChildrenPosts()
    {
        return $this->getResource()->hasChildrenPosts($this);
    }

    /**
     * The methods here are legacy methods that have been ported over from the old Page class
     * These are deprecated and will be removed shortly.
     */

    /**
     *
     */
    public function getMenuLabel()
    {
        return $this->getPostTitle();
    }

    /**
     *
     */
    public function getParentPage()
    {
        return $this->isType('page') ? $this->getParentPost() : false;
    }    

    /**
     *
     */
    public function hasChildren()
    {
        return $this->hasChildrenPosts();
    }

    /**
     *
     */
    public function getChildren()
    {
        return $this->getChildrenPosts();
    }

    /**
     * @return string
     */
    public function getMetaTableAlias()
    {
        return 'wordpress_post_meta';
    }

    /**
     *
     *
     * @return  string
     */
    public function getMetaTableObjectField()
    {
        return 'post_id';
    }

    /**
     *
     * @return bool
     */
    public function isFrontPage()
    {
        return $this->isType('page') && (int)$this->getId() === (int)$this->_getHomepageModel()->getFrontPageId();
    }

    /**
     *
     * @return bool
     */
    public function isPageForPosts()
    {
        return $this->isType('page') && (int)$this->getId() === (int)$this->_getHomepageModel()->getPageForPostsId();
    }

    /**
     *
     * @return \FishPig\WordPress\Model\Homepage
     */
    protected function _getHomepageModel()
    {
        return $this->factory->get('Homepage');
    }

    /**
     * Get the post format string (eg. video or aside)
     *
     * @return string
     */
    public function getPostFormat()
    {
        if (!$this->hasPostFormat()) {
            $this->setPostFormat('');

            $formats = $this->factory->create('Term')->getCollection()
                ->addTaxonomyFilter('post_format')
                ->setPageSize(1)
                ->addObjectIdFilter($this->getId())
                ->load();

            if (count($formats) > 0) {
                $this->setPostFormat(
                    str_replace('post-format-', '', $formats->getFirstItem()->getSlug())
                );
            }
        }

        return $this->_getData('post_format');
    }

    /**
     * Get the latest revision of the post
     *
     * @return FishPig\WordPress\Model\Post
     */
     public function getLatestRevision()
     {
         if (!$this->hasLatestRevision()) {
            $revision = $this->getCollection()
                ->addFieldToFilter('post_parent', $this->getId())
                ->addPostTypeFilter('revision')
                ->setPageSize(1)
                ->load()
                ->getFirstItem();

            $this->setLatestRevision($revision->getId() ? $revision : false);
         }

         return $this->_getData('latest_revision');
     }

    /**
     * Return cache identities
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     *
     *
     * @return
     */
    public function setAsGlobal()
    {
        $GLOBALS['post'] = json_decode(json_encode(array('ID' => $this->getId())));

        return $this;
    }

    /**
     *
     *
     */
    public function applyPageConfigData($pageConfig)
    {
        parent::applyPageConfigData($pageConfig);

        if (!$pageConfig) {
            return $this;
        }

        if ($this->isFrontPage()) {
            $pageConfig->addBodyClass('wordpress-frontpage');
        }
        else if ($this->isPageForPosts()) {
            $pageConfig->addBodyClass('wordpress-post-list');
        }

        return $this;
    }
}
