<?php

namespace ProcessMaker\BusinessModel;

use Bootstrap;
use Illuminate\Support\Facades\Log;
use ProcessMaker\Core\System;
use ProcessMaker\Model\TaskScheduler;

class TaskSchedulerBM
{
    public static $services = [
        [
            "title" => "ID_TASK_SCHEDULER_UNPAUSE",
            "enable" => "0",
            "service" => "unpause",
            "category" => "case_actions",
            "file" => "workflow/engine/bin/cron.php",
            "filew" => "workflow\\engine\bin\cron.php",
            "startingTime" => null,
            "endingTime" => null,
            "timezone" => null,
            "everyOn" => "1",
            "interval" => "week",
            "expression" => "0 */1 * * 0,1,2,3,4,5,6",
            "description" => "ID_TASK_SCHEDULER_UNPAUSE_DESC"
        ],
        [
            "title" => "ID_TASK_SCHEDULER_CALCULATE_ELAPSED",
            "enable" => "0",
            "service" => "calculate",
            "category" => "case_actions",
            "file" => "workflow/engine/bin/cron.php",
            "filew" => "workflow\\engine\bin\cron.php",
            "startingTime" => null,
            "endingTime" => null,
            "timezone" => "default",
            "everyOn" => "1",
            "interval" => "week",
            "expression" => "0 0 * * 0,1,2,3,4,5,6",
            "description" => 'ID_TASK_SCHEDULER_CALCULATE_ELAPSED_DESC'
        ],
        [
            "title" => "ID_TASK_SCHEDULER_UNASSIGNED",
            "enable" => "0",
            "service" => "unassigned-case",
            "category" => "case_actions",
            "file" => "workflow/engine/bin/cron.php",
            "filew" => "workflow\\engine\bin\cron.php",
            "startingTime" => null,
            "endingTime" => null,
            "timezone" => null,
            "everyOn" => "1",
            "interval" => "week",
            "expression" => "0 */1 * * 0,1,2,3,4,5,6",
            "description" => 'ID_TASK_SCHEDULER_UNASSIGNED_DESC'
        ],
        [
            "title" => "ID_TASK_SCHEDULER_CLEAN_SELF",
            "enable" => "0",
            "service" => "clean-self-service-tables",
            "category" => "case_actions",
            "file" => "workflow/engine/bin/cron.php",
            "filew" => "workflow\\engine\bin\cron.php",
            "startingTime" => null,
            "endingTime" => null,
            "timezone" => "default",
            "everyOn" => "1",
            "interval" => "week",
            "expression" => "0 0 * * 0,1,2,3,4,5,6",
            "description" => 'ID_TASK_SCHEDULER_CLEAN_SELF_DESC'
        ],
        [
            "title" => "ID_TIMER_EVENT",
            "enable" => "1",
            "service" => "",
            "category" => "case_actions",
            "file" => "workflow/engine/bin/timereventcron.php",
            "filew" => "workflow\\engine\bin\\timereventcron.php",
            "startingTime" => null,
            "endingTime" => null,
            "timezone" => null,
            "everyOn" => "1",
            "interval" => "week",
            "expression" => "*/1 * * * 0,1,2,3,4,5,6",
            "description" => "ID_TIMER_EVENT_DESC"
        ],
        [
            "title" => "ID_CLEAN_WEBENTRIES",
            "enable" => "0",
            "service" => "",
            "category" => "case_actions",
            "file" => "workflow/engine/bin/webentriescron.php",
            "filew" => "workflow\\engine\bin\\webentriescron.php",
            "startingTime" => null,
            "endingTime" => null,
            "timezone" => null,
            "everyOn" => "1",
            "interval" => "week",
            "expression" => "0 20 * * 5",
            "description" => "ID_CLEAN_WEBENTRIES_DESC"
        ],
        [
            "title" => "ID_TASK_SCHEDULER_CASE_EMAILS",
            "enable" => "1",
            "service" => "emails",
            "category" => "emails_notifications",
            "file" => "workflow/engine/bin/cron.php",
            "filew" => "workflow\\engine\bin\cron.php",
            "startingTime" => null,
            "endingTime" => null,
            "timezone" => null,
            "everyOn" => "1",
            "interval" => "week",
            "expression" => "*/5 * * * 0,1,2,3,4,5,6",
            "description" => "ID_TASK_SCHEDULER_CASE_EMAILS_DESC"
        ],
        [
            "title" => "ID_TASK_SCHEDULER_MESSAGE_EVENTS",
            "enable" => "1",
            "service" => "",
            "category" => "emails_notifications",
            "file" => "workflow/engine/bin/messageeventcron.php",
            "filew" => "workflow\\engine\bin\messageeventcron.php",
            "startingTime" => null,
            "endingTime" => null,
            "timezone" => null,
            "everyOn" => "1",
            "interval" => "week",
            "expression" => "*/5 * * * 0,1,2,3,4,5,6",
            "description" => "ID_TASK_SCHEDULER_MESSAGE_EVENTS_DESC"
        ]
    ];

    /**
     * Return the records in Schedule Table by category
     */
    public static function getSchedule($category)
    {
        $tasks = TaskScheduler::all();
        $count = $tasks->count();
        if ($count == 0) {
            TaskSchedulerBM::generateInitialData();
            $tasks = TaskScheduler::all();
        }
        if (is_null($category)) {
            return $tasks;
        } else {
            $tasks = TaskScheduler::where('category', $category)->get();
            foreach ($tasks as $task) {
                $task->default_value = json_decode($task->default_value);
            }
            return $tasks;
        }
    }

    /**
     * Save the record Schedule in Schedule Table
     */
    public static function saveSchedule(array $request)
    {
        $task = TaskScheduler::find($request['id']);
        if (isset($request['enable'])) {
            $task->enable = $request['enable'];
        }
        if (isset($request['expression'])) {
            $task->expression = $request['expression'];
            $task->startingTime = $request['startingTime'];
            $task->endingTime = $request['endingTime'];
            $task->timezone = $request['timezone'];
            $task->everyOn = $request['everyOn'];
            $task->interval = $request['interval'];
        }
        $task->save();
        return $task;
    }

    /**
     * Initial data for Schedule Table, with default values
     */
    public static function generateInitialData()
    {
        foreach (TaskSchedulerBM::$services as $service) {
            self::registerScheduledTask($service);
        }
    }

    /**
     * Register scheduled task.
     * @param array $service
     * @return TaskScheduler
     */
    private static function registerScheduledTask(array $service)
    {
        $task = new TaskScheduler;
        $task->title = $service["title"];
        $task->category = $service["category"];
        $task->description = $service["description"];
        $task->startingTime = $service["startingTime"];
        $task->endingTime = $service["endingTime"];
        $task->body = self::buildBody($service);
        $task->expression = $service["expression"];
        $task->type = "shell";
        $task->system = 1;
        $task->enable = $service["enable"];
        $task->everyOn = $service["everyOn"];
        $task->interval = $service["interval"];
        $task->timezone = $service["timezone"] == "default" ? date_default_timezone_get() : null;
        $task->default_value = json_encode([
            "startingTime" => $service["startingTime"],
            "endingTime" => $service["endingTime"],
            "everyOn" => $service["everyOn"],
            "interval" => $service["interval"],
            "expression" => $service["expression"],
            "timezone" => $task->timezone
        ]);
        $task->save();
        return $task;
    }

    /**
     * Build body parameter.
     * @param array $service
     * @return string
     */
    private static function buildBody(array $service): string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return 'php "' . PATH_TRUNK . $service["filew"] . '" ' . $service["service"] . ' +w' . config("system.workspace") . ' +force +async';
        } else {
            return 'su -s /bin/sh -c "php ' . PATH_TRUNK . $service["file"] . " " . $service["service"] . ' +w' . config("system.workspace") . ' +force +async"';
        }
    }

    /**
     * Check data integrity.
     * @return array
     */
    public static function checkDataIntegrity(): array
    {
        $beforeChanges = TaskScheduler::select()->get();

        //remove missing register
        $titleCondition = [];
        $descriptionCondition = [];
        foreach (self::$services as $service) {
            $titleCondition[] = $service['title'];
            $descriptionCondition[] = $service['description'];
        }
        TaskScheduler::whereNotIn('title', $titleCondition)
            ->whereNotIn('description', $descriptionCondition)
            ->delete();

        //update register or create new register
        foreach (self::$services as $service) {
            $scheduler = TaskScheduler::select()
                ->where('title', '=', $service['title'])
                ->where('description', '=', $service['description'])
                ->first();
            if (is_null($scheduler)) {
                self::registerScheduledTask($service);
            } else {
                $scheduler->body = self::buildBody($service);
                $scheduler->type = 'shell';
                $scheduler->category = $service['category'];
                $scheduler->system = 1;
                $scheduler->update();
            }
        }

        //log changes
        $afterChanges = TaskScheduler::select()->get();
        $result = [
            'beforeChanges' => $beforeChanges,
            'afterChanges' => $afterChanges
        ];
        $message = 'Check SCHEDULER table integrity';
        Log::channel(':taskSchedulerCheckDataIntegrity')->info($message, Bootstrap::context($result));
        return $result;
    }
}
