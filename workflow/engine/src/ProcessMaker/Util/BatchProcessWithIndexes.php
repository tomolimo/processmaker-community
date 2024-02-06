<?php

namespace ProcessMaker\Util;

class BatchProcessWithIndexes
{
    /**
     * Start of the query.
     * @var int
     */
    private $start = 0;

    /**
     * Limit of the query.
     * @var int
     */
    private $limit = 1000;

    /**
     * Total size of the query.
     * @var int
     */
    private $size = 0;

    /**
     * Constructor of the class.
     * @param int $size
     */
    public function __construct(int $size)
    {
        $this->size = $size;
    }

    /**
     * Set custom limit of the query.
     * @param int $limit
     * @return self
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Batch process returning the index for query.
     * @param callable $callback
     * @return void
     */
    public function process(callable $callback): void
    {
        for ($batch = 1; $this->start < $this->size; $batch++) {
            $callback($this->start, $this->limit);
            $this->start = $batch * $this->limit;
        }
    }
}
