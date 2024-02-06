<?php

use ProcessMaker\Plugins\PluginRegistry;

spl_autoload_register(function($sClassName) {
    if (!empty(config("system.workspace"))) {
        $sPath = PATH_DB . config("system.workspace") . PATH_SEP . 'classes' . PATH_SEP;
        if (file_exists($sPath . $sClassName . '.php')) {
            require_once $sPath . $sClassName . '.php';
        }
    }
});

if (!empty(config("system.workspace")) && (!defined('PATH_DATA_SITE') || !defined('PATH_WORKSPACE'))) {
    Bootstrap::setConstantsRelatedWs(config("system.workspace"));
}

//Add External Triggers
$dir = G::ExpandPath("classes") . 'triggers';
$filesArray = [];
if (file_exists($dir)) {
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if (($file != ".") && ($file != "..")) {
                $extFile = explode(".", $file);
                if ($extFile[sizeof($extFile) - 1] == 'php') {
                    include_once ($dir . PATH_SEP . $file);
                }
            }
        }
        closedir($handle);
    }
}

/**
 * PMScript - PMScript class
 *
 * @package workflow.engine.ProcessMaker
 */
class PMScript
{
    /**
     * Constants to identify the execution origin
     */
    const UNDEFINED_ORIGIN = 'executed.undefined.origin';

    const BEFORE_DYNAFORM = 'executed.before.dynaform';

    const AFTER_DYNAFORM = 'executed.after.dynaform';

    const BEFORE_INPUT_DOCUMENT = 'executed.before.input';

    const AFTER_INPUT_DOCUMENT = 'executed.after.input';

    const BEFORE_OUTPUT_DOCUMENT = 'executed.before.output';

    const AFTER_OUTPUT_DOCUMENT = 'executed.after.output';

    const BEFORE_EXTERNAL_STEP = 'executed.before.external';

    const AFTER_EXTERNAL_STEP = 'executed.after.external';

    const BEFORE_ASSIGNMENT = 'executed.before.assignment';

    const BEFORE_ROUTING = 'executed.before.routing';

    const AFTER_ROUTING = 'executed.after.routing';

    const CONDITION = 'executed.condition';

    const SCRIPT_TASK = 'executed.script.task';

    const CLASSIC_PROCESS_EVENTS = 'executed.classic.process.events';

    const SELF_SERVICE_TIMEOUT = 'executed.selfservice.timeout';

    const ISOLATED_TRIGGER = 'executed.isolated.trigger';

    const PROCESS_ACTION = 'executed.process.action';

    const EVALUATE_FUNCTION = 'executed.evaluate.function';

    /**
     * @var array $dataTrigger
     */
    public $dataTrigger = [];

    /**
     * Original fields
     */
    public $aOriginalFields = [];

    /**
     * Fields to use
     */
    public $aFields = [];

    /**
     * Script
     */
    public $sScript = '';

    /**
     * Error has happened?
     */
    public $bError = false;

    /**
     * Affected fields
     */
    public $affected_fields = [];
    public $scriptExecutionTime = 0;
    public $sRegexp = '/\@(?:([\@\%\#\?\$\=\&])([a-zA-Z\_]\w*)|([a-zA-Z\_][\w\-\>\:]*)\(((?:[^\\\\\)]*(?:[\\\\][\w\W])?)*)\))((?:\s*\[[\'"]?\w+[\'"]?\])+|\-\>([a-zA-Z\_]\w*))?/';

    /**
     * Execution origin, by default is undefined
     */
    protected $executedOn = self::UNDEFINED_ORIGIN;

    /**
     * Variables changed in the trigger execution
    */
    private $varsChanged = [];

    /**
     * Constructor of the class PMScript
     *
     * @return void
     */
    public function __construct()
    {
        $this->aFields['__ERROR__'] = 'none';
    }

    /**
     * Set the fields changed in the trigger execution
     *
     * @param array $v
     *
     * @return void
     */
    public function setVarsChanged(array $v)
    {
        $this->varsChanged = $v;
    }

    /**
     * Get the fields changed in the trigger execution
     *
     * @return array
     */
    public function getVarsChanged()
    {
        return $this->varsChanged;
    }

    /**
     * Set the fields to use
     *
     * @param array $aFields
     * @return void
     */
    public function setFields($aFields = [])
    {
        if (!is_array($aFields)) {
            $aFields = [];
        }
        $this->aOriginalFields = $this->aFields = $aFields;
    }

    /**
     * Set the current script
     *
     * @param string $sScript
     * @return void
     */
    public function setScript($sScript = '')
    {
        if (!defined("T_ML_COMMENT")) {
            define("T_ML_COMMENT", T_COMMENT);
        } else {
            if (!defined("T_DOC_COMMENT")) {
                define("T_DOC_COMMENT", T_ML_COMMENT);
            }
        }

        $script = "<?php " . $sScript;
        $tokens = token_get_all($script);
        $result = "";

        foreach ($tokens as $token) {
            if (is_string($token)) {
                $result .= $token;
            } else {
                list($id, $text) = $token;

                switch ($id) {
                    case T_OPEN_TAG:
                    case T_CLOSE_TAG:
                    case T_COMMENT:
                    case T_ML_COMMENT:  //we've defined this
                    case T_DOC_COMMENT: //and this
                        if ($text != '<?php ' && $text != '<?php' && $text != '<? ' && $text != '<?' && $text != '<% ' && $text != '<%') {
                            $result .= $text;
                        }
                        break;
                    default:
                        $result .= $text;
                        break;
                }
            }
        }

        $this->sScript = trim($result);
    }

    /**
     * Verify the syntax
     *
     * @param string $sScript
     * @return boolean
     */
    public function validSyntax($sScript)
    {
        return true;
    }

    /**
     * @param $dataTrigger
     */
    public function setDataTrigger($dataTrigger)
    {
        $this->dataTrigger = is_array($dataTrigger) ? $dataTrigger : [];
    }

    /**
     * Set the execution origin
     *
     * @param string $executedOn
     */
    public function setExecutedOn($executedOn)
    {
        $this->executedOn = $executedOn;
    }

    /**
     * Get the execution origin
     *
     * @return string
     */
    public function executedOn() {
        return $this->executedOn;
    }

    /**
     * Helper to get the execution origin from an step
     *
     * @param string $stepType
     * @param mixed $stepUidObj
     * @param string $triggerType
     *
     * @return string
     */
    public function getExecutionOriginForAStep($stepType, $stepUidObj, $triggerType)
    {
        switch ($stepType) {
            case 'DYNAFORM':
                $executedOn = $triggerType === 'BEFORE' ? self::BEFORE_DYNAFORM : $triggerType === 'AFTER' ?
                    self::AFTER_DYNAFORM : self::UNDEFINED_ORIGIN;
                break;
            case 'INPUT_DOCUMENT':
                $executedOn = $triggerType === 'BEFORE' ? self::BEFORE_INPUT_DOCUMENT : $triggerType === 'AFTER' ?
                    self::AFTER_INPUT_DOCUMENT : self::UNDEFINED_ORIGIN;
                break;
            case 'OUTPUT_DOCUMENT':
                $executedOn = $triggerType === 'BEFORE' ? self::BEFORE_OUTPUT_DOCUMENT : $triggerType === 'AFTER' ?
                    self::AFTER_OUTPUT_DOCUMENT : self::UNDEFINED_ORIGIN;
                break;
            case 'EXTERNAL':
                $executedOn = $triggerType === 'BEFORE' ? self::BEFORE_EXTERNAL_STEP : $triggerType === 'AFTER' ?
                    self::AFTER_EXTERNAL_STEP : self::UNDEFINED_ORIGIN;
                break;
            case 'ASSIGN_TASK':
                $stepUidObj = (int)$stepUidObj;
                if ($stepUidObj === -1) {
                    $executedOn = $triggerType === 'BEFORE' ? self::BEFORE_ASSIGNMENT : self::UNDEFINED_ORIGIN;
                } elseif ($stepUidObj === -2) {
                    $executedOn = $triggerType === 'BEFORE' ? self::BEFORE_ROUTING : ($triggerType === 'AFTER' ?
                        self::AFTER_ROUTING : self::UNDEFINED_ORIGIN);
                } else {
                    $executedOn = self::UNDEFINED_ORIGIN;
                }
                break;
            case 'PROCESS_ACTION':
                $executedOn = self::PROCESS_ACTION;
                break;
            case 'SCRIPT_TASK':
                $executedOn = self::SCRIPT_TASK;
                break;
            default:
                $executedOn = self::UNDEFINED_ORIGIN;
                break;
        }
        return $executedOn;
    }

    /**
     * @param $sScript
     * @param $sCode
     */
    public function executeAndCatchErrors($sScript, $sCode)
    {
        ob_start('handleFatalErrors');
        set_error_handler('handleErrors', ini_get('error_reporting'));
        $_SESSION['_CODE_'] = $sCode;
        $_SESSION['_DATA_TRIGGER_'] = $this->dataTrigger;
        $_SESSION['_DATA_TRIGGER_']['_EXECUTION_TIME_'] = microtime(true);
        eval($sScript);
        $this->scriptExecutionTime = round(microtime(true) -
                $_SESSION['_DATA_TRIGGER_']['_EXECUTION_TIME_'], 5);
        $this->evaluateVariable();
        ob_end_flush();

        //log trigger execution in processmaker.log
        G::logTriggerExecution($_SESSION, '', '', $this->scriptExecutionTime);
        unset($_SESSION['_CODE_']);
        unset($_SESSION['_DATA_TRIGGER_']);
    }

    /**
     * Execute the current script
     *
     * @return void
     */
    public function execute()
    {
        $sScript = "";
        $iAux = 0;
        $iOcurrences = preg_match_all($this->sRegexp, $this->sScript, $aMatch, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);
        if ($iOcurrences) {
            for ($i = 0; $i < $iOcurrences; $i ++) {
                $bEqual = false;
                $sAux = substr($this->sScript, $iAux, $aMatch[0][$i][1] - $iAux);
                if (!$bEqual) {
                    if (strpos($sAux, "==") !== false || strpos($sAux, "!=") !== false || strpos($sAux, ">") !== false || strpos($sAux, "<") !== false || strpos($sAux, ">=") !== false || strpos($sAux, "<=") !== false || strpos($sAux, "<>") !== false || strpos($sAux, "===") !== false || strpos($sAux, "!==") !== false) {
                        $bEqual = false;
                    } else {
                        if (strpos($sAux, "=") !== false || strpos($sAux, "+=") !== false || strpos($sAux, "-=") !== false || strpos($sAux, "*=") !== false || strpos($sAux, "/=") !== false || strpos($sAux, "%=") !== false || strpos($sAux, ".=") !== false) {
                            $bEqual = true;
                        }
                    }
                }
                if ($bEqual) {
                    if (strpos($sAux, ';') !== false) {
                        $bEqual = false;
                    }
                }
                if ($bEqual) {
                    if (!isset($aMatch[5][$i][0])) {
                        eval("if (!isset(\$this->aFields['" . $aMatch[2][$i][0] . "'])) { \$this->aFields['" . $aMatch[2][$i][0] . "'] = " . ($aMatch[1][$i][0] == "&" ? "new stdclass()" : "null") . "; }");
                    } else {
                        if ($aMatch[1][$i][0] == "&") {
                            eval("if (!isset(\$this->aFields['" . $aMatch[2][$i][0] . "'])) { \$this->aFields['" . $aMatch[2][$i][0] . "'] = new stdclass(); }");
                        }
                        eval("if (!isset(\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")) { \$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . " = null; }");
                    }
                } else {
                    if ($aMatch[1][$i][0] == "&") {
                        eval("if (!isset(\$this->aFields['" . $aMatch[2][$i][0] . "'])) { \$this->aFields['" . $aMatch[2][$i][0] . "'] = new stdclass(); }");
                    }
                }
                $sScript .= $sAux;
                $iAux = $aMatch[0][$i][1] + strlen($aMatch[0][$i][0]);
                switch ($aMatch[1][$i][0]) {
                    case '@':
                        if ($bEqual) {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "pmToString(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            } else {
                                $sScript .= "pmToString(\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        } else {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            } else {
                                $sScript .= "\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '%':
                        if ($bEqual) {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "pmToInteger(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            } else {
                                $sScript .= "pmToInteger(\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        } else {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            } else {
                                $sScript .= "\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '#':
                        if ($bEqual) {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "pmToFloat(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            } else {
                                $sScript .= "pmToFloat(\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        } else {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            } else {
                                $sScript .= "\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '?':
                        if ($bEqual) {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "pmToUrl(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            } else {
                                $sScript .= "pmToUrl(\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        } else {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            } else {
                                $sScript .= "\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '$':
                        if ($bEqual) {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "pmSqlEscape(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            } else {
                                $sScript .= "pmSqlEscape(\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        } else {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            } else {
                                $sScript .= "\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '=':
                    case '&':
                        if ($bEqual) {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            } else {
                                $sScript .= "\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        } else {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            } else {
                                $sScript .= "\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                }
                $this->affected_fields[] = $aMatch[2][$i][0];
            }
        }
        $sScript .= substr($this->sScript, $iAux);
        $sScript = "try {\n" . $sScript . "\n} catch (Exception \$oException) {\n " . " \$this->aFields['__ERROR__'] = utf8_encode(\$oException->getMessage());\n}";

        $this->executeAndCatchErrors($sScript, $this->sScript);
        //We get the affected_fields only if has the prefix
        //@see https://wiki.processmaker.com/3.2/Triggers#Typing_rules_for_Case_Variables
        $this->setVarsChanged($this->affected_fields);
        $this->aFields["__VAR_CHANGED__"] = implode(",", $this->affected_fields);
        for ($i = 0; $i < count($this->affected_fields); $i ++) {
            $_SESSION['TRIGGER_DEBUG']['DATA'][] = Array('key' => $this->affected_fields[$i], 'value' => isset($this->aFields[$this->affected_fields[$i]]) ? $this->aFields[$this->affected_fields[$i]] : ''
            );
        }
    }

    /**
     * Evaluate the current script
     *
     * @return boolean
     */
    public function evaluate()
    {
        $bResult = null;
        $sScript = '';
        $iAux = 0;
        $bEqual = false;
        $variableIsDefined = true;
        $iOcurrences = preg_match_all($this->sRegexp, $this->sScript, $aMatch, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);
        if ($iOcurrences) {
            for ($i = 0; $i < $iOcurrences; $i ++) {
                // if the variables for that condition has not been previously defined then $variableIsDefined
                // is set to false
                if (!isset($this->aFields[$aMatch[2][$i][0]]) && !isset($aMatch[5][$i][0])) {
                    eval("if (!isset(\$this->aFields['" . $aMatch[2][$i][0] . "'])) { \$this->aFields['" . $aMatch[2][$i][0] . "'] = " . ($aMatch[1][$i][0] == "&" ? "new stdclass()" : "null") . "; }");
                } else {
                    if ($aMatch[1][$i][0] == "&") {
                        eval("if (!isset(\$this->aFields['" . $aMatch[2][$i][0] . "'])) { \$this->aFields['" . $aMatch[2][$i][0] . "'] = new stdclass(); }");
                    }
                    if (!isset($this->aFields[$aMatch[2][$i][0]])) {
                        eval("\$this->aFields['" . $aMatch[2][$i][0] . "']" . $aMatch[5][$i][0] . " = '';");
                    } else {
                        if (isset($aMatch[5][$i][0])) {
                            eval("if (!isset(\$this->aFields['" . $aMatch[2][$i][0] . "']" . $aMatch[5][$i][0] . ")) {\$this->aFields['" . $aMatch[2][$i][0] . "']" . $aMatch[5][$i][0] . " = '';}");
                        } else {
                            eval("if (!isset(\$this->aFields['" . $aMatch[2][$i][0] . "'])) {\$this->aFields['" . $aMatch[2][$i][0] . "'] = " . ($aMatch[1][$i][0] == "&" ? "new stdclass()" : "''") . ";}");
                        }
                    }
                }
                $sAux = substr($this->sScript, $iAux, $aMatch[0][$i][1] - $iAux);
                if (!$bEqual) {
                    if (strpos($sAux, '=') !== false) {
                        $bEqual = true;
                    }
                }
                if ($bEqual) {
                    if (strpos($sAux, ';') !== false) {
                        $bEqual = false;
                    }
                }
                $sScript .= $sAux;
                $iAux = $aMatch[0][$i][1] + strlen($aMatch[0][$i][0]);
                switch ($aMatch[1][$i][0]) {
                    case '@':
                        if ($bEqual) {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "pmToString(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            } else {
                                $sScript .= "pmToString(\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        } else {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            } else {
                                $sScript .= "\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '%':
                        if ($bEqual) {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "pmToInteger(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            } else {
                                $sScript .= "pmToInteger(\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        } else {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            } else {
                                $sScript .= "\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '#':
                        if ($bEqual) {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "pmToFloat(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            } else {
                                $sScript .= "pmToFloat(\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        } else {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            } else {
                                $sScript .= "\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '?':
                        if ($bEqual) {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "pmToUrl(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            } else {
                                $sScript .= "pmToUrl(\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        } else {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            } else {
                                $sScript .= "\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '$':
                        if ($bEqual) {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "pmSqlEscape(\$this->aFields['" . $aMatch[2][$i][0] . "'])";
                            } else {
                                $sScript .= "pmSqlEscape(\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0] . ")";
                            }
                        } else {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            } else {
                                $sScript .= "\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                    case '=':
                    case '&':
                        if ($bEqual) {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            } else {
                                $sScript .= "\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        } else {
                            if (!isset($aMatch[5][$i][0])) {
                                $sScript .= "\$this->aFields['" . $aMatch[2][$i][0] . "']";
                            } else {
                                $sScript .= "\$this->aFields" . (isset($aMatch[2][$i][0]) ? "['" . $aMatch[2][$i][0] . "']" : '') . $aMatch[5][$i][0];
                            }
                        }
                        break;
                }
            }
        }
        $sScript .= substr($this->sScript, $iAux);
        if (preg_match('/\b(or|and|xor)\b/i', $sScript)) {
            $sScript = "( " . $sScript . " )";
        }
        $sScript = '$bResult = ' . $sScript . ';';
        // checks if the syntax is valid or if the variables in that condition has been previously defined
        if ($this->validSyntax($sScript) && $variableIsDefined) {
            $this->bError = false;
            eval($sScript);
        } else {
            G::SendTemporalMessage('MSG_CONDITION_NOT_DEFINED', 'error', 'labels');
            $this->bError = true;
        }
        return $bResult;
    }

    Public function evaluateVariable()
    {
        $process = new Process();
        if (!$process->isBpmnProcess($_SESSION['PROCESS'])) {
            return;
        }
        require_once PATH_CORE . 'controllers/pmTablesProxy.php';
        $pmTablesProxy = new pmTablesProxy();
        $variableModule = new ProcessMaker\BusinessModel\Variable();
        $searchTypes = array('checkgroup', 'dropdown', 'suggest');
        $processVariables = $pmTablesProxy->getDynaformVariables($_SESSION['PROCESS'], $searchTypes, false);
        $variables = $this->affected_fields;
        $variables = (is_array($variables)) ? array_unique($variables) : $variables;
        $newFields = [];
        $arrayValues = [];
        $arrayLabels = [];
        if (is_array($variables) && is_array($processVariables)) {
            foreach ($variables as $var) {
                if (strpos($var, '_label') === false) {
                    if (in_array($var, $processVariables)) {
                        if (isset($this->aFields[$var]) && is_array($this->aFields[$var][1])) {
                            $varLabel = $var . '_label';
                            $arrayValue = $this->aFields[$var];
                            if (is_array($arrayValue) && sizeof($arrayValue)) {
                                foreach ($arrayValue as $val) {
                                    if (is_array($val)) {
                                        $val = array_values($val);
                                        $arrayValues[] = $val[0];
                                        $arrayLabels[] = $val[1];
                                    }
                                }
                                if (sizeof($arrayLabels)) {
                                    $varInfo = $variableModule->getVariableTypeByName($_SESSION['PROCESS'], $var);
                                    if (is_array($varInfo) && sizeof($varInfo)) {
                                        $varType = $varInfo['VAR_FIELD_TYPE'];
                                        switch ($varType) {
                                            case 'array':
                                                $arrayLabels = '["' . implode('","', $arrayLabels) . '"]';
                                                $newFields[$var] = $arrayValues;
                                                $newFields[$varLabel] = $arrayLabels;
                                                break;
                                            case 'string':
                                                $newFields[$var] = $arrayValues[0];
                                                $newFields[$varLabel] = $arrayLabels[0];
                                                break;
                                        }
                                        $this->affected_fields[] = $varLabel;
                                        $this->aFields = array_merge($this->aFields, $newFields);
                                        unset($newFields);
                                        unset($arrayValues);
                                        unset($arrayLabels);
                                    }
                                }
                            }
                        }
                        if (isset($this->aFields[$var]) && is_string($this->aFields[$var])) {
                            $varInfo = $variableModule->getVariableTypeByName($_SESSION['PROCESS'], $var);
                            $options = G::json_decode($varInfo["VAR_ACCEPTED_VALUES"]);
                            $no = count($options);
                            for ($io = 0; $io < $no; $io++) {
                                if ($options[$io]->value === $this->aFields[$var]) {
                                    $this->aFields[$var . "_label"] = $options[$io]->label;
                                }
                            }
                            if ($varInfo["VAR_DBCONNECTION"] !== "" && $varInfo["VAR_DBCONNECTION"] !== "none" && $varInfo["VAR_SQL"] !== "") {
                                try {
                                    $cnn = Propel::getConnection($varInfo["VAR_DBCONNECTION"]);
                                    $stmt = $cnn->createStatement();
                                    $sql = G::replaceDataField($varInfo["VAR_SQL"], $this->aFields, 'mysql', false);
                                    $rs = $stmt->executeQuery($sql, \ResultSet::FETCHMODE_NUM);
                                    while ($rs->next()) {
                                        $row = $rs->getRow();
                                        if ($row[0] === $this->aFields[$var]) {
                                            $this->aFields[$var . "_label"] = isset($row[1]) ? $row[1] : $row[0];
                                        }
                                    }
                                } catch (Exception $e) {
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
