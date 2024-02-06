<?php

use Cases as ClassesCases;
use ProcessMaker\BusinessModel\Cases;

class adhocUserProxy extends HttpProxyController
{
    //list of users into adhoc option
    function adhocAssignUsersk ($params)
    {
        $oTasks = new Tasks();
        $aAux = $oTasks->getGroupsOfTask( $_SESSION['TASK'], 2 );
        $aAdhocUsers = array ();
        $oGroups = new Groups();
        foreach ($aAux as $aGroup) {
            $aUsers = $oGroups->getUsersOfGroup( $aGroup['GRP_UID'] );
            foreach ($aUsers as $aUser) {
                if ($aUser['USR_UID'] != $_SESSION['USER_LOGGED']) {
                    $aAdhocUsers[] = $aUser['USR_UID'];
                }
            }
        }
        $aAux = $oTasks->getUsersOfTask( $_SESSION['TASK'], 2 );
        foreach ($aAux as $aUser) {
            if ($aUser['USR_UID'] != $_SESSION['USER_LOGGED']) {
                $aAdhocUsers[] = $aUser['USR_UID'];
            }
        }
        require_once 'classes/model/Users.php';
        $oCriteria = new Criteria( 'workflow' );
        $oCriteria->addSelectColumn( UsersPeer::USR_UID );
        $oCriteria->addSelectColumn( UsersPeer::USR_FIRSTNAME );
        $oCriteria->addSelectColumn( UsersPeer::USR_LASTNAME );
        $oCriteria->add( UsersPeer::USR_UID, $aAdhocUsers, Criteria::IN );
        $oDataset = UsersPeer::doSelectRS( $oCriteria );
        $oDataset->setFetchmode( ResultSet::FETCHMODE_ASSOC );
        $aData = array ();
        while ($oDataset->next()) {
            $aData[] = $oDataset->getRow();
        }

        $this->data = $aData;

    }
    
    /**
     * Reassign a user adhoc to the case
     * 
     * @return void
     */
    function reassignCase()
    {
        $cases = new ClassesCases();
        $cases->reassignCase($_SESSION['APPLICATION'], $_SESSION['INDEX'], $_SESSION['USER_LOGGED'], $_POST['USR_UID'], $_POST['THETYPE']);
        $this->success = true;
    }

    /**
     * Delete case from the actions menu
     *
     * @link https://wiki.processmaker.com/3.3/Cases/Actions#Delete
    */
    function deleteCase($params)
    {
        try {
            $appUid = (isset($_POST['APP_UID'])) ? $_POST['APP_UID'] : $_SESSION['APPLICATION'];
            // Load case information for get appNumber
            $data = [];
            $app = new Application();
            $caseData = $app->load($appUid);
            $data['APP_NUMBER'] = $caseData['APP_NUMBER'];

            $case = new Cases();
            $case->deleteCase($appUid, $_SESSION['USER_LOGGED']);

            // Result successfully
            $this->success = true;
            $this->msg = G::LoadTranslation('ID_CASE_DELETED_SUCCESSFULLY', SYS_LANG, $data);
        } catch (Exception $e) {
            $this->success = false;
            $this->msg = $e->getMessage();
        }
    }

}
//End adhocUserProxy
