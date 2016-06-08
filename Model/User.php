<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
 
namespace FishPig\WordPress\Model;

use \FishPig\WordPress\Api\Data\Entity\MetaInterface;
use \FishPig\WordPress\Api\Data\Entity\ViewableInterface;

class User extends \FishPig\WordPress\Model\AbstractModel implements MetaInterface, ViewableInterface
{
	const ENTITY = 'wordpress_user';
	
	/**
	 * Entity meta infromation
	 *
	 * @var string
	 */
	protected $_metaHasPrefix = true;

	/**
	 * Event information
	 *
	 * @var string
	*/
	protected $_eventPrefix = 'wordpress_user';
	protected $_eventObject = 'user';

	public function getMetaTableObjectField()
	{
		return 'user_id';
	}
	
	public function getMetaTableAlias()
	{
		return 'wordpress_user_meta';
	}
	
	/**
	 * Retrieve the column name of the primary key fields
	 *
	 * @return string
	 */
	public function getMetaPrimaryKeyField()
	{
		return 'umeta_id';
	}
	
	public function _construct()
	{
        $this->_init('FishPig\WordPress\Model\ResourceModel\User');
	}

	public function getName()
	{
		return $this->_getData('name');
	}
	
	public function getContent()
	{
		return $this->_getData('post_content');
	}
	
	public function getSummary()
	{
		return 'Summary';
	}
	
	public function getPageTitle()
	{
		return 'Page Title';
	}
	
	public function getMetaDescription()
	{
		return 'Meta Description';
	}
	
	public function getMetaKeywords()
	{
		return 'keywords,for,meta';
	}
	
	public function getRobots()
	{
		return 'index,follow';
	}
	
	public function getCanonicalUrl()
	{
		return $this->getUrl();
	}

	public function getImage()
	{
		return false;
		
		#add gravatar here?
	}
	
	/**
	 * Load a user by an email address
	 *
	 * @param string $email
	 * @return $this
	 */
	public function loadByEmail($email)
	{
		return $this->load($email, 'user_email');
	}
	
	/**
	 * Get the URL for this user
	 *
	 * @return string
	 */
	public function getUrl()
	{
		if (!$this->hasUrl()) {
			$this->setUrl($this->_wpUrlBuilder->getUrl('author/' . urlencode($this->getUserNicename())) . '/');
		}
		
		return $this->_getData('url');
	}

	/**
	 * Load the WordPress user model associated with the current logged in customer
	 *
	 * @return \FishPig_WordPress\Model\User
	 */
	public function loadCurrentLoggedInUser()
	{
		return $this->getResource()->loadCurrentLoggedInUser($this);
	}
	
	/**
	 * Retrieve the table prefix
	 * This is also used to prefix some fields (roles)
	 *
	 * @return string
	 */
	public function getTablePrefix()
	{
		return $this->getApp()->getTablePrefix();
	}
	
	/**
	 * Retrieve the user's role
	 *
	 * @return false|string
	 */
	public function getRole()
	{
		if ($roles = $this->getMetaValue($this->getTablePrefix() . 'capabilities')) {
			foreach(unserialize($roles) as $role => $junk) {
				return $role;
			}
		}
		
		return false;
	}
	
	/**
	 * Set the user's role
	 *
	 * @param string $role
	 * @return $this
	 */
	public function setRole($role)
	{
		$this->setMetaValue($this->getTablePrefix() . 'capabilities', serialize(array($role => '1')));
		
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
	 * Set the user level
	 *
	 * @param int $level
	 * @return $this
	 */
	public function setUserLevel($level)
	{
		$this->setMetaValue($this->getTablePrefix() . 'user_level', $level);
		return $this;
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
	 * Set the users first name
	 *
	 * @param string $name
	 * @return $this
	 */
	public function setFirstName($name)
	{
		$this->setMetaValue('first_name', $name);
		return $this;
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
	 * Set the users last name
	 *
	 * @param string $name
	 * @return $this
	 */
	public function setLastName($name)
	{
		$this->setMetaValue('last_name', $name);
		return $this;
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
	 * Set the user's nickname
	 *
	 * @param string $nickname
	 * @return $this
	 */
	public function setNickname($nickname)
	{
		$this->setMetaValue('nickname', $nickname);
		return $this;
	}

	/**
	 * Retrieve the URL for Gravatar
	 *
	 * @return string
	 */
	public function getGravatarUrl($size = 50)
	{
		return "http://www.gravatar.com/avatar/" . md5(strtolower(trim($this->getUserEmail()))) . "?d=" . urlencode( $this->_getDefaultGravatarImage() ) . "&s=" . $size;
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
	
	/**
	 * Retrieve the user's photo
	 * The UserPhoto plugin must be installed in WordPress
	 *
	 * @param bool $thumb
	 * @return null|string
	 */
	public function getUserPhoto($thumb = false)
	{
		$dataKey = $thumb ? 'userphoto_thumb_file' : 'userphoto_image_file';
		
		if (!$this->hasData($dataKey)) {
			if ($photo = $this->getCustomField($dataKey)) {
				$this->setData($dataKey, $this->getApp()->getFileUploadUrl() . 'userphoto/' . $photo);
			}
			else if ($this->getApp()->getWpOption('userphoto_use_avatar_fallback')) {
				if ($thumb) {
					$this->setData($dataKey, $this->getGravatarUrl($this->getApp()->getWpOption('userphoto_thumb_dimension')));
				}
				else {
					$this->setData($dataKey, $this->getGravatarUrl($this->getApp()->getWpOption('userphoto_maximum_dimension')));
				}
			}
		}
		
		return $this->_getData($dataKey);
	}
	
	/**
	 * Retrieve the default user role from the WordPress Database
	 *
	 * @return string
	 */
	public function getDefaultUserRole()
	{
		if (($role = trim($this->getApp()->getWpOption('default_role', 'subscriber'))) !== '') {
			return $role;
		}

		return 'subscriber';
	}
}
