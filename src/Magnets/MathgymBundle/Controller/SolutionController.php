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

class SolutionController extends Controller
{

    public function viewAction($pid, $uid)
    {
        $probRep = $this->get('db_h')->getRep('Problem');
        $solRep = $this->get('db_h')->getRep('Solution');
        $userRep = $this->get('db_h')->getRep('User');
        $ratingRep = $this->get('db_h')->getRep('Rating');
//first check if this user has solved this problem.. 

        $user = $this->getUser();
        $problem = $probRep->find($pid);

        $draftWrapper = $solRep->getDraft($user, $problem);

        if ($draftWrapper['status'] == 'Submitted') {
//fetch the requested solution..
            $draftWrapper = $solRep->getDraft($userRep->find($uid), $problem);
//if solution not submitted by requested user..
            if ($draftWrapper['status'] == 'Submitted') {
                //get rating stats.. 
                $rating = $ratingRep->getSolStats($draftWrapper['draft']);
                return $this->render('MagnetsMathgymBundle:Solution:view.body.html.twig', ['problem' => $problem, 'draftStatus' => $draftWrapper['status'], 'draft' => $draftWrapper['draft'], 'rating' => $rating]);
            } else {
                $this->addFlash('danger', 'Error; the requested solution does not exist!');
                return $this->redirectToRoute('viewproblem', ['pid' => $pid, 'sid' => '-1']);
            }
        } else {
            $this->addFlash('danger', 'Error; you have not solved this problem yet!');
            return $this->redirectToRoute('viewproblem', ['pid' => $pid, 'sid' => '-1']);
        }
    }

    public function checkAction(Request $request, $pid)
    {
        $probRep = $this->get('db_h')->getRep('Problem');
        $solRep = $this->get('db_h')->getRep('Solution');

        $user = $this->getUser();
        $problem = $probRep->find($pid);

        $draftWrapper = $solRep->getDraft($user, $problem);

        if ($draftWrapper['status'] == 'Submitted') {
//solution already exists.. 
            return new JsonResponse(['status' => 'Error', 'payload' => 'Error; problem already completed!']);
        }
        if ($draftWrapper['status'] == 'NoneExists') {
            //create one..
            $draft = $solRep->addDraft($user, $problem, "");
        } else {
            $draft = $draftWrapper['draft'];
        }

        $data = $request->request->all();
        if ($data['response'] == $problem->getAnswer()) {
            //change score:
            //and make it a non-draft..
            $draft->setIsDraft(false);
            $draft->setScore(10 - $draft->getPenalty());
            $this->get('db_h')->flush();
            return new JsonResponse(['status' => 'OK', 'result' => 'Correct', 'score' => $draft->getScore(), 'penalty' => $draft->getPenalty()]);
        } else {
            //penalize; but don't change the score yet..
            $draft->setPenalty(min($draft->getPenalty() + 1, 8));
            $this->get('db_h')->flush();
            return new JsonResponse(['status' => 'OK', 'result' => 'Wrong', 'score' => $draft->getScore(), 'penalty' => $draft->getPenalty()]);
//            return new JsonResponse(['status' => 'OK', 'payload' => $result]);
        }
    }

    public function showHint($pid)
    {
        //check if user draft has already achieved the barrier..
        //if not, upgrade the penalty level accordingly..
    }

    public function savedraftAction(Request $request, $pid)
    {
        $probRep = $this->get('db_h')->getRep('Problem');
        $solRep = $this->get('db_h')->getRep('Solution');

        $user = $this->getUser();
        $problem = $probRep->find($pid);

        $draftWrapper = $solRep->getDraft($user, $problem);

//will bind POST data to solution
        $data = $request->request->all();

        if ($draftWrapper['status'] == 'Submitted') {
//solution already exists..
            return new JsonResponse(['status' => 'Error', 'payload' => 'Error; solution already submitted!']);
        } else if ($draftWrapper['status'] == 'NoneExists') {
//create a new one..
            $solRep->addDraft($user, $problem, $this->get('input_validator')->cleanup($data['response']));

            return new JsonResponse(['status' => 'OK']);
        } else if ($draftWrapper['status'] == 'Draft') {
//update the existing one..
            $draft = $draftWrapper['draft'];
            $draft->setResponse($this->get('input_validator')->cleanup($data['response']));

            return new JsonResponse(['status' => 'OK']);
        } else {
//something went wrong.. 
            return new JsonResponse(['status' => 'Error', 'payload' => 'Something went wrong..']);
        }
    }

    public function previewdraftAction($pid)
    {
        $probRep = $this->get('db_h')->getRep('Problem');
        $solRep = $this->get('db_h')->getRep('Solution');

        $user = $this->getUser();
        $problem = $probRep->find($pid);

        $draftWrapper = $solRep->getDraft($user, $problem);

        if ($draftWrapper['status'] == 'Draft') {
            return $this->render('MagnetsMathgymBundle:Problem:presubmit.body.html.twig', ['problem' => $problem, 'draft' => $draftWrapper['draft']]);
        } else {//else need to check what happened         
            $this->addFlash('danger', 'Please save your solution first!');
            return $this->redirectToRoute('solveproblem', ['pid' => $pid, 'sid' => '-1']); // renderztrf('MagnetsMathgymBundle:UI:status.html.twig', array('status' => 'Error!'));
        }
    }

    public function submitdraftAction($pid)
    {
        $probRep = $this->get('db_h')->getRep('Problem');
        $solRep = $this->get('db_h')->getRep('Solution');

        $user = $this->getUser();
        $problem = $probRep->find($pid);

        $draftWrapper = $solRep->getDraft($user, $problem);

        if ($draftWrapper['status'] == 'Draft') {
//make the draft final..
            $draft = $draftWrapper['draft'];
            $draft->setIsDraft(false);

            $this->addFlash('success', 'Solution submitted successfully!');

            return $this->redirectToRoute('viewproblem', ['pid' => $pid]);
        } else {
//something went wrong..
            return new JsonResponse(['status' => 'Error!']);
        }
    }

    public function notebookAction()
    {
//TODO: render notebook pages.. basically it is an embedded 'view problem/solution' feature, nothing more..
    }

    public function hintAction($pid, $hlevel)
    {
        $probRep = $this->get('db_h')->getRep('Problem');
        $solRep = $this->get('db_h')->getRep('Solution');

        $user = $this->getUser();
        $problem = $probRep->find($pid);

        $draftWrapper = $solRep->getDraft($user, $problem);

        if ($draftWrapper['status'] == 'Draft') {
            $draft = $draftWrapper['draft'];
        } else if ($draftWrapper['status'] == 'NoneExists') {
            $draft = $solRep->addDraft($user, $problem, $this->get('input_validator')->cleanup(''));
            $solRep = $this->get('db_h')->flush();
        } else {
            return new JsonResponse(['status' => 'Error!', 'msg' => 'Solution already submitted']);
        }

//check if user has already unlocked this hint..
        $hintsThres = ['hint1' => 2, 'hint2' => 5, 'sol' => 8];
        if (array_key_exists($hlevel, $hintsThres) && ($problem->getQtype() == 'sub' || $draft->getPenalty() >= $hintsThres[$hlevel])) {
//show the hint.. 
            $hint1 = null;
            $hint2 = null;
            $solution = null;
            if ($hlevel == 'hint1') {
                $hint1 = $problem->getHint1();
            } else if ($hlevel == 'hint2') {
                $hint2 = $problem->getHint2();
            } else {
                $solution = $problem->getSolution();
            }
//set hint level in solution..
            $draft->setPenalty(max($draft->getPenalty(), $hintsThres[$hlevel]));
            $this->get('db_h')->flush();
            return new JsonResponse(['status' => 'OK', 'hint1' => $hint1, 'hint2' => $hint2, 'sol' => $solution, 'score' => $draft->getScore(), 'penalty' => $draft->getPenalty()]);
        } else {
            return new JsonResponse(['status' => 'Error!', 'payload' => '']);
        }
    }

    public function rateAction(Request $request, $sid)
    {
        //check if rating exists..
        $ratingRep = $this->get('db_h')->getRep('Rating');
        $solRep = $this->get('db_h')->getRep('Solution');
        $data = $request->request->all();

        $score = $this->get('input_validator')->cleanup($data['score']);
        $ratingRep->addSolutionRating($this->getUser(), $sid, $score);

        return new JsonResponse(['status' => 'OK', 'payload' => '']);
    }

}
