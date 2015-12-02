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
 * Magnets\MathgymBundle\Entity\Rating
 *
 * @ORM\Table(name="ratings")
 * @ORM\Entity(repositoryClass="Magnets\MathgymBundle\Entity\RatingRepository")
 */
class Rating {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Solution")
     * @ORM\JoinColumn(name="sol_id", referencedColumnName="id")
     */
    protected $solution;

    /**
     * @ORM\Column(type="text")
     */
    private $handle;

    /**
     * @ORM\Column(type="integer")
     */
    private $score;

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set handle
     *
     * @param string $handle
     * @return Rating
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;
    
        return $this;
    }

    /**
     * Get handle
     *
     * @return string 
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Set score
     *
     * @param integer $score
     * @return Rating
     */
    public function setScore($score)
    {
        $this->score = $score;
    
        return $this;
    }

    /**
     * Get score
     *
     * @return integer 
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set user
     *
     * @param \Magnets\MathgymBundle\Entity\User $user
     * @return Rating
     */
    public function setUser(\Magnets\MathgymBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Magnets\MathgymBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set solution
     *
     * @param \Magnets\MathgymBundle\Entity\Solution $solution
     * @return Rating
     */
    public function setSolution(\Magnets\MathgymBundle\Entity\Solution $solution = null)
    {
        $this->solution = $solution;
    
        return $this;
    }

    /**
     * Get solution
     *
     * @return \Magnets\MathgymBundle\Entity\Solution 
     */
    public function getSolution()
    {
        return $this->solution;
    }
}
