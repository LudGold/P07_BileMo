<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApiVersionListener
{
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        
        // Vérifie si la requête concerne l'API
        if (0 === strpos($request->getPathInfo(), '/api/')) {
            $acceptHeader = $request->headers->get('Accept');

            // Déterminer la version en fonction de l'en-tête Accept
            if ('application/vnd.bilemo.v1+json' === $acceptHeader) {
                $request->attributes->set('api_version', 'v1');
            } elseif ('application/vnd.bilemo.v2+json' === $acceptHeader) {
                $request->attributes->set('api_version', 'v2');
            } else {
                // Si l'en-tête Accept est manquant ou incorrect
                throw new BadRequestHttpException('Unsupported API version');
            }
        }
    }
}
