<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

/* Parent Class */
use FishPig\WordPress\Model\AbstractModel;

/* Interface */
use FishPig\WordPress\Api\Data\Entity\ViewableInterface;

class Homepage extends AbstractModel implements ViewableInterface
{
	/*
	 * @var string
	 */
	const ENTITY = 'wordpress_homepage';

	/*
	 * @const string
	 */
	const CACHE_TAG = 'wordpress_homepage';
	
	/*
	 * @var
	 */    
  protected $blogPage = null;
    
	/*
	 *
	 *
	 * @return  string
	 */
	public function getName()
	{
		if ($blogPage = $this->getBlogPage()) {
			return $blogPage->getName();
		}

		return $this->getBlogName();
	}

	/*
	 *
	 *
	 * @return  string
	 */
	public function getUrl()
	{
		if ($blogPage = $this->getBlogPage()) {
			return $blogPage->getUrl();	
		}
		
		return $this->url->getUrl();
	}
		
	/*
	 *
	 *
	 * @return  string
	 */
	public function getContent()
	{
		return $this->getBlogDescription();
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getBlogPage()
	{
		if ($this->blogPage !== null) {
			return $this->blogPage;
			
		}
		
		$this->blogPage = false;

		if ((int)$this->getBlogPageId() > 0) {
			$blogPage = $this->_factory->getFactory('Post')->create()->load(
				$this->getBlogPageId()
			);
			
			if ($blogPage->getId()) {
				$this->blogPage = $blogPage;
			}
		}
		
		return $this->blogPage;
	}
	
	/*
	 * If a page is set as a custom homepage, get it's ID
	 *
	 * @return false|int
	 */
	public function getHomepagePageId()
	{
		if ($this->optionManager->getOption('show_on_front') === 'page') {
			if ($pageId = $this->optionManager->getOption('page_on_front')) {
				return $pageId;
			}
		}
		
		return false;
	}
	
	/*
	 * If a page is set as a custom homepage, get it's ID
	 *
	 * @return false|int
	 */
	public function getBlogPageId()
	{
		if ($this->optionManager->getOption('show_on_front') === 'page') {
			if ($pageId = $this->optionManager->getOption('page_for_posts')) {
				return $pageId;
			}
		}
		
		return false;
	}
}
