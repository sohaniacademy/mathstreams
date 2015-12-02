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
use Magnets\MathgymBundle\Entity\Lesson;

class LessonController extends Controller
{
    public function indexAction()
    {
        $streamCats = $this->get('db_h')->getRep('Stream')->getStreamsByCat();
        return $this->render('MagnetsMathgymBundle:Lesson:index.body.html.twig', ['streamCats' => $streamCats]);
    }

    public function getDetailsAction(Request $request)
    {
        //todo: set limits..
        $data = $request->request->all();
        $cids = $data['cids'];

        $lessons = $this->get('db_h')->getQB()->select('l.id,l.label')->from('MagnetsMathgymBundle:Lesson','l','l.id')->where('l.id in (:cids)')->setParameter('cids',$cids)->getQuery()->getResult();
        return new JsonResponse(['status' => 'OK', 'data' => $lessons]);
    }

    public function homeAction(Request $request)
    {
        return $this->render('MagnetsMathgymBundle:Lesson:home.frag.html.twig');
    }

    public function viewAction($lid)
    {
        $lessonRep = $this->get('db_h')->getRep('Lesson');

        $lesson = $lessonRep->find($lid);
        if (!$lesson) {
            $this->addFlash('danger', 'Lesson does not exist!');
            return $this->redirectToRoute('lessons');
        } else if (!$this->get('filt_helper')->canLView($lesson, $this->getUser())) {
            $this->addFlash('danger', 'You do not have the permissions to view this lesson!');
            return $this->redirectToRoute('lessons');
        } else if (!$this->get('rate_control')->chargeTokens('LessonViews', 9)) {
            $this->addFlash('warning', 'You have reached the lessons limit for the day! Please try again tomorrow');
            return $this->redirectToRoute('lessons');
        }
        $canEdit = $this->get('filt_helper')->canLEdit($lesson, $this->getUser());
        return $this->render('MagnetsMathgymBundle:Lesson:view.body.html.twig', ['lesson' => $lesson, 'content' => json_decode($lesson->getContent()), 'canEdit' => $canEdit]); //, 'problems' =>
        // $statsWrapper['problems'], 'scores' => $statsWrapper['scores'], 'status' => $statsWrapper['status']]);
    }

    public function peekAction($lid)
    {
        $lessonRep = $this->get('db_h')->getRep('Lesson');

        $lesson = $lessonRep->find($lid);
        if (!$lesson) {
            //$this->addFlash('danger', 'Lesson does not exist!');
            //return $this->redirectToRoute('lessons');
            return new JsonResponse(['status' => 'Error', 'content' => 'Lesson does not exist']);
        }
        //$jsOb = ['status'=>'OK','content'=>$this->render('MagnetsMathgymBundle:Lesson:peek.json.twig',['title' => $lesson->getName(), 'content' => json_decode($lesson->getContent())])];
        //print_r($jsOb);
        //return new JsonResponse([]);
        $canView = $this->get('filt_helper')->canLView($lesson, $this->getUser());
        $canEdit = $this->get('filt_helper')->canLEdit($lesson, $this->getUser());
        $peekView = $this->renderView('MagnetsMathgymBundle:Lesson:peek.json.twig',
            ['title' => $lesson->getName(), 'content' => json_decode($lesson->getContent()),
                'canView' => $canView, 'canEdit' => $canEdit,
            ]);
        return new JsonResponse(['status' => 'OK', 'content' => $peekView]);
    }

    public function newAction()
    {
        //check lessons limit first..

        $lessonRep = $this->get('db_h')->getRep('Lesson');
        $lesson = $lessonRep->createLesson($this->getUser());
        return $this->editAction(-1);
    }

//    public function renameAction(Request $request, $lid)
//    {
//        $lessonRep = $this->get('db_h')->getRep('Lesson');
//        //form POST data..
//        $data = $request->request->all();
//        $lesson = $lessonRep->find($lid); //TODO add ifNull
//        $lesson->setName($this->get('input_validator')->cleanup($data['name']));
//        return new JsonResponse(['status' => 'OK']);
//    }

    public function deleteAction($lid)
    {
        $lessonRep = $this->get('db_h')->getRep('Lesson');
        $lesson = $lessonRep->find($lid); //TODO add ifNull
        $lessonRep->remove($lesson);

        return $this->redirectToRoute('lessons');
    }

    public function editAction($lid)
    {
        $lessonRep = $this->get('db_h')->getRep('Lesson');

        $lesson = null;
        if ($lid >= 0) {
            $lesson = $lessonRep->find($lid);
            if (!$lesson) {
                $this->addFlash('danger', 'Error; lesson does not exist!');
                return $this->redirectToRoute('lessons');
            }
        } else {
            $lesson = $lessonRep->createLesson($this->getUser());
        }
        return $this->render('MagnetsMathgymBundle:Lesson:edit.body.html.twig', ['lesson' => $lesson]);
//        return $this->render('MagnetsMathgymBundle:UI:texInputAdvanced.frag.html.twig', ['lesson' => $lesson]);
    }

    public function saveAction(Request $request, $lid)
    {
        $lessRep = $this->get('db_h')->getRep('Lesson');

        $cleanName = $this->get('input_validator')->cleanup($request->request->get('lessonName'));
        $cleanData = $this->get('input_validator')->cleanupJSON($request->request->get('lessonData'));

        if ($lid < 0) {
            $lesson = $lessRep->addLesson($this->getUser());
        } else {
            $lesson = $lessRep->find($lid); //todo if doesn't exist..
        }
        $lesson->setData(['name' => $cleanName, 'content' => $cleanData]);
        return new JsonResponse(['status' => 'OK', 'lid' => $lesson->getId()]);
    }

    public function getFilteredAction($fid, $short = false)
    {
        if (!$this->get('rate_control')->chargeTokens('FilterRequests', 1)) {
            $this->addFlash('warning', 'Too many filter requests! Please try again after a few minutes');
            return new JsonResponse(['status' => 'Error']);
            //return $this->redirectToRoute('lessons');
        }

        $maxResults = $this->get('rate_control')->getHardLimits('FilterResults', $this->getUser());
        $lessons = $this->get('filt_helper')->getLfiltered($fid, $this->getUser(), $maxResults);

        $lStats = $this->get('stats_helper')->getViewLStats($lessons, $this->getUser());

        return new JsonResponse(['status' => 'OK', 'contents' => $lStats]);

        //$listView = $this->get('stats_helper')->getViewLStats($lessons, $this->getUser());
        //we will always return json..
//        return new JsonResponse(['status' => 'OK', 'lessons' => $listView]);
//        return $this->render('MagnetsMathgymBundle:Lesson:list.plug.html.twig', [
//            'lessons' => $statsWrapper['lessons'],
//            'scores' => $statsWrapper['scores'],
//            'status' => $statsWrapper['status'],
//            'root_author' => $userRep->find(1), //TODO: this is a hack!
//            'short' => $short,
//        ]);
    }

    public function filterAction(Request $request)
    {
        return $this->getFilteredAction($request->request->get('fid'), $short = false);
    }

    public function filterallAction(Request $request)
    {
        return $this->getFilteredAction('[{"sid":["all"],"freq":1}]', $short = false);
    }

}
