<?php

namespace App\EventSubscriber;


use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ApiExceptionSubscriber implements EventSubscriberInterface
{

    public function __construct(
        readonly private RequestStack $requestStack,
        #[Autowire(param: 'kernel.debug')] readonly bool $debug
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $response = new JsonResponse(null, 500);
        $throw = $event->getThrowable();
        $data = [
            'status' => 'error',
            'message' => $throw->getMessage(),
        ];

        if ($throw instanceof HttpException) {
            $response->setStatusCode($throw->getStatusCode());
        }

        if ($throw instanceof RequestExceptionInterface) {
            $response->setStatusCode(500);
        }

        if ($throw instanceof AuthenticationException) {
            $response->setStatusCode(401);
        }

        if ($throw instanceof NotFoundExceptionInterface) {
            $response->setStatusCode(404);
        }

        if ($this->debug) {
            do {
                $data['debug'][] = [
                    'message' => $throw->getMessage(),
                    'file' => $throw->getFile(),
                    'line' => $throw->getLine(),
                ];

                if (!$throw->getPrevious()) {
                    $data['debug']['trace'] = explode("\n", $throw->getTraceAsString());
                }
            } while ($throw = $throw->getPrevious());
        }

        $response->setData($data);
        $event->setResponse($response);
    }
}
