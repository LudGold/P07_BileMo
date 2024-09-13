<?php

namespace App\Service;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheService
{
    private TagAwareCacheInterface $cachePool;

    public function __construct(TagAwareCacheInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    public function getCacheData(string $cacheId, callable $callback, array $tags = [], int $expiresAfter = 3600)
    {
        return $this->cachePool->get($cacheId, function (ItemInterface $item) use ($callback, $tags, $expiresAfter) {
            $item->expiresAfter($expiresAfter);
            if (!empty($tags)) {
                $item->tag($tags);
            }

            return $callback();
        });
    }
    public function invalidateCache(array $tags): void
    {
        $this->cachePool->invalidateTags($tags);
    }
}