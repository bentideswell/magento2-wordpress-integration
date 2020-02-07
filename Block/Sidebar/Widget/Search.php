<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Sidebar\Widget;

class Search extends AbstractWidget
{
    /**
     * Retrieve the action URL for the search form
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->url->getUrl('search') . '/';
    }

    /**
     * Retrieve the default title
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return __('Search');
    }

    /**
     * Retrieve the search term used
     *
     * @return string
     */
    public function getSearchTerm()
    {
        return '';
    }

    /**
     * Ensure template is set
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::sidebar/widget/search.phtml');
        }

        return parent::_beforeToHtml();
    }
}
