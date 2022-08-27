<?php

namespace App\EventListener;

use App\Exception\CustomErrorException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'processException'

        ];
    }

    public function processException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
//        dump($event);
//        dump($exception);
//        dump($exception->getCode());
        if ($exception instanceof NotFoundHttpException) {
            $response = new JsonResponse($exception->getMessage(), $exception->getCode());
            $event->setResponse($response);
        }
        if ($exception instanceof CustomErrorException) {
            $response = new JsonResponse($exception->getViolations(), $exception->getCode());
            $event->setResponse($response);
        }
    }

}
