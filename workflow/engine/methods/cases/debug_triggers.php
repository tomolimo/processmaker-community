<?php
if (isset($_SESSION['TRIGGER_DEBUG']['info'])) {
    $triggers = $_SESSION['TRIGGER_DEBUG']['info'];
} else {
    $triggers[0] = $_SESSION['TRIGGER_DEBUG'];
}

$triggersList = [];

$i = 0;
foreach ($triggers as $trigger) {

    if ($trigger['NUM_TRIGGERS'] != 0) {

        foreach ($trigger['TRIGGERS_NAMES'] as $index => $name) {

            $triggersList[$i]['name'] = $name;
            $triggersList[$i]['execution_time'] = strtolower($trigger['TIME']);

            $geshi = new GeSHi($trigger['TRIGGERS_VALUES'][$index]['TRI_WEBBOT'], 'php');
            $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
            $geshi->set_line_style('background: #f0f0f0;');

            $triggersList[$i]['code'] = $geshi->parse_code();

            $triggerUid = $trigger['TRIGGERS_VALUES'][$index]['TRI_UID'];
            $triggersList[$i]['script_execution_time'] = isset($trigger['TRIGGERS_EXECUTION_TIME'][$triggerUid]) ? $trigger['TRIGGERS_EXECUTION_TIME'][$triggerUid] : '';

            $i++;
        }
    }
}

$debugErrors = array_unique($_SESSION['TRIGGER_DEBUG']['ERRORS'], SORT_REGULAR);

foreach ($debugErrors as $error) {
    if (isset($error['ERROR']) and $error['ERROR'] != '') {
        $triggersList[$i]['name'] = 'Error';
        $triggersList[$i]['execution_time'] = 'error';
        $triggersList[$i]['code'] = $error['ERROR'];
        $i++;
    }

    if (isset($error['FATAL']) and $error['FATAL'] != '') {
        $error['FATAL'] = str_replace("<br />", "\n", $error['FATAL']);
        $tmp = explode("\n", $error['FATAL']);
        $triggersList[$i]['name'] = isset($tmp[0]) ? $tmp[0] : 'Fatal Error in trigger';
        $triggersList[$i]['execution_time'] = 'Fatal error';
        $triggersList[$i]['code'] = $error['FATAL'];
        $i++;
    }
}
$triggersRet = new StdClass();
$triggersRet->total = count($triggersList);
$triggersRet->data = $triggersList;
echo G::json_encode($triggersRet);

