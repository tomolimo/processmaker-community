<?php

namespace ProcessMaker\BusinessModel\Files;

use ProcessMaker\Util\DateTime;

class Cron
{
    /**
     * Cron file log path.
     * 
     * @var string 
     */
    private $filepath = '';

    /**
     * Class constructor.
     */
    function __construct()
    {
        $this->setFilepath(PATH_DATA . 'log' . PATH_SEP . 'cron.log');
    }

    /**
     * Set the cron file log path.
     * 
     * @param string $filepath
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;
    }

    /**
     * Get data from file.
     * 
     * @param array $filter
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function getData($filter, $start = 0, $limit = 20)
    {
        if (!file_exists($this->filepath)) {
            return [0, []];
        }
        $result = [];
        $count = 0;
        $array = file($this->filepath);
        foreach ($array as $line) {
            if (empty($line)) {
                continue;
            }
            $row = $this->getRow($line, $filter);
            if ($row === null) {
                continue;
            }
            $count = $count + 1;
            if ($start < $count && count($result) < $limit) {
                $row['DATE'] = DateTime::convertUtcToTimeZone($row['DATE']);
                $result[] = $row;
            }
        }
        return [$count, $result];
    }

    /**
     * Get registry from string line.
     * 
     * @param string $line
     * @param array $filter
     * @return array
     */
    public function getRow($line, $filter)
    {
        $row = explode('|', $line);
        $date = '';
        $workspace = '';
        $action = '';
        $status = '';
        $description = trim($row[0]);

        if (!empty($row)) {
            $date = isset($row[0]) ? trim($row[0]) : '';
            $workspace = isset($row[1]) ? trim($row[1]) : '';
            $action = isset($row[2]) ? trim($row[2]) : '';
            $status = isset($row[3]) ? trim($row[3]) : '';
            $description = isset($row[4]) ? trim($row[4]) : '';
        }


        $isValid = true;
        if ($filter['workspace'] != 'ALL' && $workspace != $filter['workspace']) {
            $isValid = false;
        }
        if ($filter['status'] != 'ALL') {
            switch ($filter['status']) {
                case 'COMPLETED':
                    if ($status != 'action') {
                        $isValid = false;
                    }
                    break;
                case 'FAILED':
                    if ($status == 'action') {
                        $isValid = false;
                    }
                    break;
            }
        }

        $mktDate = !empty($date) ? $this->mktimeDate($date) : 0;
        if (!empty($filter['dateFrom']) && $mktDate > 0) {
            if (!($this->mktimeDate($filter['dateFrom']) <= $mktDate)) {
                $isValid = false;
            }
        }
        if (!empty($filter['dateTo']) && $mktDate > 0) {
            if (!($mktDate <= $this->mktimeDate($filter['dateTo'] . ' 23:59:59'))) {
                $isValid = false;
            }
        }

        if ($isValid) {
            return [
                'DATE' => $date,
                'ACTION' => $action,
                'STATUS' => $status,
                'DESCRIPTION' => $description
            ];
        }
        return null;
    }

    /**
     * Create a timestamp from a string value.
     * 
     * @param string $date
     * @return int|false
     */
    public function mktimeDate($date)
    {
        $array = getdate(strtotime($date));
        $mktime = mktime($array['hours'], $array['minutes'], $array['seconds'], $array['mon'], $array['mday'], $array['year']);
        return $mktime;
    }
}
