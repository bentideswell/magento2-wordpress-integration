<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\ResourceConnection;
use FishPig\WordPress\Model\OptionManager;
use FishPig\WordPress\Model\ShortcodeManager;
use FishPig\WordPress\Model\PostTypeManager\Proxy as PostTypeManager;
use FishPig\WordPress\Model\TaxonomyManager\Proxy as TaxonomyManager;
use FishPig\WordPress\Model\Url;
use FishPig\WordPress\Model\Factory;
use FishPig\WordPress\Helper\Date as DateHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http as Request;
use Magento\Store\Model\StoreManagerInterface;
use FishPig\WordPress\Model\Logger;
use FishPig\WordPress\Model\DirectoryList;

class Context
{
    /**
     *
     * @var 
     *
     */
    protected $resourceManager;

    /**
     *
     * @var 
     *
     */
    protected $optionManager;

    /**
     *
     * @var 
     *
     */
    protected $shortcodeManager;

    /**
     *
     * @var 
     *
     */
    protected $postTypeManager;

    /**
     *
     * @var 
     *
     */
    protected $taxonomyManager;

    /**
     *
     * @var 
     *
     */
    protected $url;

    /**
     *
     * @var 
     *
     */
    protected $factory;

    /**
     *
     * @var 
     *
     */
    protected $dateHelper;

    /**
     *
     * @var 
     *
     */
    protected $registry;

    /**
     *
     * @var 
     *
     */
    protected $customerSession;

    /**
     *
     * @var 
     *
     */
    protected $request;

    /**
     *
     * @var StoreManagerInterface
     *
     */
    protected $storeManager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     *
     *
     *
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        OptionManager $optionManager,
        ShortcodeManager $shortcodeManager,
        PostTypeManager $postTypeManager,
        TaxonomyManager $taxonomyManager,
        Url $url,
        Factory $factory,
        DateHelper $dateHelper,
        Registry $registry,
        Layout $layout,
        CustomerSession $customerSession,
        Request $request,
        StoreManagerInterface $storeManager,
        Logger $logger,
        DirectoryList $directoryList
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->optionManager      = $optionManager;
        $this->shortcodeManager   = $shortcodeManager;
        $this->postTypeManager    = $postTypeManager;
        $this->taxonomyManager    = $taxonomyManager;
        $this->url                = $url;
        $this->factory            = $factory;
        $this->dateHelper         = $dateHelper;
        $this->registry           = $registry;
        $this->layout             = $layout;
        $this->customerSession    = $customerSession;
        $this->request            = $request;
        $this->storeManager       = $storeManager;
        $this->logger             = $logger;
        $this->directoryList      = $directoryList;
    }

    /**
     *
     *
     * @return 
     */
    public function getResourceConnection()
    {
        return $this->resourceConnection;
    }

    /**
     *
     *
     * @return 
     */
    public function getOptionManager()
    {
        return $this->optionManager;
    }

    /**
     *
     *
     * @return 
     */
    public function getShortcodeManager()
    {
        return $this->shortcodeManager;
    }

    /**
     *
     *
     * @return 
     */
    public function getTaxonomyManager()
    {
        return $this->taxonomyManager;
    }

    /**
     *
     *
     * @return 
     */
    public function getPostTypeManager()
    {
        return $this->postTypeManager;
    }

    /**
     *
     *
     * @return 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     *
     *
     * @return 
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     *
     *
     * @return 
     */
    public function getDateHelper()
    {
        return $this->dateHelper;
    }

    /**
     *
     *
     * @return 
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     *
     *
     * @return 
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     *
     *
     * @return 
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     *
     *
     * @return 
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     *
     *
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     *
     *
     * @return Logger
     */
    public function getLogger()
    {
    return $this->logger;
    }

    /**
     *
     * @return DirectoryList
     */
    public function getDirectoryList()
    {
    return $this->directoryList;
    }
}
