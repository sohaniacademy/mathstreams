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

use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ChatroomController extends Controller
{

    public function statsStr()
    {
        $roomRep = $this->get('db_h')->getRep('Chat\\Chatroom');
        $tickRep = $this->get('db_h')->getRep('Chat\\Ticket');

//        $ticks = $this->getUser()->getTickets();
//        foreach($ticks as $tick){
//            //check if expired..
//
//        }
        //list of current rooms..
        $allRooms = $roomRep->findAll();
        $userRooms = $tickRep->getUserRooms($this->getUser()); //findBy(array('user' => $this->getUser()));
        $rStat = [];
        foreach ($allRooms as $room) {
            $rStat [] = ['rid' => $room->getId(), 'joined' => in_array($room->getId(), $userRooms), 'rname' => $room->getName()];
        }

        return new JsonResponse(['status' => 'OK', 'rStat' => $rStat]);
    }

    public function listAction()
    {
        return $this->statsStr();
    }

    public function updateAction(Request $request)
    {
        $roomRep = $this->get('db_h')->getRep('Chat\Chatroom');

        $data = $this->get('input_validator')->cleanupArr($request->request->all());

        if ($data['rid'] < 0) {
            $room = $roomRep->addRoom($data['name']);
        } else {
            $room = $roomRep->find($data['rid']);
            if(!$room){
                return new JsonResponse(['status'=>'Error','msg'=>'Room does not exist!']);
            }
        }
        $room->setData($data);

        return new JsonResponse(['status' => 'OK']);//,'msg'=>'This is what went wrong!']);// $this->redirectToRoute('managestream');
    }


    public function createAction(Request $request)
    {
        $roomRep = $this->get('db_h')->getRep('Chat\\Chatroom');

        $data = $request->request->all();
        $rname = $data['rname'];

        if ($roomRep->findBy(array('name' => $rname)) != null) {
            return new JsonResponse(['status' => 'Error', 'msg' => 'Error; room already exists!']);
        } else {
            $roomRep->addRoom($rname);
            return $this->statsStr();
        }
    }

    public function deleteAction($rid)
    {
        $roomRep = $this->get('db_h')->getRep('Chat\\Chatroom');

        $room = $roomRep->find($rid);
        if ($room == null) {
            return new JsonResponse(['status' => 'Error', 'msg' => 'Error; room ' . $rid . ' does not exist!']);
        }
        $roomRep->delRoom($room);
        return $this->statsStr();
    }

    /**
     * @param $rid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enterAction($rid)
    {
        $roomRep = $this->get('db_h')->getRep('Chat\\Chatroom');
        $tickRep = $this->get('db_h')->getRep('Chat\\Ticket');

        //check if room exists..
        $room = $roomRep->find($rid);
        if ($room == null) {
            //error..
            return new JsonResponse(['status' => 'Error', 'msg' => 'Error; room ' . $rid . ' does not exist!']);
        }

        //check if user already in the room..
        $user = $this->getUser();
        if (!$tickRep->isInsideRoom($room, $user)) {
            $tickRep->enterRoom($room, $user);
        }

        return $this->usersAction($rid);// $this->statsStr();
    }

    /**
     * @param $rid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function leaveAction($rid)
    {
        $roomRep = $this->get('db_h')->getRep('Chat\\Chatroom');
        $tickRep = $this->get('db_h')->getRep('Chat\\Ticket');

        //check if room exists..
        $room = $roomRep->find($rid);
        if ($room == null) {
            //error..
            return new JsonResponse(['status' => 'Error', 'msg' => 'Error; room ' . $rid . ' does not exist!']);
        }

        //check if user already left the room..
        $user = $this->getUser();
        if (!$tickRep->isInsideRoom($room, $user)) {
            //nothing to do
            return $this->statsStr();
        }

        $tickRep->leaveRoom($room, $user);

        return $this->statsStr();
    }

    /**
     * @param $rid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function usersAction($rid)
    {
        $roomRep = $this->get('db_h')->getRep('Chat\\Chatroom');
        $tickRep = $this->get('db_h')->getRep('Chat\\Ticket');

        //check if user is in this room..
        $room = $roomRep->find($rid);

        $users = $tickRep->getRoomUsers($room);
        $usersView = array_map(function ($x) {
            return ['id' => $x->getId(), 'name' => $x->getFullname()];
        }, $users);
        return new JsonResponse(['status' => 'OK', 'ulist' => ['rid' => $rid, 'users' => $usersView]]);
    }
}