<?php
/**
 * @
**/
namespace FishPig\WordPress\Controller\Post;

class Invalidate extends \Magento\Framework\App\Action\Action
{
	/**
	 * @var \FishPig\WordPress\Model\App
	 */
	protected $app;

	/**
	 * @var \FishPig\WordPress\Model\App\Factory
	 */
	protected $factory;

	/**
	  * @var \Magento\Framework\App\CacheInterface
	  */
	protected $cacheManager;

	/**
	 * @var \Magento\Framework\Event\ManagerInterface
	 */
	protected $eventManager;

	/**
	 * Constructor
	 *
	 * @param \Magento\Framework\App\Action\Context $context
	 * @param \FishPig\WordPress\Model\App $app
	 * @param \FishPig\WordPress\Model\App\Factory $factory
	 * @param \Magento\Framework\App\CacheInterface $cacheManager
	 * @param \Magento\Framework\Event\ManagerInterface $eventManager
	 */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\FishPig\WordPress\Model\App $app,
		\FishPig\WordPress\Model\App\Factory $factory,
		\Magento\Framework\App\CacheInterface $cacheManager
		)
	{
		$this->app = $app;
		$this->factory = $factory;
		$this->cacheManager = $cacheManager;
		$this->eventManager = $context->getEventManager();

		parent::__construct($context);
	}

	/**
	 * @return void
	 */
	public function execute()
	{
		if ($this->invalidateCache()) {
			$result = array('result' => 'success');
		} else {
			$result = array('result' => 'failure');
		}

		$this->getResponse()->appendBody(json_encode($result));
	}

	/**
	 * Attempt to invalidate cache entry
	 */
	protected function invalidateCache()
	{
		$postId = $this->getRequest()->getParam('id');

		$nonce = $this->getRequest()->getParam('nonce');
		if (!$this->verifyNonce($nonce, 'invalidate_' . $postId)) {
			return false;
		}

		$post = $this->factory->getFactory('Post')->create()->load($postId);
		if (!$post) {
			return false;
		}

		// Clean cache related objects and then allow FPC plugins to do the same
		$post->cleanModelCache();
		$this->eventManager->dispatch('clean_cache_by_tags', ['object' => $post]);
		return true;
	}

	/**
	 * Validate given nonce
	 */
	protected function verifyNonce($nonce, $action)
	{
		$salt = $this->app->getConfig()->getOption('fishpig_salt');
		if (!$salt) {
			return false;
		}

		$nonce_tick = ceil(time() / ( 86400 / 2 ));

		// 0-12 hours
		if (substr(hash_hmac('sha256', $nonce_tick . '|fishpig|' . $action, $salt), -12, 10) == $nonce) {
			return true;
		}

		// 12-24 hours
		if (substr(hash_hmac('sha256', ($nonce_tick - 1) . '|fishpig|' . $action, $salt), -12, 10) == $nonce) {
			return true;
		}

		return false;
	}
}
