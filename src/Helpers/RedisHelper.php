<?php

namespace App\Helpers;

use Clue\React\Redis\Client;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

class RedisHelper
{
    protected Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function get(string $key): PromiseInterface|Promise
    {
        return $this->redis
            ->get($key)
            ->then(function ($data) {
                return [
                    'result' => true,
                    'data' => $data
                ];
            }, function (\Exception $exception) {
                return [
                    'result' => false,
                    'error' => $exception->getMessage()
                ];
            });
    }

    public function set(string $key, string $value, int $ttl = null): PromiseInterface|Promise
    {
        return ((!is_null($ttl))
            ? $this->redis->set($key, $value, "EX", $ttl)
            : $this->redis->set($key, $value)
        )->then(function () {
            return [
                'result' => true
            ];
        }, function (\Exception $exception) {
            return [
                'result' => false,
                'error' => $exception->getMessage()
            ];
        });
    }

    public function hSet(string $key, string $hashKey, string $value): PromiseInterface|Promise
    {
        return $this->redis
            ->hSet($key, $hashKey, $value)
            ->then(function () {
                return [
                    'result' => true,
                ];
            }, function (\Exception $exception) {
                return [
                    'result' => false,
                    'error' => $exception->getMessage()
                ];
            });
    }

    public function hGet(string $key, string $hashKey): PromiseInterface|Promise
    {
        return $this->redis
            ->hGet($key, $hashKey)
            ->then(function ($data) {
                return [
                    'result' => true,
                    'data' => $data
                ];
            }, function (\Exception $exception) {
                return [
                    'result' => false,
                    'error' => $exception->getMessage()
                ];
            });
    }


    public function sAdd(string $key, ...$values): PromiseInterface|Promise
    {
        return $this->redis
            ->sAdd($key, $values)
            ->then(function () {
                return [
                    'result' => true
                ];
            }, function (\Exception $exception) {
                return [
                    'result' => false,
                    'error' => $exception->getMessage()
                ];
            });
    }

    public function sIsMember(string $key, string $value): PromiseInterface|Promise
    {
        return $this->redis
            ->sIsMember($key, $value)
            ->then(function ($data) {
                return [
                    'result' => true,
                    'data' => $data
                ];
            }, function (\Exception $exception) {
                return [
                    'result' => false,
                    'error' => $exception->getMessage()
                ];
            });
    }
}