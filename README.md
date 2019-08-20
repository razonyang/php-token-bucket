PHP Token Bucket
================

[![Build Status](https://travis-ci.org/razonyang/php-token-bucket.svg?branch=master)](https://travis-ci.org/razonyang/php-token-bucket)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/razonyang/php-token-bucket/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/razonyang/php-token-bucket/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/razonyang/php-token-bucket/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/razonyang/php-token-bucket/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/razonyang/token-bucket.svg)](https://packagist.org/packages/razonyang/token-bucket)
[![Total Downloads](https://img.shields.io/packagist/dt/razonyang/token-bucket.svg)](https://packagist.org/packages/razonyang/token-bucket)
[![LICENSE](https://img.shields.io/github/license/razonyang/php-token-bucket)](LICENSE)

It is an implementation of [Token Bucket](https://en.wikipedia.org/wiki/Token_bucket) algorithm that for HTTP rate limiter.

Installation
------------

```shell
$ composer require razonyang/token-bucket
```

Integration
-----------

- [PSR-15 Rate Limiter Middleware](https://github.com/razonyang/psr-rate-limiter)
- [Yii2 Rate Limiter](https://github.com/razonyang/yii2-rate-limiter)

You can also build your own, let's take 5000 requests per hours as example:

```php
// create a token bucket manager
$capacity = 5000; // each bucket capacity, in other words, maximum number of tokens.
$rate = 0.72; // 3600/5200, how offen the token will be added to bucket
$logger = new \Psr\Log\NullLogger(); // PSR logger
$ttl = 3600; // time to live.
$prefix = 'rateLimiter:'; // prefix.

$manager = new \RazonYang\TokenBucket\Manager\RedisManager($capacity, $rate, $logger, $redis, $ttl, $prefix);

// implements rate limiter, comsumes a token from the bucket which called $name.
$name = 'uid:route'; // the name of bucket.
$comsumed = $manager->consume($name, $remaining, $reset);

// set header
header('X-Rate-Limit-Limit: ' . $manager->getLimit());
header('X-Rate-Limit-Remaining: ' . $remaining); // remaining number of tokens.
header('X-Rate-Limit-Reset: ' . $reset);

if (!$comsumed) {
    throw new \Exception('Too many requests', 429);
}

// continue handling
```

Credit
------

It was inspired from the follow documents and code:

- [Token Bucket](https://en.wikipedia.org/wiki/Token_bucket)
- [What's a good rate limiting algorithm?](https://stackoverflow.com/questions/667508/whats-a-good-rate-limiting-algorithm)
- [Yii2 Rate Limiter](https://github.com/yiisoft/yii2/blob/master/framework/filters/RateLimiter.php)
