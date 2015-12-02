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

use Doctrine\ORM\Mapping as ORM;

/**
 * Magnets\MathgymBundle\Entity\PSMap
 *
 * @ORM\Table(name="psmaps")
 * @ORM\Entity(repositoryClass="Magnets\MathgymBundle\Entity\PSMapRepository")
 */
class PSMap {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Problem", inversedBy="psmaps")
     * @ORM\JoinColumn(name="problem_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $problem;

    /**
     * @ORM\ManyToOne(targetEntity="Stream", inversedBy="psmaps")
     * @ORM\JoinColumn(name="stream_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $stream;

    /**
     * @ORM\Column(type="integer")
     */
    private $pos;

    public function __construct($problem = null, $stream = null, $pos = null) {
        //init default values:
        if ($problem !== null) {
            $this->setProblem($problem);
            $this->setStream($stream);
            $this->setPos($pos);
        }
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set problem
     *
     * @param \Magnets\MathgymBundle\Entity\Problem $problem
     * @return PSMap
     */
    public function setProblem(\Magnets\MathgymBundle\Entity\Problem $problem = null) {
        $this->problem = $problem;

        return $this;
    }

    /**
     * Get problem
     *
     * @return \Magnets\MathgymBundle\Entity\Problem 
     */
    public function getProblem() {
        return $this->problem;
    }

    /**
     * Set stream
     *
     * @param \Magnets\MathgymBundle\Entity\Stream $stream
     * @return PSMap
     */
    public function setStream(\Magnets\MathgymBundle\Entity\Stream $stream = null) {
        $this->stream = $stream;

        return $this;
    }

    /**
     * Get stream
     *
     * @return \Magnets\MathgymBundle\Entity\Stream 
     */
    public function getStream() {
        return $this->stream;
    }

    /**
     * Set pos
     *
     * @param integer $pos
     * @return PSMap
     */
    public function setPos($pos) {
        $this->pos = $pos;

        return $this;
    }

    /**
     * Get pos
     *
     * @return integer 
     */
    public function getPos() {
        return $this->pos;
    }

}
