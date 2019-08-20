<?php
namespace RazonYang\TokenBucket\Manager;

use Psr\Log\LoggerInterface;
use RazonYang\TokenBucket\Manager;
use RazonYang\TokenBucket\SerializerInterface;

class RedisManager extends Manager
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
     * @var \Redis $conn
     */
    private $conn;

    public function __construct(
        int $capacity,
        float $rate,
        LoggerInterface $logger,
        \Redis $conn,
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
        if ($this->ttl > 0) {
            $saved = $this->conn->setEx($this->getKey($name), $this->ttl, $data);
        } else {
            $saved = $this->conn->set($this->getKey($name), $data);
        }
        if (!$saved) {
            throw new \RuntimeException($this->conn->getLastError());
        }
    }

    protected function getKey(string $name): string
    {
        return $this->prefix . $name;
    }
}
