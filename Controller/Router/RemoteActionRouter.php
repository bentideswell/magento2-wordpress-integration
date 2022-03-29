<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Router;

class RemoteActionRouter implements \Magento\Framework\App\RouterInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\RemoteActions $remoteActions
    ) {
        $this->remoteActions = $remoteActions;
    }

    /**
     * @param  \Magento\Framework\App\RequestInterface $request
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $this->remoteActions->listenAndExecute();
        return false;
    }
}
