<?php

namespace ProcessMaker\Core;

/**
 * This class run a command in shell and stores the pointer to him
 */
class RunProcess
{
    // Class constants
    const TERMINATED = 'terminated';
    const RUNNING = 'running';
    const NOT_RUNNING = 'not_running';
    const ERROR = 'error';

    // This constant can be overrides in the child class according to the command response, always should be have a value
    const EXPECTED_ANSWER = '1';

    // Class properties
    private $resource;
    private $command;
    private $rawAnswer;
    private $status;
    private $exitCode;
    private $pipes;
    private $descriptors = [
        ['pipe', 'r'],
        ['pipe', 'w'],
        ['pipe', 'w']
    ];

    /**
     * Class constructor
     *
     * @param string $command
     */
    public function __construct($command)
    {
        $this->command = $command;
    }

    /**
     * Class destructor, the resource created should be closed
     */
    public function __destruct()
    {
        if (is_resource($this->resource)) {
            proc_close($this->resource);
        }
    }

    /**
     * Get the command
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Get the raw response
     *
     * @return string|null
     */
    public function getRawAnswer()
    {
        return $this->rawAnswer;
    }

    /**
     * Get the status
     *
     * @return string
     */
    public function getStatus()
    {
        // If already exist a status return this value
        if ($this->status !== null) {
            return $this->status;
        }

        // If doesn't exists a resource the process is not running
        if (!is_resource($this->resource)) {
            return self::NOT_RUNNING;
        }

        // If the process is running return this value
        if ($this->isRunning()) {
            return self::RUNNING;
        }

        // If the process is not running, parse the response to determine the status
        $this->rawAnswer = stream_get_contents($this->pipes[1]);
        $this->status = $this->parseAnswer();

        return $this->status;
    }

    /**
     * Get the exit code
     *
     * @return string|null
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }

    /**
     * Run the command
     */
    public function run()
    {
        $this->resource = proc_open($this->command, $this->descriptors, $this->pipes);
    }

    /**
     * Process is running?
     *
     * @return bool
     */
    public function isRunning()
    {
        // Get the process status
        $status = proc_get_status($this->resource);

        // If process is not running get the exit code
        if ($status['running'] === false) {
            $this->exitCode = $status['exitcode'];
        }

        return $status['running'];
    }

    /**
     * Process the raw response and compare with the expected answer in order to determine the status
     *
     * @return string
     */
    public function parseAnswer()
    {
        return $this->rawAnswer === self::EXPECTED_ANSWER ? self::TERMINATED : self::ERROR;
    }
}
