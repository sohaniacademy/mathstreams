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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class IndexController extends Controller {

    public function indexAction() {
        return $this->render('MagnetsMathgymBundle:Index:index.body.html.twig');
    }

    public function featuresAction() {
        return $this->render('MagnetsMathgymBundle:Index:features.body.html.twig');
    }

    public function aboutAction() {
        return $this->render('MagnetsMathgymBundle:Index:about.body.html.twig');
    }

    public function contactAction() {
        return $this->render('MagnetsMathgymBundle:Index:contact.body.html.twig');
    }

    public function creditsAction() {
        return $this->render('MagnetsMathgymBundle:Index:credits.body.html.twig');
    }

    public function texhelpAction() {
        return $this->render('MagnetsMathgymBundle:Index:tex_help.body.html.twig');
    }

    public function flashAction() {
        $this->addFlash('success','Hello flash!');
        $this->addFlash('info','Hello flash!');
        $this->addFlash('warning','Hello flash!');
        $this->addFlash('danger','Hello flash!');
        return $this->render('MagnetsMathgymBundle:Index:about.body.html.twig');
    }

    public function removeTrailingSlashAction(Request $request) {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        return $this->redirect($url, 301);
    }

    public function csrfErrorAction() {
        return new JsonResponse(['status' => 'Error', 'msg' => 'CSRF token invalid!']);
    }
}
