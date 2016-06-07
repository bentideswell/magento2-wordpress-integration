<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model\ResourceModel;

class User extends \FishPig\WordPress\Model\ResourceModel\AbstractResource
{

	public function _construct()
	{
		$this->_init('wordpress_user', 'ID');
	}

	/**
	 * Load the WP User associated with the current logged in Customer
	 *
	 * @param Fishpig_Wordpress_Model_User $user
	 * @return bool
	 */
	public function loadCurrentLoggedInUser(Fishpig_Wordpress_Model_User $user)
	{
		$session = Mage::getSingleton('customer/session');
		
		if ($session->isLoggedIn()) {
			$user->loadByEmail($session->getCustomer()->getEmail());

			if ($user->getId() > 0) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Ensure the model has the necessary data attributes set
	 *
	 * @param \Magento\Framework\Model\AbstractModel $object
	 * @return $this
	 */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
    	if (!$object->getUserEmail()) {
    		throw new Exception('Cannot save WordPress user without email address');
    	}
    	
    	if (!$object->getUserRegistered()) {
    		$object->setUserRegistered(now());
    	}
    	
		if (!$object->getUserStatus()) {
			$object->setUserStatus(0);
		}
		
		if (!$object->getRole()) {
			$object->setRole($object->getDefaultUserRole());
		}
		
		if (!$object->getUserLevel()) {
			$object->setUserLevel(0);
		}
			
    	return parent::_beforeSave($object);
    }
    
    /**
     * Remove duplicate user accounts from WordPress that use the same email address
     *
     * @return $this
     */
    public function cleanDuplicates()
    {
		$collection = Mage::getResourceModel('wordpress/user_collection')->load();
		$byEmail = array();
		
		foreach($collection as $user) {
			$email = $user->getUserEmail();
			
			if (!isset($byEmail[$email])) {
				$byEmail[$email] = array();
			}

			$byEmail[$email]	[] = (int)$user->getId();
		}

	    $db = $this->_getWriteAdapter();
		$postTable = $this->getTable('wordpress_post');
		
	    foreach($byEmail as $email => $users) {
		    if (count($users) > 1) {
			    $original = array_shift($users);

			    $db->update($postTable, array('post_author' => $original), $db->quoteInto('post_author IN (?)', $users));
				$db->delete($this->getMainTable(), $db->quoteInto('ID IN (?)', $users));
			}
	    }
	    
	    $select = $db->select()
	    	->distinct()
	    	->from($this->getTable('wordpress_user'), 'ID');
	    	
	    $userIds = $db->fetchCol($select);
		
		if (count($userIds) > 0) {
			$db->delete($this->getTable('wordpress_user_meta'), $db->quoteInto('user_id NOT IN (?)', $userIds));
		}

		return $this;
    }
}
