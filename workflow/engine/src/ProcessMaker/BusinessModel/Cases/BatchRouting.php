<?php

namespace ProcessMaker\BusinessModel\Cases;

use ProcessMaker\Model\Consolidated;
use ProcessMaker\Model\Delegation;

class BatchRouting extends AbstractCases
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
        // todo, the list for consolidated cases was not defined for the new HOME
    }

    /**
     * Get the data of consolidated cases
     *
     * @return array
     */
    public function getData()
    {
        $query = Delegation::query()->select($this->getColumnsView());
        $this->filters($query);
        // todo, the list for consolidated cases was not defined for the new HOME
        return [];
    }

    /**
     * Get the number of consolidated cases
     *
     * @return int
     */
    public function getCounter()
    {
        $query = Consolidated::query()->select();
        // Scope get the pending consolidated task
        $query->joinPendingCases();
        // Get only active
        $query->active();
        // Return the number of rows
        return $query->count(['APP_DELEGATION.APP_NUMBER']);
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