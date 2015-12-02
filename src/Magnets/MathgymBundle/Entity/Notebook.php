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
 * Magnets\MathgymBundle\Entity\Notebook
 *
 * @ORM\Table(name="notebooks")
 * @ORM\Entity(repositoryClass="Magnets\MathgymBundle\Entity\NotebookRepository")
 */
class Notebook
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @ORM\ManyToOne(targetEntity="Lesson", inversedBy="notebooks")
     * @ORM\JoinColumn(name="lesson_id", referencedColumnName="id",  onDelete="CASCADE")
     */
    protected $lesson;

    /**
     * @ORM\Column(type="text")
     */
    private $response;

    /**
     * @ORM\Column(type="text")
     */
    private $answer;

    /**
     * @ORM\Column(type="integer")
     */
    private $score;

    /**
     * @ORM\Column(type="integer")
     */
    private $penalty;

    /**
     *
     * @ORM\Column(type="boolean")
     */
    private $isDraft;

    /**
     *
     * @ORM\Column(type="boolean")
     */
    private $isPrimary;

    /**
     * @ORM\Column(type="text")
     */
    private $images;

    /**
     *
     * @ORM\Column(type="datetime")
     */
    private $created;

    public function __construct($user, $lesson, $response)
    {
        $this->setAuthor($user);
        $this->setLesson($lesson);
        $this->setResponse($response);
        $this->setAnswer("");
        $this->setScore(0);
        $this->setPenalty(0);
        $this->setIsDraft(true);
        $this->setIsPrimary(false);
        $this->setImages("");
        $this->setIsDraft(true);
        $this->setCreated(new \DateTime('now'));
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
     * Set response
     *
     * @param string $response
     *
     * @return Notebook
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get response
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set answer
     *
     * @param string $answer
     *
     * @return Notebook
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer
     *
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Set score
     *
     * @param integer $score
     *
     * @return Notebook
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
     * Set penalty
     *
     * @param integer $penalty
     *
     * @return Notebook
     */
    public function setPenalty($penalty)
    {
        $this->penalty = $penalty;

        return $this;
    }

    /**
     * Get penalty
     *
     * @return integer
     */
    public function getPenalty()
    {
        return $this->penalty;
    }

    /**
     * Set isDraft
     *
     * @param boolean $isDraft
     *
     * @return Notebook
     */
    public function setIsDraft($isDraft)
    {
        $this->isDraft = $isDraft;

        return $this;
    }

    /**
     * Get isDraft
     *
     * @return boolean
     */
    public function getIsDraft()
    {
        return $this->isDraft;
    }

    /**
     * Set isPrimary
     *
     * @param boolean $isPrimary
     *
     * @return Notebook
     */
    public function setIsPrimary($isPrimary)
    {
        $this->isPrimary = $isPrimary;

        return $this;
    }

    /**
     * Get isPrimary
     *
     * @return boolean
     */
    public function getIsPrimary()
    {
        return $this->isPrimary;
    }

    /**
     * Set images
     *
     * @param string $images
     *
     * @return Notebook
     */
    public function setImages($images)
    {
        $this->images = $images;

        return $this;
    }

    /**
     * Get images
     *
     * @return string
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Notebook
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set author
     *
     * @param \Magnets\MathgymBundle\Entity\User $author
     *
     * @return Notebook
     */
    public function setAuthor(\Magnets\MathgymBundle\Entity\User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \Magnets\MathgymBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set lesson
     *
     * @param \Magnets\MathgymBundle\Entity\Lesson $lesson
     *
     * @return Notebook
     */
    public function setLesson(\Magnets\MathgymBundle\Entity\Lesson $lesson = null)
    {
        $this->lesson = $lesson;

        return $this;
    }

    /**
     * Get lesson
     *
     * @return \Magnets\MathgymBundle\Entity\Lesson
     */
    public function getLesson()
    {
        return $this->lesson;
    }
}
