<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Post;

use FishPig\WordPress\Model\Post;

class Comment extends \FishPig\WordPress\Model\AbstractMetaModel
{
    /**
     * @const string
     */
    const ENTITY = 'wordpress_post_comment';
    const CACHE_TAG = 'wordpress_post_comment';

    /**
     * @var const string
     */
    const GRAVATAR_BASE_URL = 'http://www.gravatar.com/avatar/';
    const GRAVATAR_BASE_URL_SECURE = 'https://secure.gravatar.com/avatar/';

    /**
     * @var \FishPig\WordPress\Model\Post
     */
    private $post = null;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \FishPig\WordPress\Model\Context $wpContext,
        \FishPig\WordPress\Api\Data\MetaDataProviderInterface $metaDataProvider,
        \FishPig\WordPress\Model\PostRepository $postRepository,
        \FishPig\WordPress\Model\OptionRepository $optionRepository,
        \FishPig\WordPress\Helper\Date $dateHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->postCollectionFactory = $wpContext->getPostCollectionFactory();
        $this->postRepository = $postRepository;
        $this->optionRepository = $optionRepository;
        $this->dateHelper = $dateHelper;

        parent::__construct($context, $registry, $wpContext, $metaDataProvider, $resource, $resourceCollection, $data);
    }
    
    /**
     * Retrieve the post that this comment is associated to
     *
     * @return Post|false
     */
    public function getPost()
    {
        if ($this->post === null) {
            $this->post = $this->postRepository->get((int)$this->getData('comment_post_ID'));
        }
        
        return $this->post;
    }

    /**
     * Returns the comment date
     * If no format is specified, the default format is used from the Magento config
     *
     * @return string
     */
    public function getCommentDate($format = null)
    {
        return $this->dateHelper->formatDate($this->getData('comment_date'), $format);
    }

    /**
     * Returns the comment time
     * If no format is specified, the default format is used from the Magento config
     *
     * @return string
     */
    public function getCommentTime($format = null)
    {
        return $this->dateHelper->formatTime($this->getData('comment_date'), $format);
    }

    /**
     * Return the URL for the comment author
     *
     * @return string
     */
    public function getCommentAuthorUrl()
    {
        if ($url = $this->_getData('comment_author_url')) {
            if (strpos($url, 'http') !== 0) {
                $url = 'http://' . $url;
            }

            return $url;
        }

        return '#';
    }

    /**
     * Get the comment GUID
     *
     * @return string
     */
    public function getGuid()
    {
        return $this->getPost()
            ? $this->url->getUrl('?p='. $this->getPost()->getId() . '#comment-' . $this->getId())
            : '';
    }

    /**
     * Retrieve the URL for this comment
     *
     * @return string
     */
    public function getUrl()
    {
        if (!$this->hasUrl()) {
            if ($post = $this->getPost()) {
                $pageId = '';

                if ($this->optionRepository->get('page_comments')) {
                    $pageId = '/comment-page-' . $this->getCommentPageId();
                }

                $fragment = '#comment-' . $this->getId();

                if (substr($post->getUrl(), -1) === '/') {
                    $fragment = '/' . $fragment;
                }

                $this->setUrl(rtrim($post->getUrl(), '/') . $pageId . $fragment);
            }
        }

        return $this->getData('url');
    }

    /**
     * Retrieve the page number that the comment is on
     *
     * @return int
     */
    public function getCommentPageId()
    {
        if (!$this->hasCommentPageId()) {
            $this->setCommentPageId(1);
            if ($post = $this->getPost()) {
                $totalComments = count($post->getComments());
                $commentsPerPage = $this->optionRepository->get('comments_per_page', 50);

                if ($commentsPerPage > 0 && $totalComments > $commentsPerPage) {
                    $it = 0;

                    foreach ($post->getComments() as $comment) {
                        ++$it;
                        if ($this->getId() == $comment->getId()) {
                            $position = $it;
                            break;
                        }
                    }

                    $this->setCommentPageId(ceil($position / $commentsPerPage));
                } else {
                    $this->setCommentPageId(1);
                }
            }
        }

        return $this->getData('comment_page_id');
    }

    /**
     * Retrieve the child comments
     *
     * @return Varien_Data_Collection
     */
    public function getChildrenComments()
    {
        return $this->getCollection()
            ->addCommentApprovedFilter()
            ->addParentCommentFilter($this->getId())
            ->addOrderByDate();
    }

    /**
     * Retrieve the Gravatar URL for the comment
     *
     * @return null|string
     */
    public function getAvatarUrl($size = 50)
    {
        if (!$this->hasGravatarUrl()) {
            if ($this->optionRepository->get('show_avatars')) {
                if ($this->getCommentAuthorEmail()) {
                    $params = [
                        'r' => $this->optionRepository->get('avatar_rating'),
                        's' => (int)$size,
                        'd' => $this->optionRepository->get('avatar_default'),
                        'v' => 45345
                    ];

                    $baseUrl = self::GRAVATAR_BASE_URL_SECURE;

                    // phpcs:ignore -- not cryptographic
                    $url = $baseUrl . md5(strtolower($this->getCommentAuthorEmail()))
                         . '/?' . http_build_query($params);

                    $this->setGravatarUrl($url);
                }
            }
        }

        return $this->_getData('gravatar_url');
    }

    /**
     * Deprecated. Use self::getAvatarUrl($size)
     *
     * @param  int $size
     * @return string
     */
    public function getGravatarUrl($size = 50)
    {
        return $this->getAvatarUrl($size);
    }

    /**
     * Determine whether the comment is approved
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->_getData('comment_approved') === '1';
    }

    /**
     * Retrieve the comment anchor
     *
     * @return string
     */
    public function getAnchor()
    {
        return $this->getPost()
            ? sprintf(
                '<a href="%s" title="%s">%s</a>',
                $this->getUrl(),
                $this->getCommentAuthor(),
                $this->getPost()->getPostTitle()
            ) : '';
    }

    /**
     * @return string
     */
    public function getPostTitle() : string
    {
        return $this->getPost() ? $this->getPost()->getPostTitle() : '';
    }
}
