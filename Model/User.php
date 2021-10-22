<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Api\Data\PostCollectionGeneratorInterface;
use FishPig\WordPress\Api\Data\ViewableModelInterface;

class User extends AbstractMetaModel implements ViewableModelInterface, PostCollectionGeneratorInterface
{
    /**
     * @const string
     */
    const ENTITY = 'wordpress_user';
    const CACHE_TAG = 'wordpress_user';

    /**
     * @var string
     */
    protected $_eventPrefix = 'wordpress_user';
    protected $_eventObject = 'user';

    /**
     * @var \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory
     */
    private $postCollectionFactory;
    
    /**
     *
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \FishPig\WordPress\Model\Context $wpContext,
        \FishPig\WordPress\Api\Data\MetaDataProviderInterface $metaDataProvider,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->postCollectionFactory = $wpContext->getPostCollectionFactory();
        parent::__construct($context, $registry, $wpContext, $metaDataProvider, $resource, $resourceCollection, $data);
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->_getData('display_name');
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if (!$this->hasUrl()) {
            

            $this->setUrl(
                $this->url->getHomeUrlWithFront('author/' . urlencode($this->getUserNicename()) . '/')
            );
        }

        return $this->_getData('url');
    }
    
    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getPostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection
    {
        return $this->postCollectionFactory->create()->addUserIdFilter(
            (int)$this->getId()            
        );
    }
    
    /**
     * @return string
     */
    public function getContent()
    {
        return $this->getMetaValue('description');
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->getGravatarUrl();
    }

    /**
     * @param  string $email
     * @return $this
     */
    public function loadByEmail($email)
    {
        return $this->load($email, 'user_email');
    }

    /**
     * Load the WordPress user model associated with the current logged in customer
     *
     * @return \FishPig\WordPress\Model\User
     */
    public function loadCurrentLoggedInUser()
    {
        return $this;
    }

    /**
     * Retrieve the table prefix
     * This is also used to prefix some fields (roles)
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->getResource()->getTablePrefix();
    }

    /**
     * Retrieve the user's role
     *
     * @return false|string
     */
    public function getRole()
    {
        if ($roles = $this->getMetaValue($this->getTablePrefix() . 'capabilities')) {
            foreach (unserialize($roles) as $role => $junk) {
                return $role;
            }
        }

        return false;
    }

    /**
     * Set the user's role
     *
     * @param  string $role
     * @return $this
     */
    public function setRole($role)
    {
        $this->setMetaValue($this->getTablePrefix() . 'capabilities', serialize([$role => '1']));

        return $this;
    }

    /**
     * Retrieve the user level
     *
     * @return int
     */
    public function getUserLevel()
    {
        return $this->getMetaValue($this->getTablePrefix() . 'user_level');
    }

    /**
     * Retrieve the users first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->getMetaValue('first_name');
    }

    /**
     * Retrieve the users last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->getMetaValue('last_name');
    }

    /**
     * Retrieve the user's nickname
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->getMetaValue('nickname');
    }

    /**
     * Retrieve the URL for Gravatar
     *
     * @return string
     */
    public function getGravatarUrl($size = 50)
    {
        return "https://www.gravatar.com/avatar/" . md5(strtolower(trim($this->getUserEmail()))) . "?d=" . urlencode($this->_getDefaultGravatarImage()) . "&s=" . $size;
    }

    /**
     * Retrieve the URL to the default gravatar image
     *
     * @return string
     */
    protected function _getDefaultGravatarImage()
    {
        return '';
    }
}
