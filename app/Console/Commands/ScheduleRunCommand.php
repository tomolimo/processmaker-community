<?php

namespace App\Console\Commands;

use Bootstrap;
use Illuminate\Support\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\ScheduleRunCommand as BaseCommand;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Maveriks\WebApplication;
use ProcessMaker\Model\TaskScheduler;

class ScheduleRunCommand extends BaseCommand
{
    use AddParametersTrait;

    /**
     * Create a new command instance.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->startedAt = Carbon::now();
        $this->signature = "schedule:run";
        $this->signature .= "
        {--workspace=workflow : Workspace to use.}
        {--user=apache : Operating system's user who executes the crons.}        
        {--processmakerPath=./ : ProcessMaker path.}
        ";
        $this->description .= ' (ProcessMaker has extended this command)';
        parent::__construct();
    }

    /**
     * Execute the console command.
     * 
     * @param Schedule $schedule
     * @param Dispatcher $dispatcher
     * @param ExceptionHandler $handler
     * @return void
     */
    public function handle(Schedule $schedule, Dispatcher $dispatcher, ExceptionHandler $handler)
    {
        $workspace = $this->option('workspace');
        $user = $this->option('user');
        if (!empty($workspace)) {
            $webApplication = new WebApplication();
            $webApplication->setRootDir($this->option('processmakerPath'));
            $webApplication->loadEnvironment($workspace, false);
        }
        TaskScheduler::all()->each(function ($p) use ($schedule, $user) {
            $win = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            if ($p->enable == 1) {
                $starting = isset($p->startingTime) ? $p->startingTime : "0:00";
                $ending = isset($p->startingTime) ? $p->endingTime : "23:59";
                $timezone = isset($p->timezone) && $p->timezone != "" ? $p->timezone : date_default_timezone_get();
                $body = $p->body;
                if (!$win) {
                    $body = str_replace(" -c", " " . $user . " -c", $p->body);
                }

                //for init date and finish date parameters
                if (strpos($body, "report_by_user") !== false || strpos($body, "report_by_process") !== false) {
                    //remove if the command is old and contains an incorrect definition of the date
                    $body = preg_replace("/\s\+init-date\"[0-9\-\s:]+\"/", "", $body);
                    $body = preg_replace("/\s\+finish-date\"[0-9\-\s:]+\"/", "", $body);

                    //the start date must be one month back from the current date.
                    $currentDate = date("Y-m-d H:i:s");
                    $oneMonthAgo = $currentDate . " -1 month";
                    $timestamp = strtotime($oneMonthAgo);
                    $oneMonthAgo = date("Y-m-d H:i:s", $timestamp);

                    $body = str_replace("report_by_user", "report_by_user +init-date'{$oneMonthAgo}' +finish-date'{$currentDate}'", $body);
                    $body = str_replace("report_by_process", "report_by_process +init-date'{$oneMonthAgo}' +finish-date'{$currentDate}'", $body);
                }

                $schedule->exec($body)->cron($p->expression)->between($starting, $ending)->timezone($timezone)->when(function () use ($p) {
                    $now = Carbon::now();
                    $result = false;
                    $datework = Carbon::createFromFormat('Y-m-d H:i:s', $p->last_update);
                    if (isset($p->everyOn)) {
                        switch ($p->interval) {
                            case "day":
                                $interval = $now->diffInDays($datework);
                                $result = ($interval !== 0 && ($interval % intval($p->everyOn)) == 0);
                                break;
                            case "week":
                                $diff = $now->diffInDays($datework);
                                if ($diff % (intval($p->everyOn) * 7) < 7 && $diff % (intval($p->everyOn) * 7) >= 0) {
                                    $result = true;
                                } else {
                                    $result = false;
                                }
                                break;
                            case "month":
                                $interval = $now->diffInMonths($datework);
                                if ($interval % intval($p->everyOn) == 0) {
                                    $result = true;
                                } else {
                                    $result = false;
                                }
                                break;
                            case "year":
                                $interval = $now->diffInYears($datework);
                                if ($interval % intval($p->everyOn) == 0) {
                                    $result = true;
                                } else {
                                    $result = false;
                                }
                                break;
                        }
                        return $result;
                    }
                    return true;
                });
                $config = Bootstrap::getSystemConfiguration();
                if (intval($config['on_one_server_enable']) === 1) {
                    $schedule->onOneServer();
                }
            }
        });
        parent::handle($schedule, $dispatcher, $handler);
    }
}
