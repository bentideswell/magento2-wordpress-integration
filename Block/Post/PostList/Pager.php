<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Post\PostList;

/** Parent Block */
use Magento\Theme\Block\Html\Pager as MagentoPager;
use Magento\Framework\View\Element\Template\Context;
use FishPig\WordPress\Model\OptionManager;
use Magento\Store\Model\ScopeInterface;

class Pager extends MagentoPager
{
    /**
     * @var OptionManager
     */
    protected $optionManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param App
     * @param array $data
     */
    public function __construct(Context $context, OptionManager $optionManager, array $data = [])
    {
    $this->optionManager = $optionManager;

    parent::__construct($context, $data);
    }

    /**
     * Construct the pager and set the limits
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
    public function getPagerUrl($params = array())
    {
        $pageVarName = $this->getPageVarName();

        if (isset($params[$pageVarName])) {
            $slug = '/' . $pageVarName . '/' . $params[$pageVarName] . '/';
            unset($params[$pageVarName]);
        }
        else {
            $slug = '';
        }

        $pagerUrl = parent::getPagerUrl($params);

        if (strpos($pagerUrl, '?') !== false) {
            $pagerUrl = rtrim(substr($pagerUrl, 0, strpos($pagerUrl, '?')), '/') . $slug . substr($pagerUrl, strpos($pagerUrl, '?'));
        }
        else {
            $pagerUrl = rtrim($pagerUrl, '/') . $slug;
        }

        return $pagerUrl;
    }
}
