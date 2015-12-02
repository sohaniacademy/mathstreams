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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
class UserController extends Controller
{

    public function indexAction()
    {
        //$streamRep = $this->get('db_h')->getRep('Stream');

        //$streams = $streamRep->findAll();

        //$filtStrings = $this->get('stats_helper')->getUserFilters($this->getUser());
        return $this->render('MagnetsMathgymBundle:User:index.body.html.twig');// , ['filters' => $filtStrings, 'streams' => $streams]);
    }

    public function settingsAction()
    {
        //TODO: need to implement, everything basically.
        return $this->redirectToRoute('user');
    }

    public function mapsAction()
    {
        $fullMap = $this->get('filt_helper')->getFullMap($this->getUser());

        $userStats = ['p' => $this->get('filt_helper')->getPstats($this->getUser()),
            'l' => $this->get('filt_helper')->getLstats($this->getUser())];

        $userProbs = array_map(function ($x) {
            return $x->getId();
        }, $this->get('db_h')->getRep('Problem')->findBy(['author' => $this->getUser()]));
        $userLesss = array_map(function ($x) {
            return $x->getId();
        }, $this->get('db_h')->getRep('Lesson')->findBy(['author' => $this->getUser()]));
        $userContent = ['p' => $userProbs, 'l' => $userLesss];

        $savedFilters = ['p' => $this->getUser()->getPfilters(), 'l' => $this->getUser()->getLfilters()];

        return new JsonResponse(['status' => 'OK', 'fullMap' => $fullMap,
            'userContent' => $userContent, 'userStats' => $userStats, 'savedFilters' => $savedFilters]);
        // 'streamCats' => $streamCats]);
    }
}
