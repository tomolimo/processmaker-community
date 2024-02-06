<?php

namespace ProcessMaker\Validation;

use Exception;
use G;
use PhpMyAdmin\SqlParser\Parser;

class SqlBlacklist extends Parser
{

    /**
     * Define the statements to block, this is case sensitive.
     * @var array
     */
    private $statementsToBeBlocked = [
        'SELECT',
        'EXECUTE',
        'EXEC',
        'SHOW',
        'DESCRIBE',
        'EXPLAIN',
        'BEGIN',
        'INSERT',
        'UPDATE',
        'DELETE',
        'REPLACE'
    ];

    /**
     * Constructor of class.
     * @param string $list
     * @param boolean $strict
     */
    public function __construct($list = null, $strict = false)
    {
        parent::__construct($list, $strict);
    }

    /**
     * Get information about the statements permitted and tables that can be modified.
     * @return array
     */
    public function getConfigValues(): array
    {
        $tables = [];
        $statements = [];
        $pmtables = [];

        $path = PATH_CONFIG . 'system-tables.ini';
        if (file_exists($path)) {
            $values = @parse_ini_file($path);

            $string = isset($values['tables']) ? $values['tables'] : '';
            $tables = explode('|', $string);
            $tables = array_filter($tables, function ($v) {
                return !empty($v);
            });
        }

        $path = PATH_CONFIG . 'execute-query-blacklist.ini';
        if (file_exists($path)) {
            $values = @parse_ini_file($path);

            $string = isset($values['pmtables']) ? $values['pmtables'] : '';
            $pmtables = explode('|', $string);
            $pmtables = array_filter($pmtables, function ($v) {
                return !empty($v);
            });

            $string = isset($values['queries']) ? $values['queries'] : '';
            $string = strtoupper($string);
            $statements = explode('|', $string);
            //get only statements allowed for lock
            $statements = array_filter($statements, function ($v) {
                $toUpper = strtoupper($v);
                return !empty($v) && in_array($toUpper, $this->statementsToBeBlocked);
            });
        }

        return [
            'tables' => $tables,
            'statements' => $statements,
            'pmtables' => $pmtables
        ];
    }

    /**
     * Parse a sql string and check the blacklist, an exception is thrown if it contains a restricted item.
     * @return void
     * @throws Exception
     */
    public function validate(): void
    {
        $config = $this->getConfigValues();

        //verify statements
        $notExecuteQuery = false;
        foreach ($this->statements as $statement) {
            $signed = get_class($statement);
            foreach (Parser::$STATEMENT_PARSERS as $key => $value) {
                if ($signed === $value && in_array(strtoupper($key), $config['statements'])) {
                    //SHOW statement is a special case, it does not require a table name
                    if (strtoupper($key) === 'SHOW') {
                        throw new Exception(G::loadTranslation('ID_INVALID_QUERY'));
                    }
                    $notExecuteQuery = true;
                    break;
                }
            }
        }

        //verify tables
        //tokens are formed multidimensionally, it is necessary to recursively traverse the multidimensional object.
        $fn = function ($object, $callback) use (&$fn) {
            foreach ($object as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $fn($value, $callback);
                }
                if ($key === 'table' && is_string($value)) {
                    $callback($value);
                }
                if ($key === 'token' && is_string($value)) {
                    $callback($value);
                }
            }
        };

        //verify system tables
        $tables = $config['tables'];
        $fn($this->statements, function ($table) use ($tables, $notExecuteQuery) {
            if (in_array($table, $tables) && $notExecuteQuery) {
                throw new Exception(G::loadTranslation('ID_NOT_EXECUTE_QUERY', [$table]));
            }
        });

        //verify pmtables
        $pmtables = $config['pmtables'];
        $fn($this->statements, function ($table) use ($pmtables, $notExecuteQuery) {
            if (in_array($table, $pmtables) && $notExecuteQuery) {
                throw new Exception(G::loadTranslation('ID_NOT_EXECUTE_QUERY', [$table]));
            }
        });
    }
}
