<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

abstract class Fishpig_Wordpress_Helper_Plugin_Seo_Abstract extends Fishpig_Wordpress_Helper_Plugin_Abstract
{
	/**
	 * Internal cache variable used to store the action reference
	 *
	 * @var Fishpig_Wordpress_Controller_Abstract
	 */
	protected $_action = false;
	
	/**
	 * The value used to separate token's in the title
	 *
	 * @var string
	 */
	protected $_rewriteTitleToken = '%';
	
	/**
	 * Determines whether the plugin is enabled in WP
	 *
	 * @return bool
	 */
	abstract public function isEnabled();
	
	/**
	 * Retrieve the title format for the given key
	 *
	 * @param string $key
	 * @return string
	 */
	abstract protected function _getTitleFormat($key);
	
	/**
	 * Based on the observer data,
	 * route the request to the appropriate method
	 * This allows extensions to respond to any event without declaring it in config.xml
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function injectSeoObserver(Varien_Event_Observer $observer)
	{
		if ($this->isEnabled()) {
			$method = sprintf('processRoute%s', uc_words($observer->getEvent()->getAction()->getFullActionName(), ''));
			
			try {
				if (method_exists($this, $method) && is_callable(array($this, $method))) {
					$this->_action = $observer->getEvent()->getAction();
					
					$this->_beforeObserver();
					
					$result = call_user_func(array($this, $method), $observer->getEvent()->getObject());
					
					if ($result !== false) {
						$this->_afterObserver();
					}
					
					return $result;
				}
			}
			catch (Mage_Core_Controller_Varien_Exception $e) {
				throw $e;
			}
			catch (Exception $e) {
				Mage::helper('wordpress')->log($e->getMessage());
			}
		}
	}

	/**
	 * Perform global actions before the user_func has been called
	 *
	 * @return $this
	 */
	protected function _beforeObserver()
	{
		return $this;
	}

	/**
	 * Perform global actions after the user_func has been called
	 *
	 * @return $this
	 */	
	protected function _afterObserver()
	{
		return $this;
	}
	
	/**
	 * Iterate through the array and apply the values to page's meta info
	 *
	 * @param array $meta
	 * @return $this
	 */
	protected function _applyMeta(array $meta)
	{
		if (($headBlock = $this->_getHeadBlock()) !== false) {
			foreach($meta as $key => $value) {
				if (($value = trim($value)) === '') {
					continue;
				}

				if (($value = $this->_rewriteString($value)) !== false) {
					$headBlock->setData($key, $value);
					
					if ($key === 'title') {
						$this->_action->ignoreAutomaticTitles();
					}
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Given a key that determines which format to load
	 * and a data array, merge the 2 to create a valid title
	 *
	 * @param string $key
	 * @param array $data
	 * @return string|false
	 */
	protected function _rewriteString($format)
	{
		$data = $this->getRewriteData();
		$rwt = $this->_rewriteTitleToken;
		$value = array();
		$parts = preg_split("/(" . $rwt . "[a-z_-]{1,}" . $rwt . ")/iU", $format, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

		foreach($parts as $part) {
			if (substr($part, 0, strlen($rwt)) === $rwt && substr($part, -(strlen($rwt))) === $rwt) {
				$part = trim($part, $rwt);
				
				if (isset($data[$part])) {
					$value[] = $data[$part];
				}
			}
			else {
				$value[] = $part;
			}
		}

		if (($value = trim(implode('', $value))) !== '') {
			return $value;
		}
		
		return false;
	}

	/**
	 * Retrieve the rewrite data
	 *
	 * @return array
	 */
	public function getRewriteData()
	{
		return array();
	}

	protected function _updateBreadcrumb($name, $label, $link = null)
	{
		if (($label = trim($label)) !== '') {
			if (($crumb = $this->getAction()->getCrumb($name)) !== false) {
				$crumb[0]['label'] = $label;
				
				if (!is_null($link)) {
					$crumb[0]['link'] = $link;				
				}
				
				$this->getAction()->addCrumb($name, $crumb[0], $crumb[1]);
			}
		}
		
		return $this;
	}

	/**
	 * Retrieve the head block from the layout object
	 *
	 * @return false|Mage_Core_Block_Page_Html_Head
	 */
	protected function _getHeadBlock()
	{
		if (($headBlock = Mage::getSingleton('core/layout')->getBlock('head')) !== false) {
			return $headBlock;
		}
		
		return false;
	}
	
	/**
	 * Cause the current action to be undispatched and redirect
	 *
	 * @param string $path
	 */
	protected function _redirect($path)
	{
		header('Location: ' . Mage::getUrl('', array('_direct' => $path)));;
		exit;
		
		$exception = new Mage_Core_Controller_Varien_Exception();

		throw $exception->prepareRedirect($path);
	}
	
	/**
	 * Retrieve the action class
	 *
	 * @return Fishpig_Wordpress_Controller_Abstract
	 */
	public function getAction()
	{
		return $this->_action;
	}
}
