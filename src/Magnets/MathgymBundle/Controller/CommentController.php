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

class CommentController extends Controller
{

    public function viewAction($pid, $uid)
    {
        $comRep = $this->get('db_h')->getRep('Comment');
        $probRep = $this->get('db_h')->getRep('Problem');

        //note! if uid < 0, this is a problem comment, not a solution comment..
        //but getHandle takes care of it..
        $comments = $comRep->findBy(array('handle' => $comRep->getHandle($pid, $uid)));
        //todo: need to present them as a linked list.. or a tree in future..
        //      but for now we'll let the client-side JS handle it..
        $comArr = [];
        foreach ($comments as $comment) {
            $comOb = ['author' => $comment->getAuthor()->getFullname(),
                'curId' => $comment->getId(), 'parentId' => $comment->getParent()->getId(),
                'msg' => $comment->getMsg()];
            $comArr[] = $comOb;
        }
        return new JsonResponse(['status' => 'OK', 'comments' => $comArr]);
    }

    public function addAction(Request $request, $pid, $uid)
    {
        $comRep = $this->get('db_h')->getRep('Comment');
        $probRep = $this->get('db_h')->getRep('Problem');
        $userRep = $this->get('db_h')->getRep('User');

        $problem = $probRep->find($pid);
        $user = $this->getUser();

        //add comment to the existing thread, if exists..
        //note! if uid < 0, this is a problem comment, not a solution comment..
        //but getHandle takes care of it..

        $handle = $comRep->getHandle($pid, $uid);
        $comments = $comRep->findBy(array('handle' => $handle), array('created' => 'DESC'));

        $comment = $comRep->addComment($user);

        if (sizeof($comments == 0)) {
            $commHead = $comment; //create a new thread, with the first comment as its own parent
        } else {
            $commHead = $comments[0];
        }

        $data = $request->request->all();

        $comment->setMsg($this->get('input_validator')->cleanup($data['msg']));
        $comment->setHandle($handle);
        $comment->setParent($commHead);
        $comment->setCreated(new \DateTime('now'));

        //return status OK..
        return new JsonResponse(['status' => 'OK', 'payload' => '']);
    }
}
