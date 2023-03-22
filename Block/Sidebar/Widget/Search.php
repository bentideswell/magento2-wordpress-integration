<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Sidebar\Widget;

class Search extends AbstractWidget
{
    /**
     * @auto
     */
    protected $searchModel = null;

    /**
     * @param  \Magento\Framework\View\Element\Template\Context $context,
     * @param  \FishPig\WordPress\Block\Context $wpContext,
     * @param  array $data = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FishPig\WordPress\Block\Context $wpContext,
        \FishPig\WordPress\Model\Search $searchModel,
        array $data = []
    ) {
        $this->searchModel = $searchModel;
        parent::__construct($context, $wpContext, $data);
    }
    /**
     * Retrieve the action URL for the search form
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->_urlBuilder->getUrl('wordpress/search');
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
        return $this->searchModel->getSearchTerm();
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
