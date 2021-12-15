<?php
/**
 * @category FishPig
 * @package  FishPig_WordPress
 * @author   Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block\Sidebar\Widget;

class Comments extends AbstractWidget
{
    /**
     * @param  \Magento\Framework\View\Element\Template\Context $context,
     * @param  \FishPig\WordPress\Block\Context $wpContext,
     * @param  array $data = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FishPig\WordPress\Block\Context $wpContext,
        \FishPig\WordPress\Model\ResourceModel\Post\Comment\CollectionFactory $commentCollectionFactory,
        array $data = []
    ) {
        $this->commentCollectionFactory = $commentCollectionFactory;
        parent::__construct($context, $wpContext, $data);
    }
    
    /**
     * Retrieve the recent comments collection
     *
     * @return FishPig\WordPress\Model_Mysql4_Post_Comment_Collection
     */
    public function getComments()
    {
        if (!$this->hasComments()) {
            $comments = $this->commentCollectionFactory->create()
                ->addCommentApprovedFilter()
                ->addOrderByDate('desc');

            $comments->getSelect()->limit($this->getNumber() ? $this->getNumber() : 5);

            $this->setComments($comments);
        }

        return $this->getData('comments');
    }

    /**
     * Retrieve the default title
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return __('Recent Comments');
    }

    /**
     * Ensure template is set
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::sidebar/widget/comments.phtml');
        }

        return parent::_beforeToHtml();
    }
}
