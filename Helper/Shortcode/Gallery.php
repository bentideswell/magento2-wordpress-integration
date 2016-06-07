<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Shortcode_Gallery extends Fishpig_Wordpress_Helper_Shortcode_Abstract
{
	/**
	 * Retrieve the shortcode tag
	 *
	 * @return string
	 */
	public function getTag()
	{
		return 'gallery';
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
			foreach($shortcodes as $it => $shortcode) {
				$args = $shortcode->getParams();
				
				if (!$args->getColumns()) {
					$args->setColumns(3);
				}

				if (!$args->getSize()) {
					$args->setSize('thumbnail');
				}
				
				if (!$args->getLink()) {
					$args->setLink('attachment');
				}

				if ($args->getPostId()) {
					if ($args->getPostId() !== $params['object']->getId()) {
						$post = Mage::getModel('catalog/post')->load($args->getPostId());
					}
				}
				
				if (($ids = trim($args->getIds(), ',')) !== '') {
					$images = new Varien_Data_Collection();
					
					foreach(explode(',', $ids) as $imageId) {
						$image = Mage::getModel('wordpress/image')->load($imageId);
						
						if ($image->getId()) {
							$images->addItem($image);
						}
					}
				}
				else {
					$images = $post->getImages();
				}
				
				$html = $this->_createBlock('wordpress/template')
					->setImageCollection($images)
					->setColumns($args->getColumns())
					->setPost($post)
					->setSize($args->getSize())
					->setLink($args->getLink())
					->setGalleryIt(($it+1))
					->setTemplate('wordpress/shortcode/gallery.phtml')
					->toHtml();

				$content = str_replace($shortcode->getHtml(), $html, $content);
			}
		}
	}
}

