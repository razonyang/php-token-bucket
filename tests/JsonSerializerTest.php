<?php
namespace RazonYang\TokenBucket\Tests;

use RazonYang\TokenBucket\Serializer\JsonSerializer;

class JsonSerializerTest extends TestCase
{
    /**
     * @dataProvider dataProviderSerialize
     */
    public function testSerialize($data): void
    {
        $serializer = new JsonSerializer();
        $value = $serializer->serialize($data);
        $this->assertEquals(json_encode($data), $value);
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
