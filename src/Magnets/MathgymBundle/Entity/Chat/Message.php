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
 * Magnets\MathgymBundle\Entity\Chat\Message
 *
 * @ORM\Table(name="chat_messages")
 * @ORM\Entity(repositoryClass="Magnets\MathgymBundle\Entity\Chat\MessageRepository")
 */
class Message
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Magnets\MathgymBundle\Entity\User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @ORM\ManyToOne(targetEntity="Chatroom", inversedBy="messages")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     */
    private $room;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="integer")
     */
    private $offset;

//    /**
//     * @ORM\Column(type="datetime")
//     */
//    private $expires;

    public function __construct($room, $user, $offset)
    {
        $this->setAuthor($user);
        $this->setRoom($room);
        $this->setOffset($offset);
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
     *
     * @return Message
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
     * Set offset
     *
     * @param integer $offset
     *
     * @return Message
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Get offset
     *
     * @return integer
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Set author
     *
     * @param \Magnets\MathgymBundle\Entity\User $author
     *
     * @return Message
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
     * Set room
     *
     * @param \Magnets\MathgymBundle\Entity\Chat\Chatroom $room
     *
     * @return Message
     */
    public function setRoom(\Magnets\MathgymBundle\Entity\Chat\Chatroom $room = null)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Get room
     *
     * @return \Magnets\MathgymBundle\Entity\Chat\Chatroom
     */
    public function getRoom()
    {
        return $this->room;
    }
}
