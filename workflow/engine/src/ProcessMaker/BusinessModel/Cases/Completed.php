<?php

namespace ProcessMaker\BusinessModel\Cases;

use ProcessMaker\Model\Delegation;

class Completed extends AbstractCases
{
    // Columns to see in the cases list
    public $columnsView = [
        // Columns view in the cases list
        'APP_DELEGATION.APP_NUMBER', // Case #
    ];

    /**
     * Get the columns related to the cases list
     * @return array
     */
    public function getColumnsView()
    {
        return $this->columnsView;
    }

    /**
     * Scope filters
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filters($query)
    {
        // todo, the list for completed cases was defined in participated
    }

    /**
     * Get the data of completed cases
     *
     * @return array
     */
    public function getData()
    {
        $query = Delegation::query()->select($this->getColumnsView());
        $this->filters($query);
        // todo, the list for completed cases was defined in participated
        return [];
    }

    /**
     * Get the number of completed cases
     *
     * @return int
     */
    public function getCounter()
    {
        // For started by me
        $participated = new Participated();
        $participated->setParticipatedStatus('COMPLETED');
        $participated->setUserUid($this->getUserUid());
        $participated->setUserId($this->getUserId());

        return $participated->getCounter();
    }

    /**
     * Count if the user has at least one case in the list
     *
     * @return bool
     */
    public function atLeastOne()
    {
        // This class does not require this value
        return false;
    }
}
