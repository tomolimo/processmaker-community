<?php

namespace ProcessMaker\Report;

use Exception;
use Illuminate\Support\Facades\DB;

class Reporting
{
    /**
     * Field pathToAppCacheFiles.
     * @var string
     */
    private $pathToAppCacheFiles;

    /**
     * Set pathToAppCacheFiles property.
     * @param string $path
     */
    public function setPathToAppCacheFiles(string $path)
    {
        $this->pathToAppCacheFiles = $path;
    }

    /**
     * This populates the USR_REPORTING table.
     * @param string $dateInit
     * @param string $dateFinish
     * @return void
     * @throws Exception
     */
    public function fillReportByUser(string $dateInit, string $dateFinish): void
    {
        $filenameSql = $this->pathToAppCacheFiles . "triggerFillReportByUser.sql";

        if (!file_exists($filenameSql)) {
            throw new Exception("File {$filenameSql} doesn't exist");
        }

        DB::statement("TRUNCATE TABLE USR_REPORTING");

        $sql = explode(';', file_get_contents($filenameSql));

        foreach ($sql as $key => $val) {
            $val = str_replace('{init_date}', $dateInit, $val);
            $val = str_replace('{finish_date}', $dateFinish, $val);
            DB::statement($val);
        }
    }

    /**
     * This populates the PRO_REPORTING table
     * @param string $dateInit
     * @param string $dateFinish
     * @return void
     * @throws Exception
     */
    public function fillReportByProcess(string $dateInit, string $dateFinish): void
    {
        $filenameSql = $this->pathToAppCacheFiles . "triggerFillReportByProcess.sql";

        if (!file_exists($filenameSql)) {
            throw new Exception("File {$filenameSql} doesn't exist");
        }

        DB::statement("TRUNCATE TABLE PRO_REPORTING");

        $sql = explode(';', file_get_contents($filenameSql));

        foreach ($sql as $key => $val) {
            $val = str_replace('{init_date}', $dateInit, $val);
            $val = str_replace('{finish_date}', $dateFinish, $val);
            DB::statement($val);
        }
    }
}
