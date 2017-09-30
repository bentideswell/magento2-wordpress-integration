<?php
/**
 * @category    Fishpig
 * @package     FishPig/WordPress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
 
namespace FishPig\WordPress\Model;

use \FishPig\WordPress\Api\Data\Entity\ViewableInterface;

class Post extends \FishPig\WordPress\Model\Meta\AbstractMeta implements ViewableInterface
{
	/**
	 *
	**/
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
	
	protected $_homepageModel = null;
	
	/**
	 *
	**/
	public function _construct()
	{
        $this->_init('FishPig\WordPress\Model\ResourceModel\Post');
        
        return parent::_construct();
	}

	/**
	 *
	**/
	public function getName()
	{
		return $this->_getData('post_title');
	}
	
	/**
	 *
	**/
	public function getMetaDescription()
	{
		return $this->getExcerpt(20);
	}

	/**
	 *
	**/
	public function isType($type)
	{
		return $this->getPostType() === $type;
	}
	
	/**
	 *
	**/
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
			else if ($typeInstance = $this->_app->getPostType($this->getPostType())) {
				$this->setTypeInstance($typeInstance);
			}
			else {
				$this->setTypeInstance($this->_app->getPostType('post'));
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
	 * Retrieve the post GUID
	 *
	 * @return string
	 */	
	public function getGuid()
	{
		if ($this->getPostType() === 'page') {
			return $this->_wpUrlBuilder->getUrl() . '?page_id=' . $this->getId();
		}
		else if ($this->getPostType() === 'post') {
			return $this->_wpUrlBuilder->getUrl() . '?p=' . $this->getId();
		}
		
		return $this->_wpUrlBuilder->getUrl() . '?p=' . $this->getId() . '&post_type=' . $this->getPostType();
	}

	/**
	 * Retrieve the post excerpt
	 * If no excerpt, try to shorten the post_content field
	 *
	 * @return string
	 */
	public function getExcerpt($maxWords = 0)
	{
		if ($this->getData('post_excerpt')) {
			return $this->getData('post_excerpt');
		}
		
		if ($this->hasMoreTag() && ($excerpt = $this->_getPostTeaser(true))) {
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
			$content = $this->getContent('excerpt');

			if (preg_match('/<!--more (.*)-->/', $content, $matches)) {
				$anchor = $matches[1];
				$split = $matches[0];
			}
			else {
				$split = '<!--more-->';
				$anchor = $this->_getTeaserAnchor();
			}
			
			$excerpt = trim(substr($content, 0, strpos($content, $split)));

			if ($excerpt !== '' && $includeSuffix && $anchor) {
				$excerpt .= sprintf(' <a href="%s" class="read-more">%s</a>', $this->getUrl(), $anchor);
			}
			
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
	 * If Yoast SEO is installed, the primary category will be used (if $taxonomy === category)
	 *
	 * @param string $taxonomy
	 * @return \FishPig\WordPress\Model\Term
	 **/
	public function getParentTerm($taxonomy)
	{
		/*
		if ($taxonomy === 'category' && $this->_app->isAddonInstalled('WordPressSEO')) {
			if ($category = Mage::helper('wp_addon_yoastseo')->getPostPrimaryCategory($this)) {
				return $category;
			}
		}
		*/
		
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
		return $this->_factory->getFactory('Term')->create()
			->getCollection()
				->addTaxonomyFilter($taxonomy)
				->addPostIdFilter($this->getId());
	}
	
	/**
	 *
	**/
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
			
			$collection = $this->getCollection()
				->addIsViewableFilter()
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

	/*
	 * Wrapper for self::getContent
	 *
	 * @return string
	 */
	public function getPostContent($context = 'default')
	{
		return $this->getContent($context);
	}
	
	/**
	 * Gets the post content
	 *
	 * @return string
	 */
	public function getContent($context = 'default')
	{
		$contextKey = 'post_content_' . $context;
		
		if (!$this->_getData($contextKey)) {
			$this->setData($contextKey, $this->_filter->process($this->_getData('post_content'), $this));
		}

		return $this->_getData($contextKey);
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
				$this->_factory->getFactory('Image')->create()
					->getCollection()->setParent($this->getData('ID'))
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
	
	/*
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
				$this->_factory->getFactory('User')->create()->load($this->getUserId())
			);
		}
		
		return $this->_getData('user');
	}
	
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
		
		return $this->_viewHelper->formatDate($date, $format);
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
		
		return $this->_viewHelper->formatDate($date, $format);
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
		
		return $this->_viewHelper->formatDate($date, $format);
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
			return $this->_app->getUrl('?p=' . $this->getId() . '&preview=1');
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
		return $this->isPublished()
			|| ($this->getPostStatus() === 'private' && $this->_app->getConfig()->isLoggedIn());
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
			
			if ($this->isHomepage()) {
				$this->setUrl($this->_wpUrlBuilder->getUrl());
			}
			else if ($this->hasPermalink()) {
				$this->setUrl($this->_wpUrlBuilder->getUrl(
					$this->_urlEncode($this->_getData('permalink'))
				));
			}
			else if ($this->getTypeInstance()->isHierarchical()) {
				if ($uris = $this->getTypeInstance()->getAllRoutes()) {
					if (isset($uris[$this->getId()])) {
						$this->setUrl($this->_wpUrlBuilder->getUrl($uris[$this->getId()] . '/'));
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
				$parent = $this->_factory->getFactory('Post')->create()
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

	public function getMenuLabel()
	{
		return $this->getPostTitle();
	}
	
	public function getParentPage()
	{
		return $this->isType('page') ? $this->getParentPost() : false;
	}	
	
	public function hasChildren()
	{
		return $this->hasChildrenPosts();
	}
	
	public function getChildren()
	{
		return $this->getChildrenPosts();
	}
	
	public function isHomepagePage()
	{
		return $this->isType('page') && (int)$this->getId() === (int)$this->_app->getHomepagePageId();
	}
	
	public function isBlogListingPage()
	{
		return $this->isType('page') && (int)$this->getId() === (int)$this->_app->getBlogPageId();
	}
	
	/**
	 *
	 *
	 * @return  string
	**/
	public function getMetaTableAlias()
	{
		return 'wordpress_post_meta';
	}
	
	/**
	 *
	 *
	 * @return  string
	**/
	public function getMetaTableObjectField()
	{
		return 'post_id';
	}
	
	/**
	 *
	 * @return bool
	**/
	public function isHomepage()
	{
		return $this->isType('page') && (int)$this->getId() === (int)$this->_app->getHomepagePageId();
	}
	
	/**
	 *
	 * @return \FishPig\WordPress\Model\Homepage
	**/
	protected function _getHomepageModel()
	{
		if ($this->homepageModel === null) {
			$this->homepageModel = $this->_factory->getFactory('Homepage')->create();
		}
		
		return $this->homepageModel;
	}
	
	/**
	 * Get the post format string (eg. video or aside)
	 *
	 * @return string
	**/
	public function getPostFormat()
	{
		if (!$this->hasPostFormat()) {
			$this->setPostFormat('');

			$formats = $this->_factory->getFactory('Term')->create()->getCollection()
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
	 **/
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
}
