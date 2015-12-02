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
 * Magnets\MathgymBundle\Entity\Chat\Chatroom
 *
 * @ORM\Table(name="chat_rooms")
 * @ORM\Entity(repositoryClass="Magnets\MathgymBundle\Entity\Chat\ChatroomRepository")
 */
class Chatroom {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createtime;

    /**
     * @ORM\Column(type="text")
     */
    private $pinnedContent;

    /**
     * @ORM\OneToMany(targetEntity="Ticket", mappedBy="room", cascade={"ALL"}, orphanRemoval=true)
     * */
    private $tickets;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="room", cascade={"ALL"}, orphanRemoval=true)
     * */
    private $messages;

    /**
     * Chatroom constructor.
     * @param $rname
     */
    public function __construct($rname) {
        $this->setName($rname);
        $this->setCreatetime(new \DateTime('now'));
        $this->setPinnedContent('');
    }

    public function setData($data) {
        $this->setName($data['name']);
        $this->setPinnedContent($data['content']);
//        $this->stype = $data['type'];
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
     * @return Chatroom
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
     * Set createtime
     *
     * @param \DateTime $createtime
     * @return Chatroom
     */
    public function setCreatetime($createtime) {
        $this->createtime = $createtime;

        return $this;
    }

    /**
     * Get createtime
     *
     * @return \DateTime 
     */
    public function getCreatetime() {
        return $this->createtime;
    }


    /**
     * Set pinnedContent
     *
     * @param string $pinnedContent
     *
     * @return Chatroom
     */
    public function setPinnedContent($pinnedContent)
    {
        $this->pinnedContent = $pinnedContent;

        return $this;
    }

    /**
     * Get pinnedContent
     *
     * @return string
     */
    public function getPinnedContent()
    {
        return $this->pinnedContent;
    }

    /**
     * Add ticket
     *
     * @param \Magnets\MathgymBundle\Entity\Chat\Ticket $ticket
     *
     * @return Chatroom
     */
    public function addTicket(\Magnets\MathgymBundle\Entity\Chat\Ticket $ticket)
    {
        $this->tickets[] = $ticket;

        return $this;
    }

    /**
     * Remove ticket
     *
     * @param \Magnets\MathgymBundle\Entity\Chat\Ticket $ticket
     */
    public function removeTicket(\Magnets\MathgymBundle\Entity\Chat\Ticket $ticket)
    {
        $this->tickets->removeElement($ticket);
    }

    /**
     * Get tickets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * Add message
     *
     * @param \Magnets\MathgymBundle\Entity\Chat\Message $message
     *
     * @return Chatroom
     */
    public function addMessage(\Magnets\MathgymBundle\Entity\Chat\Message $message)
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * Remove message
     *
     * @param \Magnets\MathgymBundle\Entity\Chat\Message $message
     */
    public function removeMessage(\Magnets\MathgymBundle\Entity\Chat\Message $message)
    {
        $this->messages->removeElement($message);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
