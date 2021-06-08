<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

class CacheInvalidator
{
    /**
     * @const string
     */
    const NONCE_PARAM = '_fp_invalidate';

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\OptionManager $optionManager,
        \FishPig\WordPress\Model\Logger $logger,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\App\RequestInterface $request,
        array $factories = []
    ) {
        $this->optionManager = $optionManager;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->request = $request;
        $this->factories = $factories;
    }
    
    /**
     *
     */
    public function invalidateAction(\FishPig\WordPress\Controller\Action $action, $nonce = null)
    {
        try {
            if (($nonce = $nonce ?? $this->request->getParam(self::NONCE_PARAM, null)) === null) {
                return;
            }

            $this->invalidate($action->getEntityObject(), $nonce);
            
            $result = [
                'result' => 'success',
            ];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            
            $result = [
                'result' => 'failure',
                'error_message' => $e->getMessage()
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    
    /**
     *
     */
    public function invalidate(\FishPig\WordPress\Model\AbstractModel $object, $nonce)
    {
        if (!$nonce) {
            throw new \Magento\Framework\Exception\InvalidArgumentException(__('Please specify a nonce'));
        }

        if (!$this->verifyNonce($nonce, 'invalidate_' . $object::ENTITY . '_' . (int)$object->getId())) {
            throw new \Magento\Framework\Exception\AuthorizationException(__('You are not authorized to do this.'));
        }
        
        if (!$object->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Unable to find an object with that ID.'));
        }

        if (!isset($this->factories[$object::ENTITY])) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Invalid invalidation type given.'));
        }

        // Clean cache related objects and then allow FPC plugins to do the same
        $object->cleanModelCache();
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $object]);
        
        return true;
    }
    
    /**
     * Validate given nonce
     */
    private function verifyNonce($nonce, $action): bool
    {
        if (!($salt = $this->optionManager->getOption('fishpig_salt'))) {
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
