<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model\Shortcode;

use FishPig\WordPress\Helper\Shortcode as ShortcodeHelper;
use FishPig\WordPress\Model\PostFactory;
use FishPig\WordPress\Model\ImageFactory;
use Magento\Framework\View\Layout;

class Gallery
{
	/*
	 *
	 */
	protected $helper;
	
	/*
	 * @var Layout
	 */
	protected $layout;

	/*
	 *
	 */
	protected $postFactory;

	/*
	 *
	 */
	protected $imageFactory;
	
	public function __construct(ShortcodeHelper $helper, PostFactory $postFactory, ImageFactory $imageFactory, Layout $layout)
	{
		$this->helper = $helper;
		$this->postFactory = $postFactory;
		$this->imageFactory = $imageFactory;
		$this->layout = $layout;
	}
	
	/*
	 *
	 *
	 * @return 
	*/
	public function renderShortcode($input, array $args = [])
	{
		if (($shortcodes = $this->helper->getShortcodesByTag($input, 'gallery')) !== false) {
			foreach($shortcodes as $it => $shortcode) {
				$params = $shortcode->getParams();
				
				if (!$params->getColumns()) {
					$params->setColumns(3);
				}
				
				if ($params->getSize()) {
					$params->setSize($params->getSize());
				}
				
				if (!$params->getLink()) {
					$params->setLink('attachment');
				}

				$post = false;
				$currentPostId = isset($args['object']) ? (int)$args['object']->getId() : 0;
				
				if ($params->getPostId() && (int)$params->getPostId() !== $currentPostId) {
					$post = $this->postFactory->create()->load($params->getPostId());
				}

				if (($ids = trim($params->getIds(), ',')) !== '') {
					$images = array();
					
					foreach(explode(',', $ids) as $imageId) {
						$image = $this->imageFactory->create()->load($imageId);
						
						if ($image->getId()) {
							$images[] = $image;
						}
					}
				}
				else if ($post) {
					$images = $post->getImages();
				}

				$html = $this->layout->createBlock('\Magento\Framework\View\Element\Template')
					->setTemplate('FishPig_WordPress::shortcode/gallery.phtml')
					->addData($params->getData())
					->setObject(isset($args['object']) ? $args['object'] : false)
					->setGalleryIt($it+1)
					->setImages($images)
					->toHtml();

				$input = str_replace($shortcode['html'], $html, $input);
			}
		}
		
		return $input;
	}
	
	/*
	 * @return bool
	 */
	public function requiresAssetInjection()
	{
		return false;
	}
	
	/*
	 * @return bool
	 */
	public function isEnabled()
	{
		return true;
	}
}
