<?php
namespace RazonYang\TokenBucket;

/**
 * ManagerInterface defines interfaces of token bucket manager.
 * It manages token buckets those has same capacity and rate.
 */
interface ManagerInterface
{
    /**
     * Returns the rate of how often the token be add to each bucket. i.e. 1/2 represents that 2 tokens
     * were added to each bucket every seconds, that is, the rate limit is 2 requests per second.
     *
     * @return float
     */
    public function getRate(): float;

    /**
     * Returns the capacity of each bucket.
     *
     * @return int
     */
    public function getCapacity(): int;

    /**
     * Loads last allowance and corresponding UNIX timestamp of the bucket.
     *
     * @param string $name bucket name.
     *
     * @return array an array that contains two elements, first is the last allowance,
     * and the other is the corresponding UNIX timestamp.
     */
    public function laodAllowance(string $name): array;

    /**
     * Saves the bucket's allowance and corresponding UNIX timestamp.
     *
     * @param string $name      bucket name.
     * @param int    $allowance current allowance.
     * @param int    $timestamp current UNIX timestamp.
     *
     * @throws \Throwable throws an exception on save fails.
     */
    public function saveAllowance(string $name, int $allowance, int $timestamp);

    /**
     * Returns maximum number of tokens during a period in seconds, it won't large than the
     * capacity of the bucket.
     *
     * @param int $period in seconds.
     *
     * @return int
     */
    public function getLimit(int $period): int;

    /**
     * Consumes a token from the bucket.
     *
     * @param string   $name      the bucket name.
     * @param null|int $remaining the remaining number of tokens within the current period.
     * @param null|int $reset     the number of seconds to wait before full filling bucket.
     *
     * @return bool whether consumes a token successfully.
     */
    public function consume(string $name, ?int &$remaining = null, ?int &$reset = null): bool;
}
