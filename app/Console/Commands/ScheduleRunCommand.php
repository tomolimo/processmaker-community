<?php
namespace App\Console\Commands;
use Illuminate\Support\Carbon;
use Illuminate\Console\Scheduling\ScheduleRunCommand as BaseCommand;
use Maveriks\WebApplication;
use ProcessMaker\Model\TaskScheduler;
class ScheduleRunCommand extends BaseCommand
{
    use AddParametersTrait;
    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function __construct(\Illuminate\Console\Scheduling\Schedule $schedule)
    {
        $this->startedAt = Carbon::now();
        $this->signature = "schedule:run";
        $this->signature .= "
        {--workspace=workflow : Workspace to use.}
        {--user=apache : Operating system's user who executes the crons.}        
        {--processmakerPath=./ : ProcessMaker path.}
        ";
        $this->description .= ' (ProcessMaker has extended this command)';
        parent::__construct($schedule);
    }
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $that = $this;
        $workspace = $this->option('workspace');
        $user =  $this->option('user');
        if (!empty($workspace)) {
            $webApplication = new WebApplication();
            $webApplication->setRootDir($this->option('processmakerPath'));
            $webApplication->loadEnvironment($workspace, false);
        }
        TaskScheduler::all()->each(function ($p) use ($that, $user) {
            $win = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            if ($p->enable == 1) {
                $starting = isset($p->startingTime) ? $p->startingTime : "0:00";
                $ending = isset($p->startingTime) ? $p->endingTime : "23:59";
                $timezone = isset($p->timezone) && $p->timezone != "" ? $p->timezone : date_default_timezone_get();
                $body = $p->body;
                if (!$win) {
                    $body = str_replace(" -c"," " . $user . " -c", $p->body);
                }
                $that->schedule->exec($body)->cron($p->expression)->between($starting, $ending)->timezone($timezone)->when(function () use ($p) {
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
            }
        });
        parent::handle();
    }
}