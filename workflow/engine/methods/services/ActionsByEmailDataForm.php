<?php
if (isset($_GET['BROWSER_TIME_ZONE_OFFSET'])) {
    if (PMLicensedFeatures::getSingleton()->verifyfeature('zLhSk5TeEQrNFI2RXFEVktyUGpnczV1WEJNWVp6cjYxbTU3R29mVXVZNWhZQT0=')) {
        global $G_PUBLISH;

        $G_PUBLISH = new Publisher();

        try {

            //Validations
            if (!isset($_REQUEST['APP_UID'])) {
                $_REQUEST['APP_UID'] = '';
            }

            if (!isset($_REQUEST['DEL_INDEX'])) {
                $_REQUEST['DEL_INDEX'] = '';
            }

            if ($_REQUEST['APP_UID'] == '') {
                throw new Exception('The parameter APP_UID is empty.');
            }

            if ($_REQUEST['DEL_INDEX'] == '') {
                throw new Exception('The parameter DEL_INDEX is empty.');
            }

            $case = new Cases();
            $actionsByEmail = new \ProcessMaker\BusinessModel\ActionsByEmail();

            $applicationUid = G::decrypt($_REQUEST['APP_UID'], URL_KEY);
            $delIndex = G::decrypt($_REQUEST['DEL_INDEX'], URL_KEY);

            $actionsByEmail->verifyLogin($applicationUid, $delIndex);

            $caseFields = $case->loadCase($applicationUid, $delIndex);

            //this value is only important for Propel::getConnection()
            $_SESSION['PROCESS'] = $caseFields['PRO_UID'];

            // Updating case variables with system constants
            $systemConstants = G::getSystemConstants();
            $caseFields['APP_DATA']['USER_LOGGED'] = $systemConstants['USER_LOGGED'];
            $caseFields['APP_DATA']['USR_USERNAME'] = $systemConstants['USR_USERNAME'];
            $caseFields['APP_DATA']['SYS_LANG'] = $systemConstants['SYS_LANG'];
            $caseFields['APP_DATA']['SYS_SKIN'] = $systemConstants['SYS_SKIN'];
            $caseFields['APP_DATA']['SYS_SYS'] = $systemConstants['SYS_SYS'];
            $caseFields['APP_DATA']['APPLICATION'] = $caseFields['APP_UID'];
            $caseFields['APP_DATA']['PROCESS'] = $caseFields['PRO_UID'];
            $caseFields['APP_DATA']['TASK'] = $caseFields['TASK'];
            $caseFields['APP_DATA']['INDEX'] = $caseFields['INDEX'];
            $case->updateCase($applicationUid, $caseFields);

            $criteria = new Criteria();
            $criteria->addSelectColumn(DynaformPeer::DYN_CONTENT);
            $criteria->addSelectColumn(DynaformPeer::PRO_UID);
            $criteria->add(DynaformPeer::DYN_UID, G::decrypt($_REQUEST['DYN_UID'], URL_KEY));
            $result = DynaformPeer::doSelectRS($criteria);
            $result->setFetchmode(ResultSet::FETCHMODE_ASSOC);
            $result->next();
            $configuration = $result->getRow();

            $action = 'ActionsByEmailDataFormPost.php?APP_UID=' . $_REQUEST['APP_UID'] . '&DEL_INDEX=' .
                $_REQUEST['DEL_INDEX'] . '&ABER=' . $_REQUEST['ABER'] . '&DYN_UID=' . $_REQUEST['DYN_UID'];

            $record = [];
            $record['DYN_CONTENT'] = $configuration['DYN_CONTENT'];
            $record['PRO_UID'] = $configuration['PRO_UID'];
            $record['CURRENT_DYNAFORM'] = G::decrypt($_REQUEST['DYN_UID'], URL_KEY);
            $record['APP_UID'] = $_REQUEST['APP_UID'];
            $record['DEL_INDEX'] = $_REQUEST['DEL_INDEX'];
            $record['ABER'] = $_REQUEST['ABER'];
            $record['APP_DATA'] = $caseFields['APP_DATA'];

            if (is_null($caseFields['DEL_FINISH_DATE'])) {
                //we define the guest user
                $restore = false;
                if (isset($_SESSION["USER_LOGGED"])) {
                    $restore = $_SESSION["USER_LOGGED"];
                }
                $_SESSION["USER_LOGGED"] = RBAC::GUEST_USER_UID;
                $_SESSION['GUEST_USER'] = RBAC::GUEST_USER_UID;
                $pmDynaform = new PmDynaform($record);
                //we must return to the original value of the session
                if ($restore === false) {
                    unset($_SESSION["USER_LOGGED"]);
                } else {
                    $_SESSION["USER_LOGGED"] = $restore;
                }
                $pmDynaform->printABE($action, $record);
            } else {
                $G_PUBLISH->AddContent(
                    'xmlform',
                    'xmlform',
                    'login/showInfo',
                    '',
                    ['MESSAGE' => '<strong>' . G::loadTranslation('ID_ABE_FORM_ALREADY_FILLED') . '</strong>']
                );
            }
        } catch (Exception $e) {
            $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/showInfo', '', ['MESSAGE' => $e->getMessage()]);
        }

        G::RenderPage('publish', 'blank');
    }
} else {
?>
<html>
<head>
    <title></title>
    <script type="text/javascript" src="/js/maborak/core/maborak.js"></script>
</head>
<body>
    <script type="text/javascript">
    location.assign(location.href + "&BROWSER_TIME_ZONE_OFFSET=" + getBrowserTimeZoneOffset());
    </script>
</body>
</html>
<?php
}
