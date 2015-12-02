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

namespace Magnets\MathgymBundle\Service;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Doctrine\ORM\EntityManager;
use Magnets\MathgymBundle\Entity\Signal;

class UserSignalService
{

    protected $dbh;

    public function __construct(DBHelperService $dbh)
    {
        $this->dbh = $dbh;
    }

    public function msg2Sig($user, $msg, $room, $curTime)
    {
        return json_encode(['rid' => $room->getId(), 'usr-time' => $curTime, 'usr-name' => $user->getFullName(), 'usr-msg' => $msg]);
    }

    public function sig2Msg($sig)
    {
        return json_decode($sig->getPayload());
    }

    public function broadcast($users, $sig, $type, $expiry)
    {
        $sigRep = $this->dbh->getRep('Signal');
        foreach ($users as $user) {
            $sigRep->addSignal($user, $sig, $type, $expiry);
        }
        $sigRep->flush();
    }

    public function getSignals($user, $type)
    {
        $sigRep = $this->dbh->getRep('Signal');

        $sigs = $sigRep->findBy(array('user' => $user, 'type' => $type));

        $msgs = [];
        foreach ($sigs as $sig) {
            $msgs [] = $this->sig2Msg($sig);
            $sigRep->remove($sig);
        }
        $sigRep->flush();
        return $msgs;
    }
}
