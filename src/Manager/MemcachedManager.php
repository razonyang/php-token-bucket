<?php
namespace RazonYang\TokenBucket\Manager;

use Psr\Log\LoggerInterface;
use RazonYang\TokenBucket\Manager;
use RazonYang\TokenBucket\SerializerInterface;

class MemcachedManager extends Manager
{
    /**
     * @var int $ttl time to live.
     */
    private $ttl;

    /**
     * @var string $prefix key prefix.
     */
    private $prefix;

    /**
     * @var \Memcached $conn
     */
    private $conn;

    public function __construct(
        int $capacity,
        float $rate,
        LoggerInterface $logger,
        \Memcached $conn,
        int $ttl = 0,
        string $prefix = '',
        ?SerializerInterface $serializer = null
    ) {
        parent::__construct($capacity, $rate, $logger, $serializer);
        $this->conn = $conn;
        $this->ttl = $ttl;
        $this->prefix = $prefix;
    }

    protected function load(string $name)
    {
        return $this->conn->get($this->getKey($name));
    }

    protected function save(string $name, $data)
    {
        if (!$this->conn->set($this->getKey($name), $data, $this->ttl)) {
            throw new \RuntimeException($this->conn->getResultCode() . ':' . $this->conn->getResultMessage());
        }
    }

    protected function getKey(string $name): string
    {
        return $this->prefix . $name;
    }
}
