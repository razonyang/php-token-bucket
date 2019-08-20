<?php
namespace RazonYang\TokenBucket\Tests;

use Psr\Log\NullLogger;
use RazonYang\TokenBucket\Manager\RedisManager;

class RedisManagerTest extends TestCase
{
    private $capacity = 10;

    private $rate = 1;

    private $ttl = 60;

    private $prefix = 'test:';

    /**
     * @var \Redis
     */
    private $redis;

    public function setUp(): void
    {
        parent::setUp();

        $redis = new \Redis();
        $redis->connect('localhost', 6379);
        $redis->auth('foobar');
        $this->redis = $redis;
    }

    public function tearDown(): void
    {
        $this->redis = null;

        parent::tearDown();
    }

    public function createManager(): RedisManager
    {
        return new RedisManager($this->capacity, $this->rate, new NullLogger(), $this->redis, $this->ttl, $this->prefix);
    }

    public function testSetUp()
    {
        $manager = $this->createManager();

        $ttl = new \ReflectionProperty(RedisManager::class, 'ttl');
        $ttl->setAccessible(true);
        $this->assertSame($this->ttl, $ttl->getValue($manager));

        $prefix = new \ReflectionProperty(RedisManager::class, 'prefix');
        $prefix->setAccessible(true);
        $this->assertSame($this->prefix, $prefix->getValue($manager));

        $conn = new \ReflectionProperty(RedisManager::class, 'conn');
        $conn->setAccessible(true);
        $this->assertSame($this->redis, $conn->getValue($manager));
    }

    public function testSaveAndLoad(): void
    {
        $manager = $this->createManager();
        $save = new \ReflectionMethod(RedisManager::class, 'save');
        $save->setAccessible(true);

        $load = new \ReflectionMethod(RedisManager::class, 'load');
        $load->setAccessible(true);

        $name = 'test';
        $data = uniqid();
        $save->invoke($manager, $name, $data);

        $this->assertSame($data, $load->invoke($manager, $name));
        $this->assertSame($data, $this->redis->get($this->prefix . $name));
        $this->assertTrue($this->redis->ttl($this->prefix . $name) > 0);
    }

    public function testSaveWithoutTtl(): void
    {
        $manager = new RedisManager($this->capacity, $this->rate, new NullLogger(), $this->redis, 0, $this->prefix);
        $save = new \ReflectionMethod(RedisManager::class, 'save');
        $save->setAccessible(true);
        $name = 'test';
        $data = uniqid();
        $save->invoke($manager, $name, $data);
        $this->assertEquals(-1, $this->redis->ttl($this->prefix . $name));
    }
}
