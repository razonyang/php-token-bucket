<?php
namespace RazonYang\TokenBucket\Tests;

use RazonYang\TokenBucket\Serializer\PhpSerializer;

class PhpSerializerTest extends TestCase
{
    /**
     * @dataProvider dataProviderSerialize
     */
    public function testSerialize($data): void
    {
        $serializer = new PhpSerializer();
        $value = $serializer->serialize($data);
        $this->assertEquals(serialize($data), $value);
        $this->assertEquals(unserialize($value), $serializer->unserialize($value));
    }

    public function dataProviderSerialize(): array
    {
        return [
            [1],
            ['foo'],
            [['bar']],
        ];
    }
}
