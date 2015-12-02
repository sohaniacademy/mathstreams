<?php

/*
 *  This file is part of Mathstreams.
 *
 *  Copyright (c) 2015  Sohani Academy <developer@sohaniacademy.com>
 *
 *  Mathstreams is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Magnets\MathgymBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class AjaxHelperService
{
    protected $twig;
    protected $rStack;
    protected $flashBag;

    public function __construct(TwigEngine $twig, RequestStack $rStack, FlashBag $flashBag)
    {
        $this->twig = $twig;
        $this->rStack = $rStack;
        $this->flashBag = $flashBag;
    }

//    public function renderFrag($view, array $parameters = array(), Response $response = null)
//    {
//        if($this->rStack->getParentRequest() == null){
//            $parameters['isMasterRequest'] = true;
//        }
//        return $this->twig->renderResponse($view, $parameters, $response);
//    }
//
//
//    public function renderFrag(Response $response)
//    {
//        return $this->twig->render('MagnetsMathgymBundle::layout.json.twig', ['bodyContent' => $response->getContent()]);
//        /*
//            If it is a nonXHR, just render the contents..
//            If it is an XHR, but is a master request, then render into JSON wrapper
//            If it is an XHR, but not  master request, then render the contents..
//            How to detect if it is the master request or not?..
//            Seems that it may not be possible at twig level
//            so for now, we do it here..
//        */
//        if (!$this->rStack->getCurrentRequest()->isXmlHttpRequest()
//            || $this->rStack->getParentRequest() !== null
//        ) {
//            return $response;
//        } else {
//            return $this->twig->render('MagnetsMathgymBundle::layout.json.twig', ['bodyContent' => $response->getContent()]);
//        }
//    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        } else if ($event->getResponse() instanceof JsonResponse || $event->getResponse() instanceof RedirectResponse) {
            return;
        } else if (strpos($event->getRequest()->attributes->get('_controller'), 'Magnets\MathgymBundle') !== false) {
            if ($event->getRequest()->isXmlHttpRequest()) {
                $event->setResponse(new JsonResponse(['status' => 'OK', 'body' => $event->getResponse()->getContent(), 'flash' => $this->flashBag->all()]));
            } else {
                //todo: can we avoid a second Twig call somehow..
                $event->setResponse($this->twig->renderResponse('MagnetsMathgymBundle::layout.html.twig', ['body' => $event->getResponse()->getContent()]));
            }
        }
    }

//    {
//        if (!$event->isMasterRequest()) {
//            return;
//        }
//        if ($event->getResponse() instanceof RedirectResponse) {
//            return;
//        }
//
//        $bypassList = ["/_wdt", "/_profiler", "/_console", "/js/routing"];
//        foreach ($bypassList as $str) {
//            if (strpos($event->getRequest()->getUri(), $str) !== false)
//                return;
//        }
//
//        if ($event->getRequest()->isXmlHttpRequest()) {
//            if ($event->getResponse() instanceof JsonResponse) {
//                return;
//            } else {
//                $event->setResponse(new JsonResponse(['body' => $event->getResponse()->getContent(),'flash' => $this->flash->all()]));
//            }
//        } else {
//            $event->setResponse($this->twig->renderResponse('MagnetsMathgymBundle::layout.html.twig', ['bodyContent' => $event->getResponse()->getContent()]));
//        }
//    }
}
