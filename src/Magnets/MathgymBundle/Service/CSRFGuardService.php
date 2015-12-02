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

use Magnets\MathgymBundle\Controller\IndexController;
use Magnets\MathgymBundle\MagnetsMathgymBundle;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

class CSRFGuardService
{

    protected $router;
    protected $tokenManager;

    public function __construct(Router $router,CsrfTokenManager $tokenManager)
    {
        $this->router = $router;
        $this->tokenManager = $tokenManager;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        //check csrf protection:
        /* strategy to use: for now, will explicitly define in options
        */
        //$event->getRequest()
        if(!$event->isMasterRequest()){
            return;
        }
        if ($this->router->getRouteCollection()->get($event->getRequest()->get('_route'))->getOption('require_csrf')) {
            if ($event->getRequest()->request->get('csrfToken') !== null) {
                $usrToken = new CsrfToken('base',$event->getRequest()->request->get('csrfToken'));
                if($this->tokenManager->isTokenValid($usrToken)){
                    return;
                }
            }
            throw new AccessDeniedException('Invalid CSRF token! You cannot access this page.');
            $event->setController([new IndexController, 'csrfError']);
        }
    }
}