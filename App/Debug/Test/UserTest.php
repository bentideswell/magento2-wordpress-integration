<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

use FishPig\WordPress\App\Debug\TestPool;

class UserTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\UserRepository $userRepository,
        \FishPig\WordPress\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Magento\Framework\View\Layout $layout
    ) {
        $this->userRepository = $userRepository;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->layout = $layout;
    }
    
    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        foreach ($this->userCollectionFactory->create()->load() as $user) {
            $user = $this->userRepository->get($user->getId());
            $user->getName();
            $user->getUrl();
            $user->getPostCollection();
            $user->getContent();
            $user->getImage();
            $user->getTablePrefix();
            $user->getRole();
            $user->getUserLevel();
            $user->getFirstName();
            $user->getLastName();
            $user->getNickname();
            $user->getGravatarUrl();
            $user->getResource();
            $user->getCollection();
            
            if (isset($options[TestPool::RUN_BLOCK_TESTS]) && $options[TestPool::RUN_BLOCK_TESTS] === true) {
                $this->layout->createBlock(\FishPig\WordPress\Block\User\View::class)->setUser($user)->toHtml();
            }
        }
    }
}
