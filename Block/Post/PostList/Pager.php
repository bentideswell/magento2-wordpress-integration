<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Post\PostList;

use Magento\Store\Model\ScopeInterface;

class Pager extends \Magento\Theme\Block\Html\Pager
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \FishPig\WordPress\Model\OptionRepository $optionRepository
     * @param \FishPig\WordPress\Model\UrlInterface $wpUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FishPig\WordPress\Model\OptionRepository $optionRepository,
        \FishPig\WordPress\Model\UrlInterface $wpUrl,
        array $data = []
    ) {
        $this->optionRepository = $optionRepository;
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

        $baseLimit = $this->optionRepository->get('posts_per_page', 10);

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
            $pagerUrl = rtrim(rtrim(substr($pagerUrl, 0, $pos), '/') . $slug, '/')
                        . $this->getTrailingSlash()
                        . substr($pagerUrl, $pos);
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
