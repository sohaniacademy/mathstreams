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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ChatController extends Controller {

    public function indexAction() {
        //list of all current rooms.. not needed anymore, since JS will fetch them later
        //but, we do need the id of our 'primary' room
        return $this->render('MagnetsMathgymBundle:Chat:index.body.html.twig', ['gen_rid' => '1']); //, array('allrooms' => $roomRep->findAll()));
        //todo: general room id is a hack
    }
    
    public function sidebarAction() {
        return $this->render('MagnetsMathgymBundle:Chat:chat_sidebar.plug.html.twig', ['gen_rid' => '1']); //, array('allrooms' => $roomRep->findAll()));
        //todo: general room id is a hack
    }
    

    public function sendmsgAction($rid, Request $request) {
        $roomRep = $this->get('db_h')->getRep('Chat\\Chatroom');
        $tickRep = $this->get('db_h')->getRep('Chat\\Ticket');

        //check if user is in this room..
        $room = $roomRep->find($rid);
        $user = $this->getUser();

        if (!$tickRep->isInsideRoom($room, $user)) {
            return new JsonResponse(['status' => 'Error', 'msg' => 'Error; user not in room']);
        }

        //broadcast to all users in this room.. 
        $users = $tickRep->getRoomUsers($room);

        //get sig from msg
        $data = $request->request->all();
        $msg = $this->get('input_validator')->cleanup($data['msg']);
        $sig = $this->get('user_signal')->msg2Sig($user, $msg, $room, date('H:i:s'));

        $this->get('user_signal')->broadcast($users, $sig, 'CHAT_NOTIF', new \DateTime('now +10 minute'));
        return $this->pingAction();
    }

    public function pingAction() {
        $user = $this->getUser();
        $msglist = $this->get('user_signal')->getSignals($user, 'CHAT_NOTIF');

        return new JsonResponse(['status' => 'OK', 'msgList' => $msglist]);
    }
}
