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

use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Role\Role;

class RateControlService
{
    protected $tokenStorage;
    protected $roleHierarchy;
    protected $flashBag;
    protected $hardLims;
    protected $rcLims;

    public function __construct(TokenStorage $tokenStorage, RoleHierarchy $roleHierarchy, FlashBag $flashBag, $hardLims, $rcLims)
    {
        $this->tokenStorage = $tokenStorage;
        $this->roleHierarchy = $roleHierarchy;
        $this->flashBag = $flashBag;
        $this->hardLims = $hardLims;
        $this->rcLims = $rcLims;
    }

    public function getUserNull($user)
    {
        if ($user == null) {
            return $this->tokenStorage->getToken()->getUser();
        } else return $user;
    }

    public function getRcStrategy($user)
    {
        //based on user's role heirarchy..
        $allRoles = array_map(function ($x) {
            return $x->getRole();
        }, $this->roleHierarchy->getReachableRoles(
            array_map(function ($y) {
                return new Role($y);
            }, $user->getRoles())
        ));

        if (in_array('ROLE_SU', $allRoles)) {
            return 'root';
        } else if (in_array('ROLE_GOLD', $allRoles)) {
            return 'gold';
        } else if (in_array('ROLE_USER', $allRoles)) {
            return 'free';
        } else return 'anon';
    }

    public function getNewTokens($tCost, $curTime, $lastTime, $lastCount, $tps, $tMax)
    {
        return floor(min($tMax, $lastCount + $tps * ($curTime - $lastTime) - $tCost));
    }

    public function testTokens($type, $cost, $user = null)
    {
        return $this->chargeTokens($type, $cost, $user, true);
    }

    public function chargeTokens($type, $cost, $user = null, $onlyTest = false)
    {
        $user = $this->getUserNull($user);

        $rcStrategy = $this->getRcStrategy($user);

        $uLimits = $user->getRcLimits();

        if (array_key_exists($type, $this->rcLims)) {
            if (array_key_exists($rcStrategy, $this->rcLims[$type])) {
                $curTime = time();
                $tps = $this->rcLims[$type][$rcStrategy]['tps'];
                $tMax = $this->rcLims[$type][$rcStrategy]['tMax'];
                if (!array_key_exists($type, $uLimits)) {
                    $uLimits[$type] = ["time" => $curTime, "tokens" => $tMax];
                }
                $lastTime = $uLimits[$type]['time'];
                $lastTokens = $uLimits[$type]['tokens'];

                $newTokens = $this->getNewTokens($cost, $curTime, $lastTime, $lastTokens, $tps, $tMax);
                if ($newTokens < 0) {
                    return false;
                } else {
                    if (!$onlyTest) {
                        $uLimits[$type] = ["time" => $curTime, "tokens" => $newTokens];
                        $user->setRcLimits($uLimits);
                    }
                    return true;
                }
            }
        }
        return false;
    }

    public function refreshTokens($user = null)
    {
        $user = $this->getUserNull($user);

        $rcStrategy = $this->getRcStrategy($user);

        $uLimits = $user->getRcLimits();

        $curTime = new \DateTime('now');
        foreach ($this->rcLims as $key => $val) {
            $uLimits[$key] = ["time" => $curTime, "tokens" => $val[$rcStrategy]["tMax"]];
        }
    }

    public function getHardLimits($type, $user = null)
    {
        $user = $this->getUserNull($user);
        $rcStrategy = $this->getRcStrategy($user);

        if (array_key_exists($type, $this->hardLims)) {
            if (array_key_exists($rcStrategy, $this->hardLims[$type])) {
                return $this->hardLims[$type][$rcStrategy]["lim"];
            }
        }
        return -1; //assuming all hard limit values are non-neg, the caller will assume this is an error
    }


    public function onKernelController(FilterControllerEvent $event)
    {
        if (null === ($token = $this->tokenStorage->getToken())) { //no auth token exists..
            return;
        } else {
            $user = $this->getUserNull(null);

            //a bit of a hack, but we'll let it pass..
            if ($user == "anon.")
                return;

            if (!$this->chargeTokens('HTTPRequests', 1.0, $user)) { //user has used up all his tokens..
                $this->flashBag->add('danger', 'Too many requests from this user! Please <a href=".">try again</a> after some time.');
                $event->setController(function () {
                    return new Response('Error; too many requests from this user! Please <a href=".">try again</a> after some time.');
                });
            }
        }
    }

    //todo: this doesn't really belong here, so move it elsewhere..
    public function onUserLogin()
    {
        if (null === ($token = $this->tokenStorage->getToken())) { //no auth token exists..
            return;
        } else {
            $user = $this->getUserNull(null);
            //check if the user has registered less than a week ago.
            // if so, set the flash message for a tutorial..
            $user->setLastLogin(new \DateTime('now'));
            if ($user->getCreatedOn() > new \DateTime('now -7 days')) {
//                $this->flashBag->add('warning', 'Welcome! Seems that you are new here; would you like to <a href="#" class="tourStartLink">take a quick tour?</a><br />'
//                    . 'This link will automatically stop appearing after 1 week; but you can always access it '
//                    . 'again from the Help section.');
            }
            //todo: check if user login tokens were violated..
        }
    }
}