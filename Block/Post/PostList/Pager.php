<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Post\PostList;

use Magento\Theme\Block\Html\Pager as MagentoPager;
use Magento\Framework\View\Element\Template\Context;
use FishPig\WordPress\Model\OptionManager;
use FishPig\WordPress\Model\Url as WPUrl;
use Magento\Store\Model\ScopeInterface;

class Pager extends MagentoPager
{
    /**
     * @param Context       $context
     * @param OptionManager $optionManager
     * @param WPUrl         $wpUrl
     * @param array         $data
     */
    public function __construct(
        Context $context,
        OptionManager $optionManager,
        WPUrl $wpUrl,
        array $data = []
    ) {
        $this->optionManager = $optionManager;
        $this->wpUrl = $wpUrl;
        
        parent::__construct($context, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setPageVarName('page');

        $baseLimit = $this->optionManager->getOption('posts_per_page', 10);

        $this->setDefaultLimit($baseLimit);
        $this->setLimit($baseLimit);
        $this->setAvailableLimit([$baseLimit => $baseLimit]);

        $this->setFrameLength(
            (int)$this->_scopeConfig->getValue(
                'design/pagination/pagination_frame',
                ScopeInterface::SCOPE_STORE
            )
        );
    }

    /**
     * Return the URL for a certain page of the collection
     *
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        $pageVarName = $this->getPageVarName();

        if (isset($params[$pageVarName])) {
            $slug = '/' . $pageVarName . '/' . $params[$pageVarName] . '/';
            unset($params[$pageVarName]);
        } else {
            $slug = '';
        }

        $pagerUrl = parent::getPagerUrl($params);

        if (($pos = strpos($pagerUrl, '?')) !== false) {
            $pagerUrl = rtrim(rtrim(substr($pagerUrl, 0, $pos), '/') . $slug, '/') . $this->getTrailingSlash() . substr($pagerUrl, $pos);
        } else {
            $pagerUrl = rtrim(rtrim($pagerUrl, '/') . $slug, '/') . $this->getTrailingSlash();
        }

        return $pagerUrl;
    }
    
    /**
     * @return string
     */
    private function getTrailingSlash()
    {
        return $this->wpUrl->hasTrailingSlash() ? '/' : '';
    }
}
