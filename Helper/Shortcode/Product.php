<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Shortcode_Product extends Fishpig_Wordpress_Helper_Shortcode_Abstract
{
	/**
	 * Retrieve the shortcode tag
	 *
	 * @return string
	 */
	public function getTag()
	{
		return 'product';
	}
	
	/**
	 * Apply the Vimeo short code
	 *
	 * @param string &$content
	 * @param Fishpig_Wordpress_Model_Post $post
	 * @return void
	 */	
	protected function _apply(&$content, Fishpig_Wordpress_Model_Post $post)
	{
		if (($shortcodes = $this->_getShortcodes($content)) !== false) {
			foreach($shortcodes as $shortcode) {
				$params = $shortcode->getParams();
				$collection = Mage::getResourceModel('catalog/product_collection');
										
				try {
					if ($params->getId()) {
						$params->setIds(array($params->getId()));
					}
					else if ($params->getSku()) {
						$params->setIds(array(Mage::getResourceModel('catalog/product')->getIdBySku($params->getSku())));
					}
					else if ($params->getIds()) {
						$params->setIds(explode(',', $params->getIds()));
					}
					else if ($params->getSkus()) {
						$ids = array();
						$resource = Mage::getResourceModel('catalog/product');
						
						foreach(explode(',', $params->getSkus()) as $sku) {
							if ($id = $resource->getIdBySku($sku)) {
								$ids[] = $id;
							}
						}

						$params->setIds($ids);
					}
					
					if ($params->getIds()) {
						$collection->addAttributeToFilter('entity_id', array('in' => $params->getIds()));
					}
					else if ($params->getAttribute() && ($params->getValue() || $params->getValueId())) {
						if ($params->getValue()) {
							$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $params->getAttribute());
							
							if (!$attribute->getSourceModel()) {
								$params->setValueId($params->getValue());
							}
							else if ($optionId = $attribute->getSource()->getOptionId($params->getValue())) {
								$params->setValueId($optionId);
							}
						}
						
						if ($params->getValueId()) {
							$collection->addAttributeToFilter($attribute->getAttributeCode(), $params->getValueId());
						}
						else {
							throw new Exception('Invalid value/value_id set for attribute in the product shortcode.');
						}
						
						if ($params->getOrder()) {
							$collection->setOrder($params->getOrder(), ($params->getDir() ? $params->getDir() : 'asc'));
						}
						
						if ($params->getLimit()) {
							$collection->setPageSize((int)$params->getLimit());
						}
					}
					else if (!$params->getIds()) {
						throw new Exception('The id, sku, ids or attribute parameter is not set for the product shortcode');
					}
					
					if (!Mage::getStoreConfigFlag('cataloginventory/options/show_out_of_stock')) {
						Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
					}

					$collection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
						->addAttributeToFilter('status', 1)
						->addAttributeToFilter('visibility', array('in' => array(2, 4)))
						->load();

					if ($collection->count() === 0) {
						throw new Exception('No valid products used in product shortcode');
					}
					
					$template = $params->getTemplate() ? $params->getTemplate() : 'wordpress/shortcode/product.phtml';

					$html = $this->_createBlock('wordpress/shortcode_product')
						->setTemplate($template)
						->setItems($collection)
						->setProducts($collection)
						->setProduct($collection->getFirstItem())
						->setProductId($collection->getFirstItem()->getId())
						->setShortcodeParams($params)
						->toHtml();

					$content = str_replace($shortcode->getHtml(), $html, $content);
				}
				catch (Exception $e) {
					$content = str_replace($shortcode->getHtml(), '', $content);
				}
			}
		}
	}
}
