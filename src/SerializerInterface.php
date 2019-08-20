<?php
namespace RazonYang\TokenBucket;

interface SerializerInterface
{
    /**
     * Serializes data.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function serialize($data);

    /**
     * Unserializes value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function unserialize($value);
}
