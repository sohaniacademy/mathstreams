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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Magnets\MathgymBundle\Entity\Stream;

class StreamController extends Controller
{

    public function manageAction()
    {
        $streamRep = $this->get('db_h')->getRep('Stream');

        return $this->render('MagnetsMathgymBundle:Stream:index.body.html.twig', ['streams' => $streamRep->findAll()]);
    }

    public function viewAction($sid)
    {
        $streamRep = $this->get('db_h')->getRep('Stream');
        $solRep = $this->get('db_h')->getRep('Solution');
        $stream = $streamRep->find($sid); //TODO add ifNull
        $user = $this->getUser();
        $statsWrapper = $solRep->getStats($user, $stream);//TODO: deprecate this asap!
        return $this->render('MagnetsMathgymBundle:Stream:view.body.html.twig', ['stream' => $stream, 'problems' => $statsWrapper['problems'], 'scores' => $statsWrapper['scores'], 'status' => $statsWrapper['status']]);
    }

    public function updateAction(Request $request)
    {
        $streamRep = $this->get('db_h')->getRep('Stream');

        $data = $this->get('input_validator')->cleanupArr($request->request->all());

        if ($data['sid'] < 0) {
            $stream = $streamRep->addStream($data['name']);
        } else {
            $stream = $streamRep->find($data['sid']);
            if(!$stream){
                return new JsonResponse(['status'=>'Error','msg'=>'Stream does not exist!']);
            }
        }
        $stream->setData($data);

        return new JsonResponse(['status' => 'OK']);//,'msg'=>'This is what went wrong!']);// $this->redirectToRoute('managestream');
    }

//    public function newAction(Request $request) {
//        $streamRep = $this->get('db_h')->getRep('Stream');
//
//        $data = $this->get('input_validator')->cleanupArr($request->request->all());
//
//        $stream = $streamRep->addStream($data['name']);
//        $stream->setData($data);
//
//        return new JsonResponse(['status'=>'OK']);//,'msg'=>'This is what went wrong!']);// $this->redirectToRoute('managestream');
//    }
//
//    public function updateAction(Request $request, $sid) {
//        $streamRep = $this->get('db_h')->getRep('Stream');
//        $stream = $streamRep->find($sid); //TODO add ifNull check
//
//        $data = $request->request->all();
//        $stream->setData($this->get('input_validator')->cleanupArr($data));
//
//        return $this->redirectToRoute('managestream');
//    }

    public function deleteAction($sid)
    {
        $streamRep = $this->get('db_h')->getRep('Stream');
        $stream = $streamRep->find($sid); //TODO add ifNull
        $streamRep->remove($stream);

        return $this->redirectToRoute('managestream');
    }

}
