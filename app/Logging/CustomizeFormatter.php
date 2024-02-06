<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class CustomizeFormatter
{
    private $format = "<%level%> %datetime% %channel% %level_name%: %message% %context%\n";
    private $dateFormat = "M d H:i:s";

    /**
     * Customize the given logger instance.
     *
     * @param \Illuminate\Log\Logger $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter($this->format, $this->dateFormat, true, true));
        }
    }
}
