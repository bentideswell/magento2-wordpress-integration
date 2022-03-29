<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

use FishPig\WordPress\App\RemoteActions\RemoteActionInterface;

class RemoteActions
{
    /**
     * @const string
     */
    const PARAM_NAME = 'fishpig-wp';

    /**
     * @var array
     */
    private $remoteActionPool = [];
    
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request = null;

    /**
     * @param \FishPig\WordPress\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \FishPig\WordPress\App\HTTP\AuthorisationKey $authorisationKey,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $remoteActionPool = []
    ) {
        $this->request = $request;
        $this->authorisationKey = $authorisationKey;
        $this->serializer = $serializer;

        foreach ($remoteActionPool as $remoteActionId => $remoteAction) {
            if (false === ($remoteAction instanceof RemoteActionInterface)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('%1 must be an instance of %2.', get_class($remoteAction), RemoteActionInterface::class)
                );
            }
            
            $this->remoteActionPool[$remoteActionId] = $remoteAction;
        }
    }

    /**
     *
     */
    public function listenAndExecute(): void
    {
        if (null === ($actionArgs = $this->getArgs())) {
            return;
        }
        
        if (empty($actionArgs['action']) || empty($actionArgs['key'])) {
            return;
        }

        if (false === $this->authorisationKey->isValidKey($actionArgs['key'])) {
            // Invalid key
            return;
        }

        if (!isset($this->remoteActionPool[$actionArgs['action']])) {
            return;
        }
        
        $result = $this->remoteActionPool[$actionArgs['action']]->run($actionArgs);
        
        if ($result === null) {
            return;
        }
        
        header('Content-Type: application/json; charset=utf-8');
        echo $this->serializer->serialize($result);
        exit;
    }
    
    /**
     * @return ?array
     */
    private function getArgs(): ?array
    {
        return $this->request->getParam(self::PARAM_NAME) ?? null;
    }
}
