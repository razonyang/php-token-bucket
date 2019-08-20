<?php
namespace RazonYang\TokenBucket\Serializer;

use RazonYang\TokenBucket\SerializerInterface;

class PhpSerializer implements SerializerInterface
{
    public function serialize($data)
    {
        return serialize($data);
    }

    public function unserialize($value)
    {
        return unserialize($value);
    }
}
