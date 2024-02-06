<?php

namespace ProcessMaker\Util;

class PhpShorthandByte
{
    private $units;
    private $terminal;

    /**
     * Constructor.
     * Supported format php directives:
     * [number]G
     * [number]K
     * [number]M
     */
    function __construct()
    {
        $this->units = ['K', 'M', 'G'];
        $this->terminal = "bytes";
    }

    /**
     * Convert value string to bytes, for directives php.ini
     * 
     * @param string $value
     * @return integer
     * 
     * @see ProcessMaker\BusinessModel\InputDocument->getMaxFileSize()
     * @link http://php.net/manual/en/faq.using.php#faq.using.shorthandbytes
     */
    public function valueToBytes($value)
    {
        foreach ($this->units as $i => $unit) {
            $number = $this->getNumberValue($value, $unit);
            if ($number !== null) {
                $result = $number * (1024 ** ($i + 1));
                return $result;
            }
        }
        return intval($value);
    }

    /**
     * Get number value and validate expresion.
     * Valid expresion is: [number][unit]
     * 
     * @param string $string
     * @param string $unit
     * @return integer|null
     * 
     * @see ProcessMaker\Util\PhpShorthandByte->valueToBytes()
     * @link http://php.net/manual/en/faq.using.php#faq.using.shorthandbytes
     */
    public function getNumberValue($string, $unit)
    {
        $string = preg_replace('/\s+/', '', $string);
        $isCorrect = preg_match("/\d+{$unit}/", $string);
        if ($isCorrect === 1) {
            $result = rtrim($string, $unit);
            return intval($result);
        }
        return null;
    }

    /**
     * Get format bytes.
     * 
     * @param string $value
     * @return string
     */
    public function getFormatBytes($value)
    {
        foreach ($this->units as $i => $unit) {
            $number = $this->getNumberValue($value, $unit);
            if ($number !== null) {
                return $number . " " . $unit . $this->terminal;
            }
        }
        return $value;
    }
}
