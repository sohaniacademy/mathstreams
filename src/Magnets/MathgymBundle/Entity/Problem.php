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
 * Magnets\MathgymBundle\Entity\Problem
 *
 * @ORM\Table(name="problems")
 * @ORM\Entity(repositoryClass="Magnets\MathgymBundle\Entity\ProblemRepository")
 */
class Problem
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id = -1;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="problems")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="text")
     */
    private $question;

    /**
     * @ORM\Column(type="text")
     */
    private $hint1;

    /**
     * @ORM\Column(type="text")
     */
    private $hint2;

    /**
     * @ORM\Column(type="text")
     */
    private $solution;

    /**
     * @ORM\Column(type="text")
     */
    private $answer;

    /**
     * @ORM\Column(type="text")
     */
    private $options;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $flags;

    /**
     * @ORM\Column(type="integer")
     */
    private $std;

    /**
     * @ORM\Column(type="integer")
     */
    private $topic;

    /**
     * @ORM\Column(type="integer")
     */
    private $diff;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $qtype;

    /**
     * @ORM\OneToMany(targetEntity="PSMap", mappedBy="problem", cascade={"ALL"}, orphanRemoval=true)
     * */
    private $psmaps;

    /**
     * @ORM\OneToMany(targetEntity="Solution", mappedBy="problem", cascade={"ALL"}, orphanRemoval=true)
     * */
    private $solutions;

    public function __construct($label, $author)
    {
        //init default values:
        $this->setAuthor($author);
        $this->setData([
            'label' => $label,
            'question' => '',
            'hint1' => '',
            'hint2' => '',
            'solution' => '',
            'answer' => '',
            'options' => '',
            'flags' => 0,
            'std' => 0,
            'topic' => 0,
            'diff' => 0,
            'qtype' => 'num',
        ]);
    }

    public function setData($srcArr)
    {
        if (array_key_exists('label', $srcArr))
            $this->setLabel($srcArr["label"]);
        if (array_key_exists('question', $srcArr))
            $this->setQuestion($srcArr["question"]);
        if (array_key_exists('hint1', $srcArr))
            $this->setHint1($srcArr["hint1"]);
        if (array_key_exists('hint2', $srcArr))
            $this->setHint2($srcArr["hint2"]);
        if (array_key_exists('solution', $srcArr))
            $this->setSolution($srcArr["solution"]);
        if (array_key_exists('answer', $srcArr))
            $this->setAnswer($srcArr["answer"]);
        if (array_key_exists('options', $srcArr))
            $this->setOptions($srcArr["options"]);
        if (array_key_exists('flags', $srcArr))
            $this->setFlags($srcArr["flags"]);
        if (array_key_exists('std', $srcArr))
            $this->setStd($srcArr["std"]);
        if (array_key_exists('topic', $srcArr))
            $this->setTopic($srcArr["topic"]);
        if (array_key_exists('diff', $srcArr))
            $this->setDiff($srcArr["diff"]);
        if (array_key_exists('qtype', $srcArr))
            $this->setQtype($srcArr["qtype"]);
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
     * Set label
     *
     * @param string $label
     * @return Problem
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set question
     *
     * @param string $question
     * @return Problem
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set hint1
     *
     * @param string $hint1
     * @return Problem
     */
    public function setHint1($hint1)
    {
        $this->hint1 = $hint1;

        return $this;
    }

    /**
     * Get hint1
     *
     * @return string
     */
    public function getHint1()
    {
        return $this->hint1;
    }

    /**
     * Set hint2
     *
     * @param string $hint2
     * @return Problem
     */
    public function setHint2($hint2)
    {
        $this->hint2 = $hint2;

        return $this;
    }

    /**
     * Get hint2
     *
     * @return string
     */
    public function getHint2()
    {
        return $this->hint2;
    }

    /**
     * Set solution
     *
     * @param string $solution
     * @return Problem
     */
    public function setSolution($solution)
    {
        $this->solution = $solution;

        return $this;
    }

    /**
     * Get solution
     *
     * @return string
     */
    public function getSolution()
    {
        return $this->solution;
    }

    /**
     * Set answer
     *
     * @param string $answer
     * @return Problem
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
     * Set options
     *
     * @param string $options
     * @return Problem
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get options
     *
     * @return string
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set flags
     *
     * @param integer $flags
     * @return Problem
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * Get flags
     *
     * @return integer
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Set std
     *
     * @param integer $std
     * @return Problem
     */
    public function setStd($std)
    {
        $this->std = $std;

        return $this;
    }

    /**
     * Get std
     *
     * @return integer
     */
    public function getStd()
    {
        return $this->std;
    }

    /**
     * Set topic
     *
     * @param integer $topic
     * @return Problem
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Get topic
     *
     * @return integer
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set diff
     *
     * @param integer $diff
     * @return Problem
     */
    public function setDiff($diff)
    {
        $this->diff = $diff;

        return $this;
    }

    /**
     * Get diff
     *
     * @return integer
     */
    public function getDiff()
    {
        return $this->diff;
    }

    /**
     * Set qtype
     *
     * @param string $qtype
     * @return Problem
     */
    public function setQtype($qtype)
    {
        $this->qtype = $qtype;

        return $this;
    }

    /**
     * Get qtype
     *
     * @return string
     */
    public function getQtype()
    {
        return $this->qtype;
    }

    /**
     * Add psmaps
     *
     * @param \Magnets\MathgymBundle\Entity\PSMap $psmaps
     * @return Problem
     */
    public function addPsmap(\Magnets\MathgymBundle\Entity\PSMap $psmaps)
    {
        $this->psmaps[] = $psmaps;

        return $this;
    }

    /**
     * Remove psmaps
     *
     * @param \Magnets\MathgymBundle\Entity\PSMap $psmaps
     */
    public function removePsmap(\Magnets\MathgymBundle\Entity\PSMap $psmaps)
    {
        $this->psmaps->removeElement($psmaps);
    }

    /**
     * Get psmaps
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPsmaps()
    {
        return $this->psmaps;
    }

    /**
     * Add solutions
     *
     * @param \Magnets\MathgymBundle\Entity\Solution $solutions
     * @return Problem
     */
    public function addSolution(\Magnets\MathgymBundle\Entity\Solution $solutions)
    {
        $this->solutions[] = $solutions;

        return $this;
    }

    /**
     * Remove solutions
     *
     * @param \Magnets\MathgymBundle\Entity\Solution $solutions
     */
    public function removeSolution(\Magnets\MathgymBundle\Entity\Solution $solutions)
    {
        $this->solutions->removeElement($solutions);
    }

    /**
     * Get solutions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSolutions()
    {
        return $this->solutions;
    }

    /**
     * Set author
     *
     * @param \Magnets\MathgymBundle\Entity\User $author
     * @return Problem
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Problem
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
}
