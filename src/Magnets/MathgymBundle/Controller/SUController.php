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

namespace Magnets\MathgymBundle\Controller;

use Magnets\MathgymBundle\Entity\UserLimit;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SUController extends Controller
{

    public function indexAction()
    {
        return $this->render('MagnetsMathgymBundle:SU:index.body.html.twig');
    }

    public function usersAction()
    {
        return $this->render('MagnetsMathgymBundle:SU:users.body.html.twig', ['users' => $this->get('db_h')->getRep('User')->findAll()]);
    }

    public function streamsAction()
    {
        return $this->render('MagnetsMathgymBundle:SU:streams.body.html.twig', ['streams' => $this->get('db_h')->getRep('Stream')->findAll()]);
    }

    public function roomsAction()
    {
        return $this->render('MagnetsMathgymBundle:SU:chatrooms.body.html.twig', ['rooms' => $this->get('db_h')->getRep('Chat\Chatroom')->findAll()]);
    }

    public function fixAuthorsAction()
    {
        //fix author fields for all problems.. 
        $probRep = $this->get('db_h')->getRep('Problem');
        $userRep = $this->get('db_h')->getRep('User');

        $suAuthor = $userRep->find(1);//todo: this is a hack!

        foreach ($probRep->findAll() as $prob) {
            if ($prob->getAuthor() == null) {
                $prob->setAuthor($suAuthor);
            }
        }
        return new JsonResponse(['status' => 'OK', 'msg' => 'fixed!']);
    }

//    public function generateUserLimsAction()
//    {
//        $userRep = $this->get('db_h')->getRep('User');
//        $limsRep = $this->get('db_h')->getRep('UserLimit');
//
//        foreach ($userRep->findAll() as $user) {
//            if ($user->getLimits() == null) {
//                //create and assign a limits object
//                $lims = new UserLimit($user);
//                $user->setLimits($lims);
//                $limsRep->persist($lims);
//            } else {
//                //reinitialize values..
//                $this->get('rate_control')->refreshTokens($user);
//            }
//        }
//        return new JsonResponse(['status' => 'OK', 'msg' => 'fixed!']);
//    }


    public function clearTicksAction()
    {
        $tickRep = $this->get('db_h')->getRep('Chat\\Ticket');
        foreach ($tickRep->findAll() as $tick) {
            $tickRep->remove($tick);
        }
        $tickRep->flush();

        return new JsonResponse(['status' => 'OK', 'msg' => 'fixed!']);
    }

}