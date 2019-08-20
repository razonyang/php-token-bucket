<?php
namespace RazonYang\TokenBucket\Tests;

use RazonYang\TokenBucket\Serializer\RawSerializer;

class RawSerializerTest extends TestCase
{
    /**
     * @dataProvider dataProviderSerialize
     */
    public function testSerialize($data): void
    {
        $serializer = new RawSerializer();
        $value = $serializer->serialize($data);
        $this->assertEquals($data, $value);
        $this->assertEquals($data, $serializer->unserialize($value));
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
