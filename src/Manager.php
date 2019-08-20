<?php
namespace RazonYang\TokenBucket;

use Psr\Log\LoggerInterface;
use RazonYang\TokenBucket\Serializer\PhpSerializer;

/**
 * Manager is an abstract bucket manager that implements ManagerInterface.
 */
abstract class Manager implements ManagerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int $capacity
     */
    private $capacity;

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    /**
     * @var float $rate
     */
    private $rate;

    public function getRate(): float
    {
        return $this->rate;
    }

    /**
     * @var SerializerInterface $serializer
     */
    private $serializer;

    public function __construct(int $capacity, float $rate, LoggerInterface $logger, ?SerializerInterface $serializer = null)
    {
        $this->capacity = $capacity;
        $this->rate = $rate;
        $this->logger = $logger;
        $this->serializer = $serializer ?? new PhpSerializer();
    }

    public function laodAllowance(string $name): array
    {
        $value = $this->load($name);
        if ($value === false) {
            return [$this->capacity, 0];
        }

        try {
            $data = $this->serializer->unserialize($value);
            if (!is_array($data) || count($data) !== 2) {
                throw new \Exception('invalid data');
            }

            return $data;
        } catch (\Throwable $e) {
            $this->logger->error('Unable to load allowance of {name}: {exception}', ['name' => $name, 'exception' => $e->__toString()]);
            return [$this->capacity, 0];
        }
    }

    /**
     * Loads allowance, return data if exists, otherwise false.
     *
     * @param string $name
     *
     * @return mixed|false
     */
    abstract protected function load(string $name);

    public function saveAllowance(string $name, int $allowance, int $timestamp)
    {
        try {
            $value = $this->serializer->serialize([$allowance, $timestamp]);
            $this->save($name, $value);
        } catch (\Throwable $e) {
            $this->logger->error('Unable to save allowance of {name}: {exception}', ['name' => $name, 'exception' => $e->__toString()]);
            return [$this->capacity, 0];
        }
    }

    /**
     * Saves allowance.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws \Throwable throws an exception if save fails.
     */
    abstract protected function save(string $name, $value);

    /**
     * Consumes a token from the bucket.
     *
     * @param string $name bucket name.
     *
     * @return bool returns true on success, otherwise false.
     */
    public function consume(string $name, ?int &$remaining = null, ?int &$reset = null): bool
    {
        list($allowance, $timestamp) = $this->laodAllowance($name);
        $now = time();
        $allowance += intval(($now - $timestamp) / $this->rate);
        if ($allowance > $this->capacity) {
            $allowance = $this->capacity;
        }

        if ($allowance < 1) {
            $remaining = 0;
            $reset = intval($this->capacity * $this->rate) - ($now - $timestamp);
            return false;
        }

        $remaining = $allowance - 1;
        $reset = intval(($this->capacity - $allowance) * $this->rate);
        $this->saveAllowance($name, $remaining, $now);
        return true;
    }

    public function getLimit(int $period): int
    {
        return min($this->capacity, intval($period / $this->rate));
    }
}
