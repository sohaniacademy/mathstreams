<?php

namespace Magnets\MathgymBundle\Entity;

/**
 * GlobalvarRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GlobalvarRepository extends \Doctrine\ORM\EntityRepository
{
    public function persist($var)
    {
        $this->_em->persist($var);
    }

    public function remove($var)
    {
        $this->_em->remove($var);
    }

    public function createNew($key, $value)
    {
        $newVar = new Globalvar($key, $value);
        $this->persist($newVar);
        return $newVar;
    }
}
