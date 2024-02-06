<?php

namespace ProcessMaker\Core;

class ProcOpen
{
    private $command;
    private $resource;
    private $descriptorspec;
    private $pipes;
    private $cwd;

    /**
     * This initializes the descriptors and the command for the open process.
     * @param string $command
     */
    public function __construct(string $command)
    {
        $this->descriptorspec = [
            ['pipe', 'r'],
            ['pipe', 'w'],
            ['pipe', 'w']
        ];
        $this->command = $command;
    }

    /**
     * Gets the resource that represents the process.
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Sets the process execution directory.
     * @param string $cwd
     */
    public function setCwd(string $cwd)
    {
        $this->cwd = $cwd;
    }

    /**
     * Open a background process.
     */
    public function open()
    {
        if (empty($this->cwd)) {
            $this->resource = proc_open($this->command, $this->descriptorspec, $this->pipes);
        } else {
            $this->resource = proc_open($this->command, $this->descriptorspec, $this->pipes, $this->cwd);
        }
    }

    /**
     * Get the content of the process when it is finished.
     * @return string
     */
    public function getContents()
    {
        if (is_resource($this->pipes[1])) {
            return stream_get_contents($this->pipes[1]);
        }
        return "";
    }

    /**
     * Get the process errors when it is finished.
     * @return string
     */
    public function getErrors()
    {
        if (is_resource($this->pipes[2])) {
            return stream_get_contents($this->pipes[2]);
        }
        return "";
    }

    /**
     * Close the resources related to the open process.
     * return void
     */
    public function close()
    {
        if (is_resource($this->resource)) {
            foreach ($this->pipes as $value) {
                fclose($value);
            }
            proc_close($this->resource);
        }
    }

    /**
     * End the process before it ends.
     */
    public function terminate()
    {
        if (is_resource($this->resource)) {
            proc_terminate($this->resource);
        }
    }

    /**
     * Gets the status of the process.
     * @return object
     */
    public function getStatus()
    {
        $status = [
            "command" => $this->command,
            "pid" => null,
            "running" => false,
            "signaled" => false,
            "stopped" => false,
            "exitcode" => -1,
            "termsig" => 0,
            "stopsig" => 0
        ];
        if (is_resource($this->resource)) {
            $status = proc_get_status($this->resource);
        }
        return (object) $status;
    }
}
