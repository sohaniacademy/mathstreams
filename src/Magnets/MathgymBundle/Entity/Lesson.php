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
 * Magnets\MathgymBundle\Entity\Lesson
 *
 * @ORM\Table(name="lessons")
 * @ORM\Entity(repositoryClass="Magnets\MathgymBundle\Entity\LessonRepository")
 */
class Lesson
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
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="text")
     */
    private $label;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     *
     * @ORM\Column(type="boolean")
     */
    private $isDraft;

    /**
     * @ORM\Column(type="text")
     */
    private $resources;

    /**
     * @ORM\OneToMany(targetEntity="LSMap", mappedBy="lesson", cascade={"ALL"}, orphanRemoval=true)
     * */
    private $lsmaps;

    public function __construct($author)
    {

        $this->setAuthor($author);
        $this->setLabel("Sample lesson");
        $this->setContent('[{"type":"h2","val":"This is a sample heading"},{"type":"p","val":"This is a sample paragraph."}]');
        $this->setIsdraft(true);
        $this->setResources('');
        $this->setCreated(new \DateTime('now'));
    }

    public function setData($srcArr)
    {
        if (array_key_exists('name', $srcArr))
            $this->name = $srcArr["name"];
        if (array_key_exists('content', $srcArr))
            $this->content = $srcArr["content"];
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
     * Set content
     *
     * @param string $content
     * @return Lesson
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set isDraft
     *
     * @param boolean $isDraft
     * @return Lesson
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
     * Set author
     *
     * @param \Magnets\MathgymBundle\Entity\User $author
     * @return Lesson
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
     * @return Lesson
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
     * Set resources
     *
     * @param string $resources
     *
     * @return Lesson
     */
    public function setResources($resources)
    {
        $this->resources = $resources;

        return $this;
    }

    /**
     * Get resources
     *
     * @return string
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Add lsmap
     *
     * @param \Magnets\MathgymBundle\Entity\LSMap $lsmap
     *
     * @return Lesson
     */
    public function addLsmap(\Magnets\MathgymBundle\Entity\LSMap $lsmap)
    {
        $this->lsmaps[] = $lsmap;

        return $this;
    }

    /**
     * Remove lsmap
     *
     * @param \Magnets\MathgymBundle\Entity\LSMap $lsmap
     */
    public function removeLsmap(\Magnets\MathgymBundle\Entity\LSMap $lsmap)
    {
        $this->lsmaps->removeElement($lsmap);
    }

    /**
     * Get lsmaps
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLsmaps()
    {
        return $this->lsmaps;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return Lesson
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
}
