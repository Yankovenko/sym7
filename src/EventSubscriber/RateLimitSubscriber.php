<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class RateLimitSubscriber implements EventSubscriberInterface
{
    private RateLimiterFactory $rateLimiter;

    public function __construct(RateLimiterFactory $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->attributes->get('_route')
            && str_starts_with($request->attributes->get('_controller'), 'App\Controller\Api\\')
        ) {
            $limiter = $this->rateLimiter->create();

            if (!$limiter->consume(1)->isAccepted()) {
                throw new TooManyRequestsHttpException('Rate limit exceeded');
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
