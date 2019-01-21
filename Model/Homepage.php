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
  protected $staticPage;
    
	/*
	 *
	 *
	 * @return  string
	 */
	public function getName()
	{
		if ($staticPage = $this->getFrontStaticPage()) {
			return $staticPage->getName();
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
		if ($staticPage = $this->getFrontStaticPage()) {
			return $staticPage->getUrl();	
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
        if ($staticPage = $this->getFrontStaticPage()) {
            return $staticPage->getContent();
        }

		return $this->getBlogDescription();
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getFrontStaticPage()
	{
		if ($this->staticPage !== null) {
			return $this->staticPage;
		}
		
		$this->staticPage = false;

		if ((int)$this->getPageForPostsId() > 0) {
			$staticPage = $this->factory->create('Post')->load(
				$this->getPageForPostsId()
			);
			
			if ($staticPage->getId()) {
				$this->staticPage = $staticPage;
			}
		}
		
		return $this->staticPage;
	}
	
	/*
	 * If a page is set as a custom homepage, get it's ID
	 *
	 * @return false|int
	 */
	public function getFrontPageId()
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
	public function getPageForPostsId()
	{
		if ($this->optionManager->getOption('show_on_front') === 'page') {
			if ($pageId = $this->optionManager->getOption('page_for_posts')) {
				return $pageId;
			}
		}
		
		return false;
	}
}
