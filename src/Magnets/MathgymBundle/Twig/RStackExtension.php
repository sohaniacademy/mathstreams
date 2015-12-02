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

namespace Magnets\MathgymBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;

class RStackExtension extends \Twig_Extension
{
    protected $rStack;

    public function setRStack(RequestStack $requestStack)
    {
        $this->rStack = $requestStack;
    }

    public function getFunctions()
    {
        return array(

            new \Twig_SimpleFunction('isMasterRequest', function () {
                //echo ($this->rStack->getParentRequest() === null)?'Mm':'Ss';
                //return true;
                return ($this->rStack->getParentRequest() === null);
            }),
        );
    }

    public function getName()
    {
        return 'rstack_extension';
    }
}