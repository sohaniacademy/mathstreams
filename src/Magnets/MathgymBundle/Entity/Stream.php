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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Magnets\MathgymBundle\Entity\Stream
 *
 * @ORM\Table(name="streams")
 * @ORM\Entity(repositoryClass="Magnets\MathgymBundle\Entity\StreamRepository")
 */
class Stream {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="PSMap", mappedBy="stream", cascade={"ALL"}, orphanRemoval=true)
     * */
    private $psmaps;
    
    /**
     * @ORM\OneToMany(targetEntity="LSMap", mappedBy="stream", cascade={"ALL"}, orphanRemoval=true)
     * */
    private $lsmaps;
    
    // ...
    /**
     * @ORM\OneToMany(targetEntity="Stream", mappedBy="parent")
     * */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Stream", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * */
    private $parent;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $stype;

    public function __construct($name) {
        $this->name = $name;
        $this->description = '';
        $this->psmaps = new ArrayCollection();
        $this->lsmaps = new ArrayCollection();
        $this->stype = '';
        $this->parent = null;
        $this->children = new ArrayCollection();
    }

    public function setData($data) {
        $this->name = $data['name'];
        $this->description = $data['descr'];
        $this->stype = $data['type'];
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
     * Set name
     *
     * @param string $name
     * @return Stream
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Add psmaps
     *
     * @param \Magnets\MathgymBundle\Entity\PSMap $psmaps
     * @return Stream
     */
    public function addPsmap(\Magnets\MathgymBundle\Entity\PSMap $psmaps) {
        $this->psmaps[] = $psmaps;

        return $this;
    }

    /**
     * Remove psmaps
     *
     * @param \Magnets\MathgymBundle\Entity\PSMap $psmaps
     */
    public function removePsmap(\Magnets\MathgymBundle\Entity\PSMap $psmaps) {
        $this->psmaps->removeElement($psmaps);
    }

    /**
     * Get psmaps
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPsmaps() {
        return $this->psmaps;
    }

    /**
     * Set stype
     *
     * @param string $stype
     * @return Stream
     */
    public function setStype($stype) {
        $this->stype = $stype;

        return $this;
    }

    /**
     * Get stype
     *
     * @return string 
     */
    public function getStype() {
        return $this->stype;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Stream
     */
    public function setDescription($description) {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription() {
        return $this->description;
    }


    /**
     * Add children
     *
     * @param \Magnets\MathgymBundle\Entity\Stream $children
     * @return Stream
     */
    public function addChild(\Magnets\MathgymBundle\Entity\Stream $children)
    {
        $this->children[] = $children;
    
        return $this;
    }

    /**
     * Remove children
     *
     * @param \Magnets\MathgymBundle\Entity\Stream $children
     */
    public function removeChild(\Magnets\MathgymBundle\Entity\Stream $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \Magnets\MathgymBundle\Entity\Stream $parent
     * @return Stream
     */
    public function setParent(\Magnets\MathgymBundle\Entity\Stream $parent = null)
    {
        $this->parent = $parent;
    
        return $this;
    }

    /**
     * Get parent
     *
     * @return \Magnets\MathgymBundle\Entity\Stream 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add lsmap
     *
     * @param \Magnets\MathgymBundle\Entity\LSMap $lsmap
     *
     * @return Stream
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
}
