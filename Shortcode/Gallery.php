<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Shortcode;

class Gallery extends AbstractShortcode
{
	protected $_imageFactory = null;
	
	/**
	 * Constructor
	**/
    public function __construct(
	    \FishPig\WordPress\Model\PostFactory $postFactory,
    	\Magento\Framework\View\Element\Context $context, 
	    \FishPig\WordPress\Model\ImageFactory $imageFactory,
    	array $data = []
    )
    {
	    parent::__construct($postFactory, $context, $data);
		
		$this->_imageFactory = $imageFactory;
    }
    
	/**
	 *
	 *
	 * @return 
	**/
	public function getTag()
	{
		return 'gallery';
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	protected function _process()
	{
		if (($shortcodes = $this->_getShortcodesByTag($this->getTag())) !== false) {
			foreach($shortcodes as $it => $shortcode) {
				$params = $shortcode->getParams();
				
				if (!$params->getColumns()) {
					$params->setColumns(3);
				}
				
				if ($params->getSize()) {
					$params->setSize('thumbnail');
				}
				
				if (!$params->getLink()) {
					$params->setLink('attachment');
				}

				$post = false;
				
				if ($params->getPostId() && (int)$params->getPostId() !== $this->getPostId()) {
					$post = $this->_postFactory->create()->load($params->getPostId());
				}

				if (($ids = trim($params->getIds(), ',')) !== '') {
					$images = array();
					
					foreach(explode(',', $ids) as $imageId) {
						$image = $this->_imageFactory->create()->load($imageId);
						
						if ($image->getId()) {
							$images[] = $image;
						}
					}
				}
				else if ($post) {
					$images = $post->getImages();
				}

				$html = $this->_layout->createBlock('\Magento\Framework\View\Element\Template')
					->setTemplate('FishPig_WordPress::shortcode/gallery.phtml')
					->addData($params->getData())
					->setObject($this->getObject())
					->setGalleryIt($it+1)
					->setImages($images)
					->toHtml();

				$this->setValue(str_replace($shortcode['html'], $html, $this->getValue()));
			}
		}
		
		return $this;
	}
}
