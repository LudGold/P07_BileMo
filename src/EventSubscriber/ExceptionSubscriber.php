<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : JsonResponse::HTTP_INTERNAL_SERVER_ERROR;

        // Log the error message
        $this->logger->error('An error occurred', [
            'exception' => $exception,
            'status_code' => $statusCode,
        ]);

        $message = $exception->getMessage();

        // Customize the message for specific exceptions
        if ($exception instanceof NotFoundHttpException) {
            $message = 'The requested resource was not found.';
        } elseif ($exception instanceof AccessDeniedHttpException) {
            $message = 'You do not have permission to access this resource.';
        } elseif ($exception instanceof AuthenticationException) {
            $message = 'Authentication failed.';
        } elseif ($exception instanceof BadCredentialsException) {
            $message = 'Invalid credentials.';
        } elseif (empty($message)) {
            // Provide a default message for common HTTP status codes
            switch ($statusCode) {
                case JsonResponse::HTTP_NOT_FOUND:
                    $message = 'Resource not found';
                    break;
                case JsonResponse::HTTP_FORBIDDEN:
                    $message = 'Access denied';
                    break;
                case JsonResponse::HTTP_INTERNAL_SERVER_ERROR:
                default:
                    $message = 'An unexpected error occurred';
                    break;
            }
        }

        $response = new JsonResponse([
            'error' => [
                'code' => $statusCode,
                'message' => $message,
            ]
        ], $statusCode);

        $event->setResponse($response);
    }
}
