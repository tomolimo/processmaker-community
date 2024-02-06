<?php

namespace ProcessMaker\Util;

use Bootstrap;
use G;
use Illuminate\Support\Facades\Log;

/**
 * Constructs the name of the variable starting from a string representing the
 * depth of the array.
 */
class ParseSoapVariableName
{
    /**
     * Constructs the name of the variable starting from a string representing
     * the depth of the array.
     *
     * @param array  $field
     * @param string $name
     * @param object $value
     * @return void
     */
    public function buildVariableName(&$field, $name, $value)
    {
        if (!$this->isValidVariableName($name)) {
            $message = 'NewCase';
            $context = [
                'action' => 'soap2',
                'exception' => 'Invalid param: '.G::json_encode($name)
            ];
            Log::channel(':soap2')->error($message, Bootstrap::context($context));
            return;
        }

        $brackets = $this->searchBrackets($name);
        if (empty($brackets)) {
            $field[$name] = $value;
        } else {
            $current = &$field;
            foreach ($brackets as $extension) {
                if (!isset($current[$extension])) {
                    $current[$extension] = [];
                }
                $current = &$current[$extension];
            }
            $current = $value;
        }
    }

    /**
     * Analysis of string representing the depth of the array, represented by a
     * valid index name and brackets as separators.
     *
     * @param type $string
     *
     * @return array
     */
    private function searchBrackets($string)
    {
        $stringClean = str_replace(' ', '', $string);
        $explode = explode('][', $stringClean);

        return $explode;
    }

    /**
     * Verify if the index name of the array is valid.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isValidVariableName($name)
    {
        if (is_string($name) === true) {
            if (preg_match("/^[0-9a-zA-Z\_\[\]]+$/", $name)) {
                return true;
            }
        }

        return false;
    }
}

