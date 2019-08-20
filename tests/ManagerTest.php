<?php
namespace RazonYang\TokenBucket\Tests;

use Psr\Log\NullLogger;
use RazonYang\TokenBucket\Manager;
use RazonYang\TokenBucket\SerializerInterface;
use RazonYang\TokenBucket\Serializer\JsonSerializer;
use RazonYang\TokenBucket\Serializer\PhpSerializer;
use RazonYang\TokenBucket\Serializer\RawSerializer;

class ManagerTest extends TestCase
{
    public function createManager($capacity, $rate, ?SerializerInterface $serializer = null): TestManager
    {
        return new TestManager($capacity, $rate, new NullLogger(), $serializer);
    }

    /**
     * @dataProvider dataProviderSetUp
     */
    public function testSetUp(int $capacity, float $rate, ?SerializerInterface $serializer = null): void
    {
        $manager = $this->createManager($capacity, $rate, $serializer);
        $this->assertSame($capacity, $manager->getCapacity());
        $this->assertSame($rate, $manager->getRate());
        $property = new \ReflectionProperty(Manager::class, 'serializer');
        $property->setAccessible(true);
        if ($serializer) {
            $this->assertSame($serializer, $property->getValue($manager));
        } else {
            $this->assertNull($property->getValue($manager));
        }
    }

    public function dataProviderSetUp(): array
    {
        return [
            [1, 0.1, new JsonSerializer(0)],
            [2, 0.5, new JsonSerializer()],
            [3, 1, new PhpSerializer()],
            [4, 2, new RawSerializer()],
        ];
    }

    /**
     * @dataProvider dataProviderLimit
     */
    public function testGetLimit(int $capacity, float $rate, int $period, int $limit): void
    {
        $manager = $this->createManager($capacity, $rate);
        $this->assertSame($limit, $manager->getLimit($period));
    }

    public function dataProviderLimit(): array
    {
        return [
            [60, 1, 10, 10],
            [60, 1, 60, 60],
            [60, 1, 120, 60],
        ];
    }

    public function testConsume(): void
    {
        $capacity = 2;
        $rate = 2;
        $manager = $this->createManager($capacity, $rate);
        $name = 'test';
        for ($i = 0; $i < $capacity; $i++) {
            $this->assertTrue($manager->consume($name));
        }
        $this->assertFalse($manager->consume($name));

        sleep($rate);
        $this->assertTrue($manager->consume($name));
    }

    public function testlaodAllowance(): void
    {
        $capacity = 10;
        $manager = $this->createManager($capacity, 1);

        $name = 'test';
        $manager->data[$name] = 'invalid data';
        $data = $manager->laodAllowance($name);
        $this->assertSame($capacity, $data[0]);
    }
}
