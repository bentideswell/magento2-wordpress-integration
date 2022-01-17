<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug;

use FishPig\WordPress\App\Debug\TestInterface;

class TestPool
{
    /**
     * @const string
     */
    const RUN_BLOCK_TESTS = 'runBlockTests';
    const ENTITY_LIMIT = 'entityLimit';
    const POST_ID = 'postId';

    /**
     * @var array
     */
    private $tests = [];

    /**
     * @param  array $tests = []
     */
    public function __construct(array $tests = [])
    {
        foreach ($tests as $code => $test) {
            if (false === ($test instanceof TestInterface)) {
                throw new \InvalidArgumentException(
                    'Test ' . $code . ' does not implement ' . TestInterface::class
                );
            }

            $this->tests[$code] = $test;
        }

        if (0 === count($this->tests)) {
            throw new \InvalidArgumentException('No tests registered.');
        }
    }

    /**
     * @return aray
     */
    public function getCodes(): array
    {
        return array_keys($this->tests);
    }

    /**
     * @return void
     */
    public function run(string $code, array $options = []): void
    {
        if (!isset($this->tests[$code])) {
            throw new \InvalidArgumentException(
                'Test ' . $code . ' does not exist.'
            );
        }

        $this->tests[$code]->run($options);
    }
    
    /**
     * @return void
     */
    public function runAll(array $options = []): void
    {
        foreach ($this->tests as $code => $test) {
            $this->run($code, $options);
        }
    }
    
    /**
     * @return int
     */
    public function getTestCount(): int
    {
        return count($this->tests);
    }
}
