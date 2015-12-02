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
use Symfony\Component\HttpFoundation\RequestStack;

class ProblemController extends Controller
{
    public function indexAction()
    {
//        $streamCats = $this->get('db_h')->getRep('Stream')->getStreamsByCat();
//        $fullMap = $this->get('filt_helper')->getFullPsMap($this->getUser());
//        $userStats = $this->get('filt_helper')->getPstats($this->getUser());
        return $this->render('MagnetsMathgymBundle:Problem:index.body.html.twig');
        //['fullMap' => $fullMap, 'userStats' => $userStats, 'streamCats' => $streamCats]);
    }

    public function getDetailsAction(Request $request)
    {
        $data = $request->request->all();
        $cids = $data['cids'];

        $problems = $this->get('db_h')->getQB()->select('p.id,p.label')->from('MagnetsMathgymBundle:Problem','p','p.id')->where('p.id in (:cids)')->setParameter('cids',$cids)->getQuery()->getResult();
        //getRep('Problem')->findBy(['id' => $cids]);
        return new JsonResponse(['status' => 'OK', 'data' => $problems]);
    }
//    public function getmapAction()
//    {
////        $streamCats = $this->get('db_h')->getRep('Stream')->getStreamsByCat();
//        $fullMap = $this->get('filt_helper')->getFullPsMap($this->getUser());
//        $userStats = $this->get('filt_helper')->getPstats($this->getUser());
//        $userProbs = array_map(function ($x) {
//            return $x->getId();
//        }, $this->get('db_h')->getRep('Problem')->findBy(['author' => $this->getUser()]));
//        $savedFilters = $this->getUser()->getPfilters();
//        return new JsonResponse(['status' => 'OK', 'fullMap' => $fullMap,
//            'userContent' => $userProbs, 'userStats' => $userStats, 'savedFilters' => $savedFilters]);
//        // 'streamCats' => $streamCats]);
//    }

    public function homeAction(Request $request)
    {
        return $this->render('MagnetsMathgymBundle:Problem:home.frag.html.twig');
    }

    public
    function newAction()
    {
        //check problems limit first..
//        $probRep = $this->get('db_h')->getRep('Problem');
//        $problem = $probRep->createProblem($this->getUser());
        return $this->editAction(-1);
    }

    public
    function editAction($pid)
    {
        $probRep = $this->get('db_h')->getRep('Problem');
        $streamRep = $this->get('db_h')->getRep('Stream');

        $problem = null;
        $streamCats = $streamRep->getStreamsByCat();
        $mems = [];
        if ($pid >= 0) {
            $problem = $probRep->find($pid);
            if (!$problem) {
                $this->addFlash('danger', 'Error; problem does not exist!');
                return $this->redirectToRoute('user');
            }

            //check if user is indeed the author of the problem..
            if (!$this->get('stats_helper')->canPEdit($problem, $this->getUser())) {
                $this->addFlash('danger', 'Error; only the problem\'s original author can edit it!');
                return $this->redirectToRoute('user');
            }

            $mems = $probRep->getStreams($problem);
        } else {
            $problem = $probRep->createProb('New problem',$this->getUser());
        }

        return $this->render('MagnetsMathgymBundle:Problem:edit.body.html.twig', ['problem' => $problem, 'streamCats' => $streamCats, 'mems' => $mems]);
    }

    public
    function saveAction(Request $request, $pid)
    {
        $probRep = $this->get('db_h')->getRep('Problem');
        $streamRep = $this->get('db_h')->getRep('Stream');

//will bind POST data to problem.. 
        $data = $request->request->all();

//check if user is indeed the author of the problem..
        $problem = $probRep->find($pid);
//todo if doesn't exist..
        if (!$this->get('stats_helper')->canPEdit($problem, $this->getUser())) {
            $this->addFlash('danger', 'Error; only the problem\'s original author can edit it!');
            return $this->redirectToRoute('user');
        }

//check if a problem with given saveId exists:
        if ($pid >= 0) {
            $curProb = $probRep->find($pid);
            $cleanData = $this->get('input_validator')->cleanupArr($data);

//convert std, diff fields to stream objects..
//TODO: 'safety' check if they are in a predefined enum
//$cleanData['std'] = $streamRep->findByName($cleanData['std']);
//$cleanData['diff'] = $streamRep->findByName($cleanData['diff']);
            $curProb->setData($cleanData);

//$cleanData = $this->get('stats_helper')->setFilters($cleanData['fid'], $curProb);

            return new JsonResponse(['status' => 'OK', 'payload' => $curProb->getId()]);
        } else {
//something went wrong..
            return new JsonResponse(['status' => 'Error']);
        }
    }

    public
    function setfiltersAction(Request $request, $pid)
    {
        $probRep = $this->get('db_h')->getRep('Problem');
        $problem = $probRep->find($pid);


//check if user is indeed the author of the problem..
        if (!$this->get('stats_helper')->canPEdit($problem, $this->getUser())) {
            return new JsonResponse(['status' => 'Error', 'payload' => 'Permission denied']);
        }

        $data = $request->request->all();
        $this->get('stats_helper')->setFilters($data['fid'], $problem);
        return new JsonResponse(['status' => 'OK', 'payload' => '']);
    }

    public function deleteAction($pid)
    {
        $probRep = $this->get('db_h')->getRep('Problem');

        $problem = $probRep->find($pid);

        //check if user is indeed the author of the problem..
        if (!$this->get('stats_helper')->canPEdit($problem, $this->getUser())) {
            $this->addFlash('danger', 'Error; only the problem\'s original author can delete it!');
            return $this->redirectToRoute('user');
        }

        $probRep->remove($problem);

        return $this->redirectToRoute('user');
    }

    public function getFilteredAction($fid, $short = false)
    {
        if (!$this->get('rate_control')->chargeTokens('FilterRequests', 1)) {
            $this->addFlash('warning', 'Too many filter requests! Please try again after a few minutes');
            return new JsonResponse(['status' => 'Error']);
        }

        $maxResults = $this->get('rate_control')->getHardLimits('FilterResults', $this->getUser());
        $problems = $this->get('filt_helper')->getPfiltered($fid, $this->getUser(), $maxResults);

        $pStats = $this->get('stats_helper')->getViewPStats($problems, $this->getUser());

        return new JsonResponse(['status' => 'OK', 'contents' => $pStats]);
    }

    public function filterAction(Request $request)
    {
        return $this->getFilteredAction($request->request->get('fid'), $short = false);
    }

    public function peekAction($pid)
    {
        $probRep = $this->get('db_h')->getRep('Problem');

        $problem = $probRep->find($pid);
        if (!$problem) {
            //$this->addFlash('danger', 'Lesson does not exist!');
            //return $this->redirectToRoute('lessons');
            return new JsonResponse(['status' => 'Error', 'content' => 'Problem does not exist']);
        }
        //$jsOb = ['status'=>'OK','content'=>$this->render('MagnetsMathgymBundle:Lesson:peek.json.twig',['title' => $lesson->getName(), 'content' => json_decode($lesson->getContent())])];
        //print_r($jsOb);
        //return new JsonResponse([]);
        $canView = $this->get('filt_helper')->canPView($problem, $this->getUser());
        $canEdit = $this->get('filt_helper')->canPEdit($problem, $this->getUser());
        $hasTokens = $this->get('rate_control')->testTokens('ProblemViews', 9);
//        $peekView = $this->renderView('MagnetsMathgymBundle:Problem:peek.json.twig',
//            ['title' => $problem->getName(), 'content' => json_decode($problem->getQuestion()),
//                'canView' => $canView, 'canEdit' => $canEdit,
//            ]);
//        return new JsonResponse(['status' => 'OK', 'content' => $peekView]);
        return $this->render('MagnetsMathgymBundle:Problem:peek.modal.twig',
            ['id' => $problem->getId(), 'title' => $problem->getLabel(), 'content' => json_decode($problem->getQuestion(), true),
                'canView' => $canView, 'canEdit' => $canEdit,
                'hasTokens' => $hasTokens,
            ]);
    }

//    public function viewAction($pid)
//    {
//        $solRep = $this->get('db_h')->getRep('Solution');
//
//        $probRep = $this->get('db_h')->getRep('Problem');
//        $ratingRep = $this->get('db_h')->getRep('Rating');
//
//        $problem = $probRep->find($pid);
//        if (!$problem) {
//            $this->addFlash('danger', 'Problem does not exist!');
//            return $this->redirectToRoute('problems');
//        } else
//            if (!$this->get('filt_helper')->canPView($problem, $this->getUser())) {
//                $this->addFlash('danger', 'You do not have the permissions to view this lesson!');
//                return $this->redirectToRoute('problems');
//            } else if (!$this->get('rate_control')->chargeTokens('ProblemViews', 9)) {
//                $this->addFlash('warning', 'You have reached the problems limit for the day! Please try again tomorrow');
//                return $this->redirectToRoute('problems');
//            }
//
//        $draftWrapper = $solRep->getDraft($this->getUser(), $problem);
//
//        $sols = [];
//        if ($draftWrapper['status'] == 'Submitted' && $problem->getQtype() == 'sub') {
//            $sols = $solRep->findBy(array('problem' => $problem, 'isDraft' => false));
//        }
//        $rating = $ratingRep->getProbStats($problem);
//
//        return $this->render('MagnetsMathgymBundle:Problem:view.body.html.twig', ['problem' => $problem, 'content' => json_decode($problem->getQuestion()), 'draftStatus' => $draftWrapper['status'], 'draft' => $draftWrapper['draft'], 'sols' => $sols, 'rating' => $rating]);
//    }

    public function solveAction($pid)
    {
        $probRep = $this->get('db_h')->getRep('Problem');
        $solRep = $this->get('db_h')->getRep('Solution');

        $user = $this->getUser();
        $problem = $probRep->find($pid);

        if (!$this->get('stats_helper')->canPView($problem, $this->getUser())) {
            $this->addFlash('danger', 'Error; permission denied!');
            return $this->redirectToRoute('user');
        }

        $draftWrapper = $solRep->getDraft($user, $problem);

        if ($draftWrapper['status'] == 'Submitted') {
            $this->addFlash(
                'warning', 'You have already completed answering this problem!'
            );
            return $this->redirectToRoute('viewproblem', ['pid' => $problem->getId()]);
        } else {
            return $this->render('MagnetsMathgymBundle:Problem:solve.body.html.twig', ['problem' => $problem, 'draftStatus' => $draftWrapper['status'], 'draft' => $draftWrapper['draft']]);
        }
    }

    public function rateAction(Request $request, $pid)
    {
        //check if rating exists..
        $ratingRep = $this->get('db_h')->getRep('Rating');
        $probRep = $this->get('db_h')->getRep('Problem');
        $data = $request->request->all();

        $score = $this->get('input_validator')->cleanup($data['score']);
        $ratingRep->addProblemRating($this->getUser(), $pid, $score);

        return new JsonResponse(['status' => 'OK', 'payload' => '']);
    }

}