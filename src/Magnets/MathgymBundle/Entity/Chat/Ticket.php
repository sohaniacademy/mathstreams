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

namespace Magnets\MathgymBundle\Entity\Chat;

use Doctrine\ORM\Mapping as ORM;

/**
 * Magnets\MathgymBundle\Entity\Chat\Ticket
 *
 * @ORM\Table(name="chat_tickets")
 * @ORM\Entity(repositoryClass="Magnets\MathgymBundle\Entity\Chat\TicketRepository")
 */
class Ticket {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Magnets\MathgymBundle\Entity\User", inversedBy="tickets")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Chatroom", inversedBy="tickets")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     */
    private $room;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expires;

    public function __construct($room, $user, $expires) {
        $this->setUser($user);
        $this->setRoom($room);
        $this->setExpires($expires);
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
     * Set user
     *
     * @param \Magnets\MathgymBundle\Entity\User $user
     * @return Ticket
     */
    public function setUser(\Magnets\MathgymBundle\Entity\User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Magnets\MathgymBundle\Entity\User 
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set room
     *
     * @param \Magnets\MathgymBundle\Entity\Chat\Chatroom $room
     * @return Ticket
     */
    public function setRoom(\Magnets\MathgymBundle\Entity\Chat\Chatroom $room = null) {
        $this->room = $room;

        return $this;
    }

    /**
     * Get room
     *
     * @return \Magnets\MathgymBundle\Entity\Chat\Chatroom 
     */
    public function getRoom() {
        return $this->room;
    }


    /**
     * Set expires
     *
     * @param \DateTime $expires
     *
     * @return Ticket
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
}
