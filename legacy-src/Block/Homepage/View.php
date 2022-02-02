<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Homepage;

class View extends \FishPig\WordPress\Block\PostType\View
{
    /**
     * @return bool
     */
    public function isFirstPage(): bool
    {
        return (int)$this->getRequest()->getParam('page', 1) === 1;
    }
}
