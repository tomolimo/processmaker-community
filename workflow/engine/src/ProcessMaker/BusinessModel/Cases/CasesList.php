<?php

namespace ProcessMaker\BusinessModel\Cases;

use ProcessMaker\BusinessModel\Cases\Canceled;
use ProcessMaker\BusinessModel\Cases\BatchRouting;
use ProcessMaker\BusinessModel\Cases\Draft;
use ProcessMaker\BusinessModel\Cases\Inbox;
use ProcessMaker\BusinessModel\Cases\Participated;
use ProcessMaker\BusinessModel\Cases\Paused;
use ProcessMaker\BusinessModel\Cases\Unassigned;
use ProcessMaker\Model\User;

class CasesList
{
    /**
     * @var array
     */
    private $mapList;
    private $batchRouting;
    private $canceled;
    private $completed;
    private $draft;
    private $inbox;
    private $participated;
    private $paused;
    private $unassigned;

    /**
     * Counter constructor.
     */
    public function __construct()
    {
        $this->mapList = [
            'canceled' => 'CASES_CANCELLED',
            'completed' => 'CASES_COMPLETED',
            'draft' => 'CASES_DRAFT',
            'inbox' => 'CASES_INBOX',
            'participated' => 'CASES_SENT',
            'paused' => 'CASES_PAUSED',
            'unassigned' => 'CASES_SELFSERVICE',
        ];

        $this->canceled = new Canceled();
        $this->completed = new Completed();
        $this->draft = new Draft();
        $this->inbox = new Inbox();
        $this->participated = new Participated();
        $this->paused = new Paused();
        $this->unassigned = new Unassigned();
    }

    /**
     * Count cases by user
     *
     * @param string $usrUid
     * @param bool $format
     *
     * @return array
     */
    public function getAllCounters(string $usrUid, bool $format = false)
    {
        // Get the usrId key
        $usrId = User::getId($usrUid);
        // Get the classes
        $list = $this->mapList;
        $response = [];
        foreach ($list as $listObject => $item) {
            $this->$listObject->setUserUid($usrUid);
            $this->$listObject->setUserId($usrId);
            $total = $this->$listObject->getCounter($usrUid);
            if ($format) {
                array_push($response, (['count' => $total, 'item' => $item]));
            } else {
                $response[$item] = $total;
            }
        }

        return $response;
    }

    /**
     * Count if the user has at least one case in the list
     *
     * @param string $usrUid
     *
     * @return array
     */
    public function atLeastOne(string $usrUid)
    {
        // Get the usrId key
        $usrId = User::getId($usrUid);
        // Get the classes
        $list = $this->mapList;
        $response = [];
        foreach ($list as $listObject => $item) {
            $this->$listObject->setUserUid($usrUid);
            $this->$listObject->setUserId($usrId);
            $atLeastOne = $this->$listObject->atLeastOne($usrUid);
            $response[] = ['item' => $item, 'highlight' => $atLeastOne];
        }

        return $response;
    }
}
