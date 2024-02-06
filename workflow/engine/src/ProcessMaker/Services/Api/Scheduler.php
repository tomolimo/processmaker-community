<?php
namespace ProcessMaker\Services\Api;

use Exception;
use Luracast\Restler\RestException;
use ProcessMaker\BusinessModel\TaskSchedulerBM;
use ProcessMaker\Services\Api;

/**
 * TaskScheduler Controller
 *
 * @protected
 */
class Scheduler extends Api
{
    /**
     * Returns the records of SchedulerTask by category
     * @url GET 
     *
     * @param string $category
     * 
     * @return mixed
     * @throws RestException
     */
    public function doGet($category = null) {
        try {
            return TaskSchedulerBM::getSchedule($category);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Receive the options sent from Scheduler UI
     * @url POST 
     * @status 200
     *
     * @param array $request_data
     *
     * @return array
     * @throws RestException
     *
     */
    public function doPost(array $request) {
        try {
            return TaskSchedulerBM::saveSchedule($request);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }
}
