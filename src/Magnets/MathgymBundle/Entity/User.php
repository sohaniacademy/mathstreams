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
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Magnets\MathgymBundle\Entity\User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Magnets\MathgymBundle\Entity\UserRepository")
 */
class User implements UserInterface
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=63)
     */
    private $ajaxsalt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fullname;

    //TODO: Make this a JSON-array of sorts?
    /**
     * @ORM\Column(type="integer")
     */
    private $score;

    /**
     * @ORM\OneToMany(targetEntity="Problem", mappedBy="author", cascade={"ALL"}, orphanRemoval=true)
     * */
    private $problems;

    /**
     * @ORM\OneToMany(targetEntity="Lesson", mappedBy="author", cascade={"ALL"}, orphanRemoval=true)
     * */
    private $lessons;

    /**
     * @ORM\OneToMany(targetEntity="Solution", mappedBy="author", cascade={"ALL"}, orphanRemoval=true)
     * */
    private $solutions;

    /**
     * @ORM\Column(type="json_array")
     */
    private $pfilters;

    /**
     * @ORM\Column(type="json_array")
     */
    private $lfilters;

    /**
     * @ORM\OneToMany(targetEntity="Psession", mappedBy="user", cascade={"ALL"}, orphanRemoval=true)
     */
    private $psessions;

    /**
     * @ORM\Column(type="json_array")
     */
    private $lsessions;

    /**
     * @ORM\OneToMany(targetEntity="Magnets\MathgymBundle\Entity\Chat\Ticket", mappedBy="user", cascade={"ALL"}, orphanRemoval=true)
     * */
    private $tickets;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(name="is_tester", type="boolean")
     */
    private $isTester;

    /**
     * @ORM\Column(type="simple_array")
     */
    private $roles;

    /**
     * @ORM\Column(type="json_array")
     */
    private $rcLimits;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdOn;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastLogin;


    public function __construct($email,$password,$fullname)
    {
        $this->setEmail($email);
        $this->setPassword($password);
        $this->initSalt();
        $this->setFullname($fullname);
        $this->setScore(0);
        $this->setPfilters(json_decode('[]'));//{"name":"Sample 1","data":[{"sid":[1],"freq":1},{"sid":[2,3],"freq":1}]},{"name":"Sample2","data":[{"sid":[2],"freq":1},{"sid":[1,3],"freq":1}]}]'));
        $this->setLfilters(json_decode('[]'));//[{"name":"Sample 1","data":[{"sid":[1],"freq":1},{"sid":[2,3],"freq":1}]},{"name":"Sample2","data":[{"sid":[2],"freq":1},{"sid":[1,3],"freq":1}]}]'));
        $this->setLsessions(json_decode('[]'));
        $this->setIsActive(true);
        $this->setIsTester(false);
        $this->setRoles(['ROLE_USER']);
        $this->setRcLimits([]);
        $this->setCreatedOn(new \DateTime('now'));
        $this->setLastLogin(new \DateTime('@0'));
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {

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
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set fullname
     *
     * @param string $fullname
     * @return User
     */
    public function setFullname($fullname)
    {
        $this->fullname = $fullname;

        return $this;
    }

    /**
     * Get fullname
     *
     * @return string
     */
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * Set roles
     *
     * @param array $roles
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Set score
     *
     * @param integer $score
     * @return User
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
     * Set pfilters
     *
     * @param string $pfilters
     * @return User
     */
    public function setPfilters($pfilters)
    {
        $this->pfilters = $pfilters;

        return $this;
    }

    /**
     * Get pfilters
     *
     * @return string
     */
    public function getPfilters()
    {
        return $this->pfilters;
    }

    /**
     * Set lfilters
     *
     * @param string $lfilters
     * @return User
     */
    public function setLfilters($lfilters)
    {
        $this->lfilters = $lfilters;

        return $this;
    }

    /**
     * Get lfilters
     *
     * @return string
     */
    public function getLfilters()
    {
        return $this->lfilters;
    }

    /**
     * Set ajaxsalt
     *
     * @param string $ajaxsalt
     * @return User
     */
    public function setAjaxsalt($ajaxsalt)
    {
        $this->ajaxsalt = $ajaxsalt;

        return $this;
    }

    /**
     * Get ajaxsalt
     *
     * @return string
     */
    public function getAjaxsalt()
    {
        return $this->ajaxsalt;
    }

    public function initSalt()
    {
        $this->setAjaxsalt(bin2hex(random_bytes(32)));
    }

    /**
     * Set createdOn
     *
     * @param \DateTime $createdOn
     *
     * @return User
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * Get createdOn
     *
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     *
     * @return User
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set rcLimits
     *
     * @param array $rcLimits
     *
     * @return User
     */
    public function setRcLimits($rcLimits)
    {
        $this->rcLimits = $rcLimits;

        return $this;
    }

    /**
     * Get rcLimits
     *
     * @return array
     */
    public function getRcLimits()
    {
        return $this->rcLimits;
    }

    /**
     * Set lsessions
     *
     * @param array $lsessions
     *
     * @return User
     */
    public function setLsessions($lsessions)
    {
        $this->lsessions = $lsessions;

        return $this;
    }

    /**
     * Get lsessions
     *
     * @return array
     */
    public function getLsessions()
    {
        return $this->lsessions;
    }

    /**
     * Add problem
     *
     * @param \Magnets\MathgymBundle\Entity\Problem $problem
     *
     * @return User
     */
    public function addProblem(\Magnets\MathgymBundle\Entity\Problem $problem)
    {
        $this->problems[] = $problem;

        return $this;
    }

    /**
     * Remove problem
     *
     * @param \Magnets\MathgymBundle\Entity\Problem $problem
     */
    public function removeProblem(\Magnets\MathgymBundle\Entity\Problem $problem)
    {
        $this->problems->removeElement($problem);
    }

    /**
     * Get problems
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProblems()
    {
        return $this->problems;
    }

    /**
     * Add lesson
     *
     * @param \Magnets\MathgymBundle\Entity\Lesson $lesson
     *
     * @return User
     */
    public function addLesson(\Magnets\MathgymBundle\Entity\Lesson $lesson)
    {
        $this->lessons[] = $lesson;

        return $this;
    }

    /**
     * Remove lesson
     *
     * @param \Magnets\MathgymBundle\Entity\Lesson $lesson
     */
    public function removeLesson(\Magnets\MathgymBundle\Entity\Lesson $lesson)
    {
        $this->lessons->removeElement($lesson);
    }

    /**
     * Get lessons
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLessons()
    {
        return $this->lessons;
    }

    /**
     * Add solution
     *
     * @param \Magnets\MathgymBundle\Entity\Solution $solution
     *
     * @return User
     */
    public function addSolution(\Magnets\MathgymBundle\Entity\Solution $solution)
    {
        $this->solutions[] = $solution;

        return $this;
    }

    /**
     * Remove solution
     *
     * @param \Magnets\MathgymBundle\Entity\Solution $solution
     */
    public function removeSolution(\Magnets\MathgymBundle\Entity\Solution $solution)
    {
        $this->solutions->removeElement($solution);
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
     * Add psession
     *
     * @param \Magnets\MathgymBundle\Entity\Psession $psession
     *
     * @return User
     */
    public function addPsession(\Magnets\MathgymBundle\Entity\Psession $psession)
    {
        $this->psessions[] = $psession;

        return $this;
    }

    /**
     * Remove psession
     *
     * @param \Magnets\MathgymBundle\Entity\Psession $psession
     */
    public function removePsession(\Magnets\MathgymBundle\Entity\Psession $psession)
    {
        $this->psessions->removeElement($psession);
    }

    /**
     * Get psessions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPsessions()
    {
        return $this->psessions;
    }

    /**
     * Set isTester
     *
     * @param boolean $isTester
     *
     * @return User
     */
    public function setIsTester($isTester)
    {
        $this->isTester = $isTester;

        return $this;
    }

    /**
     * Get isTester
     *
     * @return boolean
     */
    public function getIsTester()
    {
        return $this->isTester;
    }

    /**
     * Add ticket
     *
     * @param \Magnets\MathgymBundle\Entity\Chat\Ticket $ticket
     *
     * @return User
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
}
