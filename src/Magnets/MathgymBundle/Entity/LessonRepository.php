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
 * LessonRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LessonRepository extends EntityRepository
{

    public function persist($lesson)
    {
        $this->_em->persist($lesson);
    }

    public function remove($lesson)
    {
        $this->_em->remove($lesson);
    }

    public function addLesson($author)
    {
        $lesson = new Lesson($author);
        $this->persist($lesson);
        $this->_em->flush();
        return $lesson;
    }

    public function createLesson($author)
    {
        $lesson = new Lesson($author);
        return $lesson;
    }

}