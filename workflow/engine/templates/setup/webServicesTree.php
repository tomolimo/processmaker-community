<?php

use ProcessMaker\Core\System;

$wsSessionId = '';
if (isset ($_SESSION ['WS_SESSION_ID'])) {
    $wsSessionId = $_SESSION ['WS_SESSION_ID'];
}

if (isset ($_GET ['x'])) {
    if ($_GET ['x'] == 1) {
        $wsdl = $_SESSION ['END_POINT'];
        $workspace = $_SESSION ['WS_WORKSPACE'];
    } else {
        $wsdl = '<font color="red">' . G::LoadTranslation('ID_WSDL') . '</font>';
        $workspace = '';
    }
} else {
    if (!isset ($_SESSION ['END_POINT'])) {
        $wsdl = System::getServerProtocolHost();
        $workspace = config("system.workspace");
    } else {
        $wsdl = $_SESSION ['END_POINT'];
        $workspace = $_SESSION ['WS_WORKSPACE'];
    }
}

$defaultEndpoint = 'http://' . $_SERVER ['SERVER_NAME'] . ':' . $_SERVER ['SERVER_PORT'] . '/sys' . config("system.workspace") . '/en/classic/services/wsdl2';

$wsdl = isset ($_SESSION ['END_POINT']) ? $_SESSION ['END_POINT'] : $defaultEndpoint;

$tree = new PmTree ();
$tree->name = 'WebServices';
$tree->nodeType = "base";
$tree->contentWidth = "310";
$tree->width = "280px";
$tree->value = '
     <div class="boxTopBlue"><div class="a"></div><div class="b"></div><div class="c"></div></div>
     <div class="boxContentBlue">

      <table width="100%" style="margin:0px;" cellspacing="0" cellpadding="0">
      <tr>
          <td class="userGroupTitle">' . G::loadTranslation("ID_WEB_SERVICES") . '</td>
      </tr>
    </table>
    </div>
    <div class="boxBottomBlue"><div class="a"></div><div class="b"></div><div class="c"></div></div>

      <div class="userGroupLink">
          <a href="#" onclick="showDetails();return false;">' . G::LoadTranslation('ID_DETAILS_WEBSERVICES') .
    '</a> &nbsp;
          <a href="#" onclick="webServicesSetup();return false;">' . G::LoadTranslation('ID_SETUP_WEBSERVICES') .
    '</a>
      </div>

    ';

$tree->showSign = false;

$allWebservices = array();
$allWebservices [] = 'Login';
$allWebservices [] = 'CreateUser';
$allWebservices [] = 'AssignUserToGroup';
$allWebservices [] = 'NewCase';
$allWebservices [] = 'NewCaseImpersonate';
$allWebservices [] = 'RouteCase';
$allWebservices [] = 'SendVariables';
$allWebservices [] = 'SendMessage';
$allWebservices [] = 'ProcessList';
$allWebservices [] = 'CaseList';
$allWebservices [] = 'UnassignedCaseList';
$allWebservices [] = 'RoleList';
$allWebservices [] = 'GroupList';
$allWebservices [] = 'UserList';
$allWebservices [] = 'TaskList';
$allWebservices [] = 'TriggerList';
$allWebservices [] = 'InputDocumentList';
$allWebservices [] = 'InputDocumentProcessList';
$allWebservices [] = 'OutputDocumentList';
$allWebservices [] = 'RemoveDocument';
$allWebservices [] = 'TaskCase';
$allWebservices [] = 'ReassignCase';
$allWebservices [] = 'removeUserFromGroup';

foreach ($allWebservices as $ws) {
    $ID_TEST = G::LoadTranslation('ID_TEST');
    $UID = htmlentities($ws);
    $WS_TITLE = strip_tags($ws);

    $htmlGroup = '';
    $htmlGroup .= "<table cellspacing='0' cellpadding='0' border='1' style='border:0px;'>";
    $htmlGroup .= "<tr>";
    $htmlGroup .= "<td width='250px' class='treeNode' style='border:0px;background-color:transparent;'>";
    $htmlGroup .= "{$WS_TITLE}</td>";
    $htmlGroup .= "<td class='treeNode' style='border:0px;background-color:transparent;'>";

    if ($WS_TITLE != 'SendFiles') {
        if ($WS_TITLE == 'Login' || $wsSessionId != '') {
            $htmlGroup .= "[<a href='#' onclick=\"showFormWS('{$UID}');return false;\">{$ID_TEST}</a>]";
        }
    } else {
        if (isset($_SESSION ['WS_SESSION_ID']) && $_SESSION ['WS_SESSION_ID'] != '') {
            $htmlGroup .= "[<a href='#' onclick=\"showUploadFilesForm();return false;\">{$ID_TEST}</a>]";
        }
    }

    $htmlGroup .= "</td></tr></table>";

    $ch = $tree->addChild($ws, $htmlGroup, array('nodeType' => 'child'));
    $ch->point = '<img src="/images/trigger.gif" />';
}
print($tree->render());
