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
 * Magnets\MathgymBundle\Entity\Token
 *
 * @ORM\Table(name="tokens")
 * @ORM\Entity(repositoryClass="Magnets\MathgymBundle\Entity\TokenRepository")
 */
class Token {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $request;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tkey;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expires;

    public function __construct($user, $type) {
        $this->request = $type;
        $this->user = $user;
        $this->tkey = md5(strval(rand(100000, 999999)));
        $this->expires = new \DateTime('now +1 hour');
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
     * Set label
     *
     * @param string $label
     * @return Token
     */
    public function setLabel($label) {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * Set request
     *
     * @param string $request
     * @return Token
     */
    public function setRequest($request) {
        $this->request = $request;

        return $this;
    }

    /**
     * Get request
     *
     * @return string 
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Set key
     *
     * @param string $key
     * @return Token
     */
    public function setKey($key) {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string 
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Set user
     *
     * @param string $user
     * @return Token
     */
    public function setUser($user) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return string 
     */
    public function getUser() {
        return $this->user;
    }


    /**
     * Set expires
     *
     * @param \DateTime $expires
     * @return Token
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    
        return $this;
    }

    /**
     * Get expires
     *
     * @return \DateTime 
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set tkey
     *
     * @param string $tkey
     * @return Token
     */
    public function setTkey($tkey)
    {
        $this->tkey = $tkey;
    
        return $this;
    }

    /**
     * Get tkey
     *
     * @return string 
     */
    public function getTkey()
    {
        return $this->tkey;
    }

    /**
     * Set tokenkey
     *
     * @param string $tokenkey
     * @return Token
     */
    public function setTokenkey($tokenkey)
    {
        $this->tokenkey = $tokenkey;
    
        return $this;
    }

    /**
     * Get tokenkey
     *
     * @return string 
     */
    public function getTokenkey()
    {
        return $this->tokenkey;
    }
}
