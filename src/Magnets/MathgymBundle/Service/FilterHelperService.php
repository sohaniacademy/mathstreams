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

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

class FilterHelperService
{

    protected $dbh;

    public function __construct(DBHelperService $dbh, RoleHierarchy $roleHierarchy)
    {
        $this->dbh = $dbh;
        $this->roleHierarchy = $roleHierarchy;

    }

    public function getAllRoles($user)
    {
        return array_map(function ($x) {
            return $x->getRole();
        }, $this->roleHierarchy->getReachableRoles(
            array_map(function ($y) {
                return new Role($y);
            }, $user->getRoles())
        ));
    }

    public function nb2LMap($nbArr)
    {
        return array_map(function ($s) {
            return $s->getLesson()->getId();
        }, $nbArr);
    }

    public function canLView($lesson, $user)
    {
        return (in_array('ROLE_ADMIN', $this->getAllRoles($user))) || ($lesson->getAuthor() == $user) || (!$lesson->getIsDraft());
    }

    public function canLEdit($lesson, $user)
    {
        return (in_array('ROLE_ADMIN', $this->getAllRoles($user))) || ($lesson->getAuthor() == $user);
    }

    public function getLfiltered($fid, $user, $maxResults)
    {
        $lesRep = $this->dbh->getRep('Lesson');
        $lsRep = $this->dbh->getRep('LSMap');
        $streamRep = $this->dbh->getRep('Stream');
        $nbRep = $this->dbh->getRep('Notebook');

        // Sample:                        [{"name":"Simple1","data":[{"sid":[1],"freq":1},{"sid":[2,3],"freq":1}]},{"name":"Simple2","data":[{"sid":[2],"freq":1},{"sid":[1,3],"freq":1}]}]

        //separately, obtain nb stats if needed..
        $inProg = null;
        $solved = null;

        $fOb = json_decode($fid, true);
        $result = [];
        foreach ($fOb as $fComp) {
            $qb = $this->dbh->getQB();
            $qb = $qb->select('l')->from('MagnetsMathgymBundle:Lesson', 'l', 'l.id');
            foreach ($fComp["sid"] as $sid) {
                if ($sid == 'all') {
                    $qb = $qb->andWhere('1 = 1');
                } else if ($sid == 'mine') { //designed by user:
                    $qb = $qb->andWhere('l.author = :usr')->setParameter('usr', $user);
                } else if ($sid == 'solved') {
                    $solved = $solved ? $solved : $this->nb2LMap($nbRep->findBy(array('author' => $user, 'isDraft' => false)));
                    if (sizeof($solved) > 0)
                        $qb = $qb->andWhere('l.id in (:solved)')->setParameter('solved', $solved);//->setParameter('usr',$user);
                    else
                        $qb = $qb->andWhere('1 = 0');
                } else if ($sid == 'inprogress') { //in progress:
                    $inProg = $inProg ? $inProg : $this->nb2LMap($nbRep->findBy(array('author' => $user, 'isDraft' => true)));
                    if (sizeof($inProg) > 0)
                        $qb = $qb->andWhere('l.id in (:inprog)')->setParameter('inprog', $inProg);
                    else
                        $qb = $qb->andWhere('1 = 0');
                } else if ($sid == 'new') { //new:
                    $solved = $solved ? $solved : $this->nb2LMap($nbRep->findBy(array('author' => $user, 'isDraft' => false)));
                    $inProg = $inProg ? $inProg : $this->nb2LMap($nbRep->findBy(array('author' => $user, 'isDraft' => true)));
                    $totalSeen = array_merge($inProg, $solved);
                    if (sizeof($totalSeen) > 0) {
                        $qb = $qb->andWhere('l.id not in (:newLes)')->setParameter('newLes', $totalSeen);
                    } else {
                        $qb = $qb->andWhere('1 = 1');
                    }
                } else if (preg_match('/[0-9]+/', $sid)) { //stream id
                    $strIds = $this->nb2LMap($lsRep->findBy(array('stream' => $streamRep->find($sid))));//todo:this works luckily
                    $qb = $qb->andWhere('l.id in (:str' . $sid . ')')->setParameter('str' . $sid, $strIds);
                } else
                    continue;
            }
            $qb = $qb->setMaxResults($maxResults);
            $result = array_merge($result, $qb->getQuery()->getResult());
            //todo: interprete freq data, to randomly drop results from the list etc..
            //maybe better to do it after all accumulation..
        }
        $allowedLessons = [];
        foreach ($result as $lid => $lesson) {
            if ($this->canLView($lesRep, $user)) {
                $allowedLessons[] = $lesson;
            }
        }
        return $allowedLessons;
    }

    public function sol2PMap($solArr)
    {
        return array_map(function ($s) {
            return $s->getProblem()->getId();
        }, $solArr);
    }

    public function canPView($problem, $user)
    {
        return (in_array('ROLE_ADMIN', $this->getAllRoles($user))) || ($problem->getAuthor() == $user) || (!$problem->getIsDraft());
    }

    public function canPEdit($problem, $user)
    {
        return (in_array('ROLE_ADMIN', $this->getAllRoles($user))) || ($problem->getAuthor() == $user);
    }

    public function getFullMap($user)
    {
        //check if fullmap is available ..
        if (($fullMap = $this->dbh->getVar('fullMap')) !== null) {
            return json_decode($fullMap, true);
        }

        $probRep = $this->dbh->getRep('Problem');
        $psRep = $this->dbh->getRep('PSMap');
        $solRep = $this->dbh->getRep('Solution');

        $lesRep = $this->dbh->getRep('Lesson');
        $lsRep = $this->dbh->getRep('LSMap');
        $streamRep = $this->dbh->getRep('Stream');
        $nbRep = $this->dbh->getRep('Notebook');

        $fullMap = [];
        foreach ($streamRep->findAll() as $stream) {
//            $cats = ['std', 'topic', 'diff', 'other'];
//            $cat = $stream->getStype();
//            if (!in_array($cat, $cats)) {
//                $cat = 'other';
//            }
            $lessons = [];
            $problems = [];
            foreach ($lsRep->findBy(['stream' => $stream]) as $lsmap) {
                $lessons [] = intVal($lsmap->getLesson()->getId());
            };
            foreach ($psRep->findBy(['stream' => $stream]) as $psmap) {
                $problems[] = intVal($psmap->getProblem()->getId());
            };
            $streamData = ['id' => $stream->getId(),
                'name' => $stream->getName(),
                'cat' => $stream->getStype(),
                'map' => ['l'=>$lessons,'p'=>$problems],
            ];
            $fullMap[] = $streamData;
        }

        $fullMapOb = $this->dbh->encodeRNA($fullMap, ['id', 'name', 'cat', 'map']);
        $this->dbh->setVar('fullLsMap', json_encode($fullMapOb));
        return $fullMapOb;
    }

    public function getFullLsMap($user)
    {
        //check if fullmap is available ..
        if (($fullMap = $this->dbh->getVar('fullLsMap')) !== null) {
            return json_decode($fullMap, true);
        }

        $lesRep = $this->dbh->getRep('Lesson');
        $lsRep = $this->dbh->getRep('LSMap');
        $streamRep = $this->dbh->getRep('Stream');
        $nbRep = $this->dbh->getRep('Notebook');

        $fullMap = [];
        foreach ($streamRep->findAll() as $stream) {
//            $cats = ['std', 'topic', 'diff', 'other'];
//            $cat = $stream->getStype();
//            if (!in_array($cat, $cats)) {
//                $cat = 'other';
//            }
            $lessons = [];
            foreach ($lsRep->findBy(['stream' => $stream]) as $lsmap) {
                $lessons [] = intVal($lsmap->getLesson()->getId());
            };
            $streamData = ['id' => $stream->getId(),
                'name' => $stream->getName(),
                'cat' => $stream->getStype(),
                'map' => $lessons,
            ];
            $fullMap[] = $streamData;
        }

        $fullMapOb = $this->dbh->encodeRNA($fullMap, ['id', 'name', 'cat', 'map']);
        $this->dbh->setVar('fullLsMap', json_encode($fullMapOb));
        return $fullMapOb;
    }

    public function getFullPsMap($user)
    {
        //check if fullmap is available ..
        if (($fullMap = $this->dbh->getVar('fullPsMap')) !== null) {
            return json_decode($fullMap, true);
        }

        $probRep = $this->dbh->getRep('Problem');
        $psRep = $this->dbh->getRep('PSMap');
        $streamRep = $this->dbh->getRep('Stream');
        $solRep = $this->dbh->getRep('Solution');

        $fullMap = [];
        foreach ($streamRep->findAll() as $stream) {
//            $cats = ['std', 'topic', 'diff', 'other'];
//            $cat = $stream->getStype();
//            if (!in_array($cat, $cats)) {
//                $cat = 'other';
//            }
            $problems = [];
            foreach ($psRep->findBy(['stream' => $stream]) as $psmap) {
                $problems [] = intVal($psmap->getProblem()->getId());
            };
            $streamData = ['id' => $stream->getId(),
                'name' => $stream->getName(),
                'cat' => $stream->getStype(),
                'pmap' => $problems,
            ];
            $fullMap[] = $streamData;
        }

        $fullMapOb = $this->dbh->encodeRNA($fullMap, ['id', 'name', 'cat', 'pmap']);
        $this->dbh->setVar('fullPsMap', json_encode($fullMapOb));
        return $fullMapOb;
    }

    public function getPstats($user)
    {
        $solRep = $this->dbh->getRep('Solution');

        $drafts = $solRep->findBy(['author' => $user]);

        //make into an associative array.. there should be a better way of doing this!
        $scores = [];
        $status = [];
        $stats = [];
        foreach ($drafts as $draft) {
            $pid = $draft->getProblem()->getId();
//            $scores[$pid] = $draft->getScore();
//            $status[$pid] = ($draft->getIsDraft() ? '1' : '2');//0 new, 1 inprog, 2 solved
            $stats[$pid] = ['score' => $draft->getScore(), 'status' => ($draft->getIsDraft() ? 1 : 2)];
        }
        //return ['status' => $status, 'score' => $scores];
        return $this->dbh->encodeRNA($stats, ['score', 'status']);
    }

    public function getLstats($user)
    {
        $nbRep = $this->dbh->getRep('Notebook');

        $drafts = $nbRep->findBy(['author' => $user]);

        //make into an associative array.. there should be a better way of doing this!
        $scores = [];
        $status = [];
        $stats = [];
        foreach ($drafts as $draft) {
            $lid = $draft->getLesson()->getId();
//            $scores[$pid] = $draft->getScore();
//            $status[$pid] = ($draft->getIsDraft() ? '1' : '2');//0 new, 1 inprog, 2 solved
            $stats[$lid] = ['score' => $draft->getScore(), 'status' => ($draft->getIsDraft() ? 1 : 2)];
        }
        //return ['status' => $status, 'score' => $scores];
        return $this->dbh->encodeRNA($stats, ['score', 'status']);
    }

    public function getPfiltered2($fid, $user, $maxResults)
    {
        //todo: use redis for some sort of caching mechanism..
        //then any problem/stream change will have to dirty the cache..

        $probRep = $this->dbh->getRep('Problem');
        $psRep = $this->dbh->getRep('PSMap');
        $streamRep = $this->dbh->getRep('Stream');
        $solRep = $this->dbh->getRep('Solution');

        $pCache = [];
        $pStridCache = [];
        // Sample:                        [{"name":"Simple1","data":[{"sid":[1],"freq":1},{"sid":[2,3],"freq":1}]},{"name":"Simple2","data":[{"sid":[2],"freq":1},{"sid":[1,3],"freq":1}]}]

        //separately, obtain nb stats if needed..
        //$inProg = null;
        //$solved = null;

        $fOb = json_decode($fid, true);
        foreach ($fOb as $fComp) {
            foreach ($fComp["sid"] as $sid) {
                if ($sid == 'all' && !array_key_exists('all', $pCache)) {
                    $pCache['all'] = array_map(function ($x) {
                        return $x->getId();
                    }, $probRep->findAll());
                } else if ($sid == 'mine' && !array_key_exists('mine', $pCache)) {
                    $pCache['mine'] = array_map(function ($x) {
                        return $x->getId();
                    }, $probRep->findBy(['author' => $user]));
                } else if ($sid == 'solved' && !array_key_exists('solved', $pCache)) {
                    $pCache['solved'] = array_map(function ($x) {
                        return $x->getProblem()->getId();
                    }, $solRep->findBy(['author' => $user, 'isDraft' => false]));
                } else if ($sid == 'inprogress' && !array_key_exists('inprogress', $pCache)) {
                    $pCache['inprogress'] = array_map(function ($x) {
                        return $x->getProblem()->getId();
                    }, $solRep->findBy(['author' => $user, 'isDraft' => true]));
                } else if ($sid == 'new' && !array_key_exists('new', $pCache)) {
                    if (!array_key_exists('all', $pCache)) {
                        $pCache['all'] = array_map(function ($x) {
                            return $x->getId();
                        }, $probRep->findAll());
                    }
                    if (!array_key_exists('solved', $pCache)) {
                        $pCache['solved'] = array_map(function ($x) {
                            return $x->getProblem()->getId();
                        }, $solRep->findBy(['author' => $user, 'isDraft' => false]));
                    }
                    if (!array_key_exists('inprogress', $pCache)) {
                        $pCache['inprogress'] = array_map(function ($x) {
                            return $x->getProblem()->getId();
                        }, $solRep->findBy(['author' => $user, 'isDraft' => true]));
                    }
                    $pCache['new'] = array_diff($pCache['all'], $pCache['inprogress'], $pCache['solved']);
                } else if (preg_match('/[0-9]+/', $sid) && !array_key_exists(strval($sid), $pStridCache)) { //stream id
                    $pStridCache[strval($sid)] = $sid;
//                        array_map(function ($x) {
//                        return $x->getProblem()->getId();
//                    }, $psRep->findBy(['stream' => $streamRep->find($sid)]));
                } else
                    continue;
            }
        }
        //fetch id data from cached strids..
        $streams = $streamRep->findAll(['id' => $pStridCache]);
        $cachedPsMaps = $psRep->findAll(['stream' => $streams]);

        foreach ($cachedPsMaps as $cachedPsMap) {
            $pCache[$sid][] = $cachedPsMap;
        }
        foreach ($pStridCache as $strid) {

        }
        //finally, start applying the actual filter..

    }
//            //todo: interprete freq data, to randomly drop results from the list etc..
//            foreach ($qb->getQuery()->getResult() as $pid => $problem) {
//                if ($this->canPView($probRep, $user)) {
//                    $allowedProblems[] = $problem;
//                    $maxResults--;
//                }
//            }
//        }
//        return $allowedProblems;
//    }

    public function getPfiltered($fid, $user, $maxResults)
    {
        //todo: use redis for some sort of caching mechanism..
        //then any problem/stream change will have to dirty the cache..

        $probRep = $this->dbh->getRep('Problem');
        $psRep = $this->dbh->getRep('PSMap');
        $streamRep = $this->dbh->getRep('Stream');
        $solRep = $this->dbh->getRep('Solution');

        // Sample:                        [{"name":"Simple1","data":[{"sid":[1],"freq":1},{"sid":[2,3],"freq":1}]},{"name":"Simple2","data":[{"sid":[2],"freq":1},{"sid":[1,3],"freq":1}]}]

        //separately, obtain nb stats if needed..
        $inProg = null;
        $solved = null;

        $fOb = json_decode($fid, true);
        $allowedProblems = [];
        foreach ($fOb as $fComp) {
            if ($maxResults <= 0)
                break;
            $qb = $this->dbh->getQB();
            $qb = $qb->select('p')->from('MagnetsMathgymBundle:Problem', 'p', 'p.id');

            foreach ($fComp["sid"] as $sid) {
                if ($sid == 'all') {
                    $qb = $qb->andWhere('1 = 1');
                } else if ($sid == 'mine') { //designed by user:
                    $qb = $qb->andWhere('p.author = :usr')->setParameter('usr', $user);
                } else if ($sid == 'solved') {
                    $solved = $solved ? $solved : $this->sol2PMap($solRep->findBy(array('author' => $user, 'isDraft' => false)));
                    if (sizeof($solved) > 0)
                        $qb = $qb->andWhere('p.id in (:solved)')->setParameter('solved', $solved);//->setParameter('usr',$user);
                    else
                        $qb = $qb->andWhere('1 = 0');
                } else if ($sid == 'inprogress') { //in progress:
                    $inProg = $inProg ? $inProg : $this->sol2PMap($solRep->findBy(array('author' => $user, 'isDraft' => true)));
                    if (sizeof($inProg) > 0)
                        $qb = $qb->andWhere('p.id in (:inprog)')->setParameter('inprog', $inProg);
                    else
                        $qb = $qb->andWhere('1 = 0');
                } else if ($sid == 'new') { //new:
                    $solved = $solved ? $solved : $this->sol2PMap($solRep->findBy(array('author' => $user, 'isDraft' => false)));
                    $inProg = $inProg ? $inProg : $this->sol2PMap($solRep->findBy(array('author' => $user, 'isDraft' => true)));
                    $totalSeen = array_merge($inProg, $solved);
                    if (sizeof($totalSeen) > 0) {
                        $qb = $qb->andWhere('p.id not in (:newLes)')->setParameter('newLes', $totalSeen);
                    } else {
                        $qb = $qb->andWhere('1 = 1');
                    }
                } else if (preg_match('/[0-9]+/', $sid)) { //stream id
                    $strIds = $this->sol2PMap($psRep->findBy(array('stream' => $streamRep->find($sid))));//todo:this works luckily
                    $qb = $qb->andWhere('p.id in (:str' . $sid . ')')->setParameter('str' . $sid, $strIds);
                } else
                    continue;
            }
            $qb = $qb->setMaxResults($maxResults);

            //todo: interprete freq data, to randomly drop results from the list etc..
            foreach ($qb->getQuery()->getResult() as $pid => $problem) {
                if ($this->canPView($probRep, $user)) {
                    $allowedProblems[] = $problem;
                    $maxResults--;
                }
            }
        }
        return $allowedProblems;
    }
}