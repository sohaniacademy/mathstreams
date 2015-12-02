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

class StatsHelperService
{

    protected $dbh;
    protected $ach;

    public function __construct(DBHelperService $dbh, \Symfony\Component\Security\Core\Authorization\AuthorizationChecker $ach)
    {
        $this->dbh = $dbh;
        $this->ach = $ach;
    }

    public function getLStreamsOb($lesson)
    {
        $lsRep = $this->dbh->getRep('LSMap');
        $arr = [];
        foreach ($lsRep->findBy(['lesson' => $lesson]) as $lsOb) {
            $arr [] = ['id' => $lsOb->getStream()->getId(), 'name' => $lsOb->getStream()->getName()];
        }
        return $arr;
    }

    //massive TODO: Merge NB and Solutions, Problems and Lessons etc.. 
    public function getViewLStats($lessons, $user)
    {
        $nbRep = $this->dbh->getRep('Notebook');

        //get solved/unsolved stats..
//        $qb = $this->dbh->getQB()->select('d')->from('MagnetsMathgymBundle:Notebook', 'd', 'd.lesson')->where('d.user = :usr')->setParameter('usr', $user);
//        if (sizeof($lessons) > 0) {
//            $qb = $qb->andWhere('d.lesson in :lessons')->setParameter('lessons', $lessons);
//        } else {
//            $qb = $qb->andWhere('1 = 0');
//        }
//        $drafts = $qb->getQuery()->getResult();
        //$nbRep->createQueryBuilder('d') findBy(array('user' => $user, 'lesson' => $lessons));
        $drafts = $nbRep->findBy(['user' => $user, 'lesson' => $lessons]);

        //make into an associative array.. there should be a better way of doing this!
        $scores = [];
        $status = [];
        foreach ($drafts as $draft) {
            $lid = $draft->getLesson()->getId();
            $scores[$lid] = $draft->getScore();
            $status[$lid] = ($draft->getIsDraft() ? '1' : '2');//0 new, 1 inprog, 2 solved
        }

        // We will prepare the data for Datatables.. temporarily convenient
        // As per some fixed column ordering..
        // Let's say: LID / Name / Author / Streams / Status / Actions
        //$columns = [['title' => 'ID'], ['title' => 'Name'], ['title' => 'Author'], ['title' => 'Streams'], ['title' => 'Status']];
        $returnData = [];
        foreach ($lessons as $lesson) {
            $lid = $lesson->getId();
            if (!array_key_exists($lid, $status)) {
                $status[$lid] = '0';
                $scores[$lid] = 'N/A';
            }
            $returnData [] = [
                'id' => $lid,
                'label' => $lesson->getName(),
                'status' => ['status' => $status[$lid], 'score' => $scores[$lid]],
                'streams' => $this->getLStreamsOb($lesson),
                'created' => $lesson->getCreated()->format("d M Y"),
                'author' => ['id' => $lesson->getId(), 'name' => $lesson->getAuthor()->getFullname()],
            ];
        }

        return $returnData;
        // [ 'lessons' => $lessons, 'scores' => $scores, 'status' => $status];
    }

    public function getPStreamsOb($problem)
    {
        $psRep = $this->dbh->getRep('PSMap');
        $arr = [];
        foreach ($psRep->findBy(['problem' => $problem]) as $psOb) {
            $arr [] = ['id' => $psOb->getStream()->getId(), 'name' => $psOb->getStream()->getName(), 'cat' => $psOb->getStream()->getStype()];
        }
        return $arr;
    }

    public function getViewPStats($problems, $user)
    {
        $solRep = $this->dbh->getRep('Solution');

        $drafts = $solRep->findBy(['user' => $user, 'problem' => $problems]);

        //make into an associative array.. there should be a better way of doing this!
        $scores = [];
        $status = [];
        foreach ($drafts as $draft) {
            $pid = $draft->getProblem()->getId();
            $scores[$pid] = $draft->getScore();
            $status[$pid] = ($draft->getIsDraft() ? '1' : '2');//0 new, 1 inprog, 2 solved
        }

        // We will prepare some columns for Datatables..
        // Let's say: LID / Name / Status / Streams / Created / Author
        $returnData = [];
        foreach ($problems as $problem) {
            $pid = $problem->getId();
            if (!array_key_exists($pid, $status)) {
                $status[$pid] = '0';
                $scores[$pid] = 'N/A';
            }
            $returnData [] = [
                'id' => $pid,
                'label' => $problem->getLabel(),
                'status' => ['status' => $status[$pid], 'score' => $scores[$pid]],
                'streams' => $this->getPStreamsOb($problem),
                'created' => $problem->getCreated()->format("d M Y"),
                'author' => ['id' => $problem->getAuthor()->getId(), 'name' => $problem->getAuthor()->getFullname()],
            ];
        }

        return $returnData;// ['columns' => $columns, 'data' => $returnData];
        // [ 'lessons' => $lessons, 'scores' => $scores, 'status' => $status];
    }

    public function getPStats($pids, $user)
    {
        $probRep = $this->dbh->getRep('Problem');
        $solRep = $this->dbh->getRep('Solution');

        $problems = $probRep->findById($pids);

//get solved/unsolved stats..                        
        $drafts = $solRep->findBy(array('user' => $user, 'problem' => $problems));

//make into an associative array.. there should be a better way of doing this!
        $scores = array();
        $status = array();
        foreach ($drafts as $draft) {
            $pid = $draft->getProblem()->getId();
            $scores[$pid] = $draft->getScore();
            $status[$pid] = ($draft->getIsDraft() ? '1' : '2');
        }
        foreach ($pids as $pid) {
            if (!array_key_exists($pid, $status)) {
                $status[$pid] = '0';
                $scores[$pid] = 'N/A';
            }
        }
        return ['problems' => $problems, 'scores' => $scores, 'status' => $status];
    }

    public function getUserPFilters($user)
    {
        return $this->getUserFilters($user, $user->getPFilters());
    }

    public function getUserLFilters($user)
    {
        return $this->getUserFilters($user, $user->getLFilters());
    }

    public function getUserFilters($user, $filtStr)
    {
//return an array.. 
//TODO: eventually 
        $streamRep = $this->dbh->getRep('Stream');

        $filters = explode(';', $filtStr);
        $filtStrings = [];
        foreach ($filters as $filt) {
            $sids = explode('&', $filt);
            $filtStrings[$filt] = "";
            foreach ($sids as $sid) {
                $stream = $streamRep->find($sid);
                if ($stream !== null) {
                    $filtStrings[$filt] .= $streamRep->find($sid)->getName() . " ";
                }
            }
        }
        return $filtStrings;
    }

    public function nb2LMap($nbArr)
    {
        return array_map(function ($s) {
            return $s->getLesson()->getId();
        }, $nbArr);
    }

    public function applyLFilter($fid, $user)
    {
        $lesRep = $this->dbh->getRep('Lesson');
        $lsRep = $this->dbh->getRep('LSMap');
        $streamRep = $this->dbh->getRep('Stream');
        $nbRep = $this->dbh->getRep('Notebook');

        //determine filter types..
        $sids = explode('&', $fid);

        $qb = $this->dbh->getQB();
        $qb = $qb->select('l.id')->from('MagnetsMathgymBundle:Lesson', 'l', 'l.id');

        //separately, obtain nb stats..
        $inProg = null;
        $solved = null;


        //todo: need queryBuilder-based filtering approach
        foreach ($sids as $sid) { //AND of all components
//supported filter components:
            if ($sid == 'mydesign') { //designed by user:
                $qb = $qb->andWhere('l.author = :usr')->setParameter('usr', $user);
            } else if ($sid == 'solved') { //already solved:
                $solved = $solved ? $solved : $this->nb2LMap($nbRep->findBy(array('user' => $user, 'isDraft' => false)));
                if (sizeof($solved) > 0)
                    $qb = $qb->andWhere('l.id in (:solved)')->setParameter('solved', $solved);//->setParameter('usr',$user);
                else
                    $qb = $qb->andWhere('1 = 0');
            } else if ($sid == 'inprogress') { //in progress:
                $inProg = $inProg ? $inProg : $this->nb2LMap($nbRep->findBy(array('user' => $user, 'isDraft' => true)));
                if (sizeof($inProg) > 0)
                    $qb = $qb->andWhere('l.id in (:inprog)')->setParameter('inprog', $inProg);
                else
                    $qb = $qb->andWhere('1 = 0');
            } else if ($sid == 'new') { //new:
                $solved = $solved ? $solved : $this->nb2LMap($nbRep->findBy(array('user' => $user, 'isDraft' => false)));
                $inProg = $inProg ? $inProg : $this->nb2LMap($nbRep->findBy(array('user' => $user, 'isDraft' => true)));
                $totalSeen = array_merge($inProg, $solved);
                if (sizeof($totalSeen) > 0) {
                    $qb = $qb->andWhere('l.id not in (:newLes)')->setParameter('newLes', $totalSeen);
                } else {
                    $qb = $qb->andWhere('1 = 1');
                }
            } else if (preg_match('/[0-9]+/', $sid)) { //stream id
                $strIds = $this->nb2LMap($lsRep->findBy(array('stream' => $streamRep->find($sid))));//todo:this works luckily
                $qb = $qb->andWhere('l.id in :str' + $sid)->setParameter('str' + $sid, $strIds);
            } else
                continue;
        }
//        echo $qb->getQuery()->getSQL();
        $lids = $qb->getQuery()->getResult();
//        print_r($lids);
        //check if user can view each problem..
        $result = [];
        foreach ($lids as $lid => $ob) {
            if ($this->canLView($lesRep->find($lid), $user)) {
                $result[] = $lid;
            }
        }
        return $result;
    }

    public function sol2PMap($solArr)
    {
        return array_map(function ($s) {
            return $s->getProblem()->getId();
        }, $solArr);
    }

    public function applyPFilter($fid, $user)
    {
        $probRep = $this->dbh->getRep('Problem');
        $psRep = $this->dbh->getRep('PSMap');
        $streamRep = $this->dbh->getRep('Stream');
        $solRep = $this->dbh->getRep('Solution');

        //determine filter types..
        $sids = explode('&', $fid);

        $qb = $this->dbh->getQB();
        $qb = $qb->select('p.id')->from('MagnetsMathgymBundle:Problem', 'p', 'p.id');

        //separately, obtain solution stats..
        $inProg = null;
        $solved = null;

        foreach ($sids as $sid) { //AND of all components
            //supported filter components:
            if ($sid == 'mydesign') { //designed by user:
                $qb = $qb->andWhere('p.author = :usr')->setParameter('usr', $user);
            } else if ($sid == 'solved') { //already solved:
                $solved = $solved ? $solved : $this->sol2PMap($solRep->findBy(array('user' => $user, 'isDraft' => false)));
                $qb = $qb->andWhere('p.id in (:solved)')->setParameter('solved', $solved);//->setParameter('usr',$user);
            } else if ($sid == 'inprogress') { //in progress:
                $inProg = $inProg ? $inProg : $this->sol2PMap($solRep->findBy(array('user' => $user, 'isDraft' => true)));
                $qb = $qb->andWhere('p.id in (:inprog)')->setParameter('inprog', $inProg);
            } else if ($sid == 'new') { //new:
                $inProg = $inProg ? $inProg : $this->sol2PMap($solRep->findBy(array('user' => $user, 'isDraft' => true)));
                $solved = $solved ? $solved : $this->sol2PMap($solRep->findBy(array('user' => $user, 'isDraft' => false)));
                $qb = $qb->andWhere('p.id not in (:newProb)')->setParameter('newProb', array_merge($inProg, $solved));
            } else if (preg_match('/[0-9]+/', $sid)) { //stream id
                $strIds = $this->sol2PMap($psRep->findBy(array('stream' => $streamRep->find($sid))));
                $qb = $qb->andWhere('p.id in :str' + $sid)->setParameter('str' + $sid, $strIds);
            } else
                continue;
            //    $pids = array_intersect($pids, $sublist);
        }
//        echo $qb->getQuery()->getSQL();
        $pids = $qb->getQuery()->getResult();
        //check if user can view each problem..
        //      echo json_encode($pids);
        //     exit();
        $result = [];
        foreach ($pids as $pid => $ob) {
            if ($this->canPView($probRep->find($pid), $user)) {
                $result[] = $pid;
            }
        }
        return $result;
    }

    public function getLFiltered($fid, $user)
    {
        $lids = $this->applyLFilter($fid, $user);
        $statsWrapper = $this->getLStats($lids, $user);
        return $statsWrapper;
    }

    public function getPFiltered($fid, $user)
    {
        $pids = $this->applyPFilter($fid, $user);
        $statsWrapper = $this->getPStats($pids, $user);
        return $statsWrapper;
    }

    public function setPFilters($fid, $problem)
    {
        if (!preg_match('/[0-9]+/', $fid))
            return;

        $psRep = $this->dbh->getRep('PSMap');
        $streamRep = $this->dbh->getRep('Stream');

        $sids = explode('&', $fid);
//push problem to exactly these streams.. and remove from all others..
        foreach ($streamRep->findAll() as $stream) {
            if (in_array($stream->getId(), $sids)) {
                $psRep->addmap($problem, $stream);
            } else {
                $psRep->delmap($problem, $stream);
            }
        }
    }

    public function appendFilter($fid, $strId)
    {
        return $fid . "&" . $strId;
    }

    public function canPView($problem, $user)
    {
        $streamRep = $this->dbh->getRep('Stream');
        $psRep = $this->dbh->getRep('PSMap');
        $userRep = $this->dbh->getRep('User');

        $publicStream = $streamRep->findOneBy(array('name' => 'Public'));
        $rootUser = $userRep->find(1); //TODO: this is a hack!
        //
        return ($problem->getAuthor() == $user) || $this->ach->isGranted('ROLE_ADMIN') || $psRep->isMemberOf($problem, $publicStream) || ($problem->getAuthor() == $rootUser);
    }

    public function canPEdit($problem, $user)
    {
        return ($problem->getAuthor() == $user) || $this->ach->isGranted('ROLE_ADMIN');
    }

    public function canLView($lesson, $user)
    {
        $streamRep = $this->dbh->getRep('Stream');
        $lsRep = $this->dbh->getRep('LSMap');
        $userRep = $this->dbh->getRep('User');

        $publicStream = $streamRep->findOneBy(array('name' => 'Public'));
        $rootUser = $userRep->find(1); //TODO: this is a hack!
        //
        return ($lesson->getAuthor() == $user) || $this->ach->isGranted('ROLE_ADMIN') || $lsRep->isMemberOf($lesson, $publicStream) || ($lesson->getAuthor() == $rootUser);
    }

    public function canLEdit($lesson, $user)
    {
        return ($lesson->getAuthor() == $user) || $this->ach->isGranted('ROLE_ADMIN');
    }

}
