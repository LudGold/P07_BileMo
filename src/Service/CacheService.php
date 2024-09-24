<?php

namespace App\Service;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Service de gestion du cache avec prise en charge des tags.
 */
class CacheService
{
    private TagAwareCacheInterface $cachePool;

    /**
     * Constructeur du CacheService.
     * 
     * @param TagAwareCacheInterface $cachePool Interface de cache avec prise en charge des tags.
     */
    public function __construct(TagAwareCacheInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    /**
     * Récupère les données mises en cache ou les génère si elles n'existent pas.
     * 
     * @param string $cacheId L'identifiant unique du cache.
     * @param callable $callback Fonction de rappel pour générer les données si elles ne sont pas en cache.
     * @param array $tags Tags associés à l'élément de cache pour une gestion plus fine de l'invalidation.
     * @param int $expiresAfter Temps en secondes avant l'expiration du cache (par défaut 3600 secondes).
     * 
     * @return mixed Les données mises en cache ou générées.
     */
    public function getCacheData(string $cacheId, callable $callback, array $tags = [], int $expiresAfter = 3600)
    {
        // Récupère les données du cache ou exécute le callback pour les générer, puis les met en cache.
        return $this->cachePool->get($cacheId, function (ItemInterface $item) use ($callback, $tags, $expiresAfter) {
            // Définit la durée d'expiration du cache.
            $item->expiresAfter($expiresAfter);

            // Ajoute des tags à l'élément de cache si fournis.
            if (!empty($tags)) {
                $item->tag($tags);
            }

            // Retourne le résultat du callback qui génère les données à mettre en cache.
            return $callback();
        });
    }

    /**
     * Invalide tous les éléments de cache associés aux tags donnés.
     * 
     * @param array $tags Liste des tags à invalider.
     * 
     * @return void
     */
    public function invalidateCache(array $tags): void
    {
        // Invalide tous les éléments de cache liés aux tags spécifiés.
        $this->cachePool->invalidateTags($tags);
    }
}
