<?php
namespace RazonYang\TokenBucket\Tests;

use Psr\Log\NullLogger;
use RazonYang\TokenBucket\Manager\MemcachedManager;

class MemcachedManagerTest extends TestCase
{
    private $capacity = 10;

    private $rate = 1;

    private $ttl = 60;

    private $prefix = 'test:';

    /**
     * @var \Memcached
     */
    private $memcached;

    public function setUp(): void
    {
        parent::setUp();

        $memcached = new \Memcached();
        $memcached->addServer('localhost', 11211);
        $this->memcached = $memcached;
    }

    public function tearDown(): void
    {
        $this->memcached = null;

        parent::tearDown();
    }

    public function createManager(): MemcachedManager
    {
        return new MemcachedManager($this->capacity, $this->rate, new NullLogger(), $this->memcached, $this->ttl, $this->prefix);
    }

    public function testSetUp(): void
    {
        $manager = $this->createManager();

        $ttl = new \ReflectionProperty(MemcachedManager::class, 'ttl');
        $ttl->setAccessible(true);
        $this->assertSame($this->ttl, $ttl->getValue($manager));

        $prefix = new \ReflectionProperty(MemcachedManager::class, 'prefix');
        $prefix->setAccessible(true);
        $this->assertSame($this->prefix, $prefix->getValue($manager));

        $conn = new \ReflectionProperty(MemcachedManager::class, 'conn');
        $conn->setAccessible(true);
        $this->assertSame($this->memcached, $conn->getValue($manager));
    }

    public function testSaveAndLoad(): void
    {
        $manager = $this->createManager();
        $save = new \ReflectionMethod(MemcachedManager::class, 'save');
        $save->setAccessible(true);

        $load = new \ReflectionMethod(MemcachedManager::class, 'load');
        $load->setAccessible(true);

        $name = 'test';
        $data = uniqid();
        $save->invoke($manager, $name, $data);

        $this->assertSame($data, $load->invoke($manager, $name));

        $this->assertSame($data, $this->memcached->get($this->prefix . $name));
    }
}
