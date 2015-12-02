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

namespace Magnets\MathgymBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * LSMapRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LSMapRepository extends EntityRepository {

    public function persist($lsmap) {
        $this->_em->persist($lsmap);
    }

    public function remove($lsmap) {
        $this->_em->remove($lsmap);
    }

    public function addmap($lesson, $stream) {

        if ($this->isMemberOf($lesson, $stream)) {
            return;
        }
        //TODO: check if we really need to realign pos values for this stream..
        $lsMaps = $this->findBy(array('stream' => $stream), array('pos' => 'ASC'));
        $pos = 1;
        foreach ($lsMaps as $lsMap) {
            $lsMap->setPos($pos);
            $pos++;
        }

        $lsmap = new LSMap($lesson, $stream, $pos);
        $this->persist($lsmap);
    }

    public function delmap($lesson, $stream) {
        $maps = $this->findBy(array('lesson' => $lesson, 'stream' => $stream));
        if ($maps !== null) {
            foreach ($maps as $map) {
                $this->remove($map);
            }
        }
    }

    public function isMemberOf($lesson, $stream) {
        return (($this->findBy(array('lesson' => $lesson, 'stream' => $stream))) != null );
    }

}