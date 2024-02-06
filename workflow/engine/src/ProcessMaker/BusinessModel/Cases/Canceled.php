<?php

namespace ProcessMaker\BusinessModel\Cases;

use ProcessMaker\Model\Delegation;

class Canceled extends AbstractCases
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
        // todo, the list for canceled cases was not defined
    }

    /**
     * Get the data of canceled cases
     *
     * @return array
     */
    public function getData()
    {
        $query = Delegation::query()->select($this->getColumnsView());
        $this->filters($query);
        // todo, the list for canceled cases was not defined
        return [];
    }

    /**
     * Get the number of canceled cases
     *
     * @return int
     */
    public function getCounter()
    {
        // Get base query
        $query = Delegation::query()->select();
        // Join with application
        $query->joinApplication();
        // Scope that sets the queries for Participated
        $query->participated($this->getUserId());
        // Scope that search for the CANCELED
        $query->caseCanceled();
        // Scope to set the last thread
        $query->lastThread();
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
