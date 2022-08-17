<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class JsonBody
{
//    public static function getSubscribedEvents()
//    {
////       dump($event->getController());
//        $data = json_decode($request->getContent(), true, 128, \JSON_THROW_ON_ERROR);
//
//        if ($data === null) {
//            return $request;
//        }
//
//        $request->request->replace($data);
//        return $request;
//    }

}
