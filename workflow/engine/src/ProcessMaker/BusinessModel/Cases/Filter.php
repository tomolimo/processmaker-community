<?php

namespace ProcessMaker\BusinessModel\Cases;

use G;
use ProcessMaker\Model\Configuration;
use stdClass;

class Filter
{
    const ADVANCED_SEARCH_FILTER_KEY = 'advanced-search-filter';

    /**
     * Get filters of the advanced search for the current user
     *
     * @param string $userUid
     *
     * @return array
     */
    public static function getByUser($userUid)
    {
        // Initialize variables
        $filters = [];

        // Build query
        $query = Configuration::query()->select(['CFG_VALUE']);
        $query->where('CFG_UID', '=', self::ADVANCED_SEARCH_FILTER_KEY);
        $query->where('USR_UID', '=', $userUid);

        // Get results
        $records = $query->get();

        // Transform the serialized string to JSON object
        $records->each(function ($record) use (&$filters) {
            $filters[] = json_decode($record->CFG_VALUE);
        });

        // Return filters
        return $filters;
    }

    /**
     * Get a specific filter of the advanced search for the current user
     *
     * @param string $userUid
     * @param string $filterUid
     *
     * @return object
     */
    public static function getByUid($userUid, $filterUid)
    {
        // Initialize variables
        $filter = null;

        // Build query
        $query = Configuration::query()->select(['CFG_VALUE']);
        $query->where('CFG_UID', '=', self::ADVANCED_SEARCH_FILTER_KEY);
        $query->where('USR_UID', '=', $userUid);
        $query->where('OBJ_UID', '=', $filterUid);

        // Get result
        $record = $query->first();

        if (!is_null($record)) {
            $filter = json_decode($record->CFG_VALUE);
        }

        // Return filter
        return $filter;
    }

    /**
     * Save a new filter of the advanced search for the current user
     *
     * @param string $userUid
     * @param string $name
     * @param object $filters
     *
     * @return object
     */
    public static function create($userUid, $name, $filters)
    {
        // Generate a new unique Uid
        $filterUid = G::generateUniqueID();

        // Build object to serialize and save
        $filter = new stdClass();
        $filter->id = $filterUid;
        $filter->name = $name;
        $filter->filters = $filters;

        // Save new filter
        $configuration = new Configuration();
        $configuration->CFG_UID = self::ADVANCED_SEARCH_FILTER_KEY;
        $configuration->OBJ_UID = $filterUid;
        $configuration->CFG_VALUE = json_encode($filter);
        $configuration->USR_UID = $userUid;
        $configuration->save();

        // Return the new filter
        return $filter;
    }

    /**
     * Update a filter of the advanced search for the current user
     *
     * @param string $userUid
     * @param string $filterUid
     * @param string $name
     * @param object $filters
     */
    public static function update($userUid, $filterUid, $name, $filters)
    {
        // Build object to serialize and save
        $filter = new stdClass();
        $filter->id = $filterUid;
        $filter->name = $name;
        $filter->filters = $filters;

        // Update filter
        Configuration::query()->where('CFG_UID', '=', self::ADVANCED_SEARCH_FILTER_KEY)
            ->where('OBJ_UID', '=', $filterUid)
            ->where('USR_UID', '=', $userUid)
            ->update(['CFG_VALUE' => json_encode($filter)]);
    }

    /**
     * Delete a specific filter by filter Uid
     *
     * @param string $filterUid
     */
    public static function delete($filterUid)
    {
        // Build the query
        $query = Configuration::query()->where('CFG_UID', '=', self::ADVANCED_SEARCH_FILTER_KEY);
        $query->where('OBJ_UID', '=', $filterUid);

        // Delete filter
        $query->delete();
    }
}
