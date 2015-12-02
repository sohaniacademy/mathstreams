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
 * Magnets\MathgymBundle\Entity\Globalvar
 *
 * @ORM\Table(name="globalvars")
 * @ORM\Entity(repositoryClass="Magnets\MathgymBundle\Entity\GlobalvarRepository")
 */
class Globalvar
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $varKey;

    /**
     * @ORM\Column(type="text")
     */
    private $varValue;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="integer")
     */
    private $cacheDirty;

    public function __construct($key, $value)
    {
        $this->setVarKey($key);
        $this->setVarValue($value);
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
     * Set varKey
     *
     * @param string $varKey
     *
     * @return Globalvar
     */
    public function setVarKey($varKey)
    {
        $this->varKey = $varKey;

        return $this;
    }

    /**
     * Get varKey
     *
     * @return string
     */
    public function getVarKey()
    {
        return $this->varKey;
    }

    /**
     * Set varValue
     *
     * @param string $varValue
     *
     * @return Globalvar
     */
    public function setVarValue($varValue)
    {
        $this->varValue = $varValue;
        $this->setCreated(new \DateTime('now'));
        $this->setCacheDirty(0);

        return $this;
    }

    /**
     * Get varValue
     *
     * @return string
     */
    public function getVarValue()
    {
        return $this->varValue;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Globalvar
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
     * Set cacheDirty
     *
     * @param integer $cacheDirty
     *
     * @return Globalvar
     */
    public function setCacheDirty($cacheDirty)
    {
        $this->cacheDirty = $cacheDirty;

        return $this;
    }

    /**
     * Get cacheDirty
     *
     * @return integer
     */
    public function getCacheDirty()
    {
        return $this->cacheDirty;
    }
}
