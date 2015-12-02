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

namespace Magnets\MathgymBundle\Service;

use Magnets\MathgymBundle\Entity\Globalvar;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Doctrine\ORM\EntityManager;

class DBHelperService
{

    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getQB()
    {
        return $this->em->createQueryBuilder();
    }

//    public function getQB($alias) {
//        return $this->em->createQueryBuilder($alias);
//    }

    public function getRep($entityName)
    {
        return $this->em->getRepository('MagnetsMathgymBundle:' . $entityName);
    }

    public function getRef($entityName, $entityId)
    {
        return $this->em->getReference('MagnetsMathgymBundle\\', $entityName, $entityId);
    }

    public function flush()
    {
        $this->em->flush();
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $this->flush();
    }

    public function setVar($key, $value)
    {
        $varRep = $this->getRep('Globalvar');
        if (($curVar = $varRep->findOneBy(['varKey' => $key])) === null) {
            $newVar = $varRep->createNew($key, $value);
        } else {
            $curVar->setVarValue($value);
        }
        $this->flush();
    }

    public function getVar($key)
    {
        $varRep = $this->getRep('Globalvar');
        if (($curVar = $varRep->findOneBy(['varKey' => $key, 'cacheDirty' => 0])) !== null) {
            return $curVar->getVarValue();
        } else {
            return null;
        }
    }

    //the way the whole process should work..
    //importantly, only arrays need to be dna/undna'd..
    //so, at the client side, the interface for reading any array would be:
    //instead of saying = someArray, say: = XE.unDNA(someArray);// so if it's an object instead, will get unDNA'd accordingly
    //the final output should look like..
    //{"dna":[0:"Name",1:"Value",..],
    //nested dnas are fine!
    //which means on the client side, every single object has to be un-dna'd first before using ?
    //so.. just pass us an array of objects:
    //[{"id":"234","name":"asd",...},...]
    //and we'll peek into the keys of each inner object, (only 1 level), build the dna, return the encapsulation object.
    public function encodeRNA($data, $keys)
    {
        $resultOb = ['RNA' => $keys, 'code' => []];
        $invKeymap = [];
        $idx = 0;
        foreach ($keys as $key) {
            $invKeymap[$key] = $idx;
            $idx++;
        }
        foreach ($data as $key => $ob) {
            $newOb = [];
            foreach ($ob as $ikey => $ivalue) {
                $newOb[$invKeymap[$ikey]] = $ivalue;
            }
            $resultOb['code'][$key] = $newOb;
        }
        return $resultOb;
    }
}