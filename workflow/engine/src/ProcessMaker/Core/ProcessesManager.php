<?php

namespace ProcessMaker\Core;

/**
 * Class to manage the processes that runs in the shell
 */
class ProcessesManager
{
    // Class properties
    private $processes;
    private $sleepTime = 1;
    private $terminated = [];
    private $errors = [];

    /**
     * Class constructor
     *
     * @param array $processes
     */
    public function __construct(array $processes)
    {
        $this->processes = $processes;
    }

    /**
     * Get the list of terminated processes
     *
     * @return array
     */
    public function getTerminated()
    {
        return $this->terminated;
    }

    /**
     * Get the list of processes with errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Set the sleep time after each statuses revision
     *
     * @param int $sleepTime
     */
    public function setSleepTime($sleepTime)
    {
        $this->sleepTime = $sleepTime;
    }

    /**
     * Run the processes
     */
    public function run()
    {
        // Start all processes
        foreach ($this->processes as $process) {
            $process->run();
        }

        // Manage the processes
        $this->manage();
    }

    /**
     * Manage all started processes
     */
    private function manage()
    {
        do {
            // Check all remaining processes
            foreach ($this->processes as $index => $process) {
                // If the process has finished, save the info and destroy it
                if ($process->getStatus() === RunProcess::TERMINATED || $process->getStatus() === RunProcess::ERROR) {
                    $processInfo = ['command' => $process->getCommand(), 'rawAnswer' => $process->getRawAnswer()];
                    if ($process->getStatus() === RunProcess::TERMINATED) {
                        // Processes completed successfully
                        $this->terminated[] = $processInfo;
                    } else {
                        // Processes completed with errors
                        $this->errors[] = $processInfo;
                    }

                    // Destroy the process
                    unset($this->processes[$index]);
                }
            }

            // Waiting...
            sleep($this->sleepTime);
        } while (!empty($this->processes));
    }
}
