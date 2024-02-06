<?php

$filter = new InputFilter();
$_POST = $filter->xssFilterHard($_POST);

if (isset( $_POST['action'] ) || isset( $_POST['function'] )) {
    $action = (isset( $_POST['action'] )) ? $_POST['action'] : $_POST['function'];
} else {
    throw new Exception( 'dbconnections Fatal error, No action defined!...' );
}

if (isset( $_POST['PROCESS'] )) {
    $_SESSION['PROCESS'] = $_POST['PROCESS'];
}

    #Global Definitions

$G_PUBLISH = new Publisher();

global $_DBArray;

switch ($action) {
    case 'loadInfoAssigConnecctionDB':
        $oStep = new Step();
        return print ($oStep->loadInfoAssigConnecctionDB( $_POST['PRO_UID'], $_POST['DBS_UID'] )) ;
        break;
    case 'showDbConnectionsList':
        $oProcess = new ProcessMap();
        $oCriteria = $oProcess->getConditionProcessList();
        if (ProcessPeer::doCount( $oCriteria ) > 0) {
            $aProcesses = array ();
            $aProcesses[] = array ('PRO_UID' => 'char','PRO_TITLE' => 'char'
            );
            $oDataset = ArrayBasePeer::doSelectRS( $oCriteria );
            $oDataset->setFetchmode( ResultSet::FETCHMODE_ASSOC );
            $oDataset->next();
            $sProcessUID = '';
            while ($aRow = $oDataset->getRow()) {
                if ($sProcessUID == '') {
                    $sProcessUID = $aRow['PRO_UID'];
                }
                $aProcesses[] = array ('PRO_UID' => (isset( $aRow['PRO_UID'] ) ? $aRow['PRO_UID'] : ''),'PRO_TITLE' => (isset( $aRow['PRO_TITLE'] ) ? $aRow['PRO_TITLE'] : '')
                );
                $oDataset->next();
            }

            $_DBArray['PROCESSES'] = $aProcesses;
            $_SESSION['_DBArray'] = $_DBArray;
            $_SESSION['PROCESS'] = (isset( $_POST['PRO_UID'] ) ? $_POST['PRO_UID'] : '');

            $oDBSource = new DbSource();
            $oCriteria = $oDBSource->getCriteriaDBSList( $_SESSION['PROCESS'] );
            $G_PUBLISH->AddContent( 'propeltable', 'paged-table', 'dbConnections/dbConnections', $oCriteria );
        }
        G::RenderPage( 'publish', 'raw' );
        break;
    case 'showConnections':
        $oDBSource = new DbSource();
        $oCriteria = $oDBSource->getCriteriaDBSList( $_SESSION['PROCESS'] );
        $G_PUBLISH = new Publisher();
        $G_PUBLISH->AddContent( 'propeltable', 'paged-table', 'dbConnections/dbConnections', $oCriteria );
        G::RenderPage( 'publish', 'raw' );
        break;
    case 'newDdConnection':
        $dbs = new DbConnections( $_SESSION['PROCESS'] );
        $dbServices = $dbs->getDbServicesAvailables();
        $dbService = $dbs->getEncondeList();

        //we are updating the passwords with encrupt info
        $dbs->encryptThepassw( $_SESSION['PROCESS'] );
        //end updating

        $rows[] = array ('uid' => 'char','name' => 'char'
        );

        foreach ($dbServices as $srv) {
            $rows[] = array ('uid' => $srv['id'],'name' => $srv['name']
            );
        }

        $_DBArray['BDCONNECTIONS'] = $rows;

        $G_PUBLISH->AddContent( 'xmlform', 'xmlform', 'dbConnections/dbConnections_New', '', '' );
        G::RenderPage( 'publish', 'raw' );
        break;
    case 'editDdConnection':
        $dbs = new DbConnections( $_SESSION['PROCESS'] );
        $dbServices = $dbs->getDbServicesAvailables();

        $rows[] = array ('uid' => 'char','name' => 'char'
        );
        foreach ($dbServices as $srv) {
            $rows[] = array ('uid' => $srv['id'],'name' => $srv['name']
            );
        }

        $_DBArray['BDCONNECTIONS'] = $rows;
        $_SESSION['_DBArray'] = $_DBArray;

        $o = new DbSource();
        $aFields = $o->load( $_POST['DBS_UID'], $_SESSION['PROCESS'] );
        if ($aFields['DBS_PORT'] == '0') {
            $aFields['DBS_PORT'] = '';
        }
        $aFields['DBS_PASSWORD'] = $dbs->getPassWithoutEncrypt( $aFields );
        $aFields['DBS_PASSWORD'] = ($aFields['DBS_PASSWORD'] == 'none') ? "" : $aFields['DBS_PASSWORD'];
        $G_PUBLISH->AddContent( 'xmlform', 'xmlform', 'dbConnections/dbConnections_Edit', '', $aFields );
        G::RenderPage( 'publish', 'raw' );
        break;
    case 'saveEditConnection':
        $dBSource = new DbSource();
        $content = new Content();
        if (strpos($_POST['server'], "\\")) {
            $_POST['port'] = 'none';
        }

        $flagTns = ($_POST["type"] == "oracle" && $_POST["connectionType"] == "TNS") ? 1 : 0;

        if ($flagTns == 0) {
            $_POST["connectionType"] = "NORMAL";

            $data = [
                "DBS_UID" => $_POST["dbs_uid"],
                "PRO_UID" => $_SESSION["PROCESS"], "DBS_TYPE" => $_POST["type"],
                "DBS_SERVER" => $_POST["server"],
                "DBS_DATABASE_NAME" => $_POST["db_name"],
                "DBS_USERNAME" => $_POST["user"],
                "DBS_PASSWORD" => (($_POST["passwd"] == "none") ? "" : G::encrypt($_POST["passwd"], $_POST["db_name"], false, false)) . "_2NnV3ujj3w",
                "DBS_PORT" => (($_POST["port"] == "none") ? "" : $_POST["port"]),
                "DBS_ENCODE" => $_POST["enc"],
                "DBS_CONNECTION_TYPE" => $_POST["connectionType"],
                "DBS_TNS" => ""
            ];
        } else {
            $data = [
                "DBS_UID" => $_POST["dbs_uid"],
                "PRO_UID" => $_SESSION["PROCESS"],
                "DBS_TYPE" => $_POST["type"],
                "DBS_SERVER" => "",
                "DBS_DATABASE_NAME" => "",
                "DBS_USERNAME" => $_POST["user"],
                "DBS_PASSWORD" => (($_POST["passwd"] == "none") ? "" : G::encrypt($_POST["passwd"], $_POST["tns"], false, false)) . "_2NnV3ujj3w",
                "DBS_PORT" => "",
                "DBS_ENCODE" => "",
                "DBS_CONNECTION_TYPE" => $_POST["connectionType"],
                "DBS_TNS" => $_POST["tns"]
            ];
        }

        $dBSource->update($data);
        $content->addContent('DBS_DESCRIPTION', '', $_POST['dbs_uid'], SYS_LANG, $_POST['desc']);
        break;
    case 'saveConnection':
        $dBSource = new DbSource();
        $content = new Content();
        if (strpos($_POST['server'], "\\")) {
            $_POST['port'] = 'none';
        }

        $flagTns = ($_POST["type"] == "oracle" && $_POST["connectionType"] == "TNS") ? 1 : 0;

        if ($flagTns == 0) {
            $_POST["connectionType"] = "NORMAL";

            $data = [
                "PRO_UID" => $_SESSION["PROCESS"],
                "DBS_TYPE" => $_POST["type"],
                "DBS_SERVER" => $_POST["server"],
                "DBS_DATABASE_NAME" => $_POST["db_name"],
                "DBS_USERNAME" => $_POST["user"],
                "DBS_PASSWORD" => (($_POST["passwd"] == "none") ? "" : G::encrypt($_POST["passwd"], $_POST["db_name"], false, false)) . "_2NnV3ujj3w",
                "DBS_PORT" => (($_POST["port"] == "none") ? "" : $_POST["port"]),
                "DBS_ENCODE" => $_POST["enc"],
                "DBS_CONNECTION_TYPE" => $_POST["connectionType"],
                "DBS_TNS" => ""
            ];
        } else {
            $data = [
                "PRO_UID" => $_SESSION["PROCESS"],
                "DBS_TYPE" => $_POST["type"],
                "DBS_SERVER" => "",
                "DBS_DATABASE_NAME" => "",
                "DBS_USERNAME" => $_POST["user"],
                "DBS_PASSWORD" => (($_POST["passwd"] == "none") ? "" : G::encrypt($_POST["passwd"], $_POST["tns"], false, false)) . "_2NnV3ujj3w",
                "DBS_PORT" => "",
                "DBS_ENCODE" => "",
                "DBS_CONNECTION_TYPE" => $_POST["connectionType"],
                "DBS_TNS" => $_POST["tns"]
            ];
        }

        $newId = $dBSource->create($data);
        $sDelimiter = DBAdapter::getStringDelimiter();
        $content->addContent('DBS_DESCRIPTION', '', $newId, SYS_LANG, $_POST['desc']);
        break;
    case 'deleteDbConnection':
        $result = new stdclass();

        try {
            $oDBSource = new DbSource();
            $oContent = new Content();

            $DBS_UID = $_POST['dbs_uid'];
            $PRO_UID = $_SESSION['PROCESS'];
            $oDBSource->remove( $DBS_UID, $PRO_UID );
            $oContent->removeContent( 'DBS_DESCRIPTION', "", $DBS_UID );
            $result->success = true;
            $result->msg = G::LoadTranslation( 'ID_DBCONNECTION_REMOVED' );
        } catch (Exception $e) {
            $result->success = false;
            $result->msg = $e->getMessage();
        }
        print G::json_encode( $result );
        break;
    case 'showTestConnection':
        $G_PUBLISH = new Publisher();
        $G_PUBLISH->AddContent( 'view', 'dbConnections/dbConnections' );
        G::RenderPage( 'publish', 'raw' );
        break;
    case 'testConnection':
        sleep( 0 );

        define("SUCCESSFULL", "SUCCESSFULL");
        define("FAILED", "FAILED");

        $step = $_POST["step"];
        $type = $_POST["type"];

        $user = $_POST["user"];
        $passwd = ($_POST["passwd"] == "none")? "" : $_POST["passwd"];

        $flagTns = ($_POST["type"] == "oracle" && $_POST["connectionType"] == "TNS")? 1 : 0;

        if ($flagTns == 0) {
            $server = $_POST["server"];
            $db_name = $_POST["db_name"];
            $port = $_POST["port"];

            if ($port == "none" || $port == 0) {
                //setting defaults ports
                switch ($type) {
                    case "mysql":
                        $port = 3306;
                        break;
                    case "pgsql":
                        $port = 5432;
                        break;
                    case "mssql":
                        $port = 1433;
                        break;
                    case "oracle":
                        $port = 1521;
                        break;
                }
            }

            $Server = new Net($server);

            switch ($step) {
                case 1:
                    if ($Server->getErrno() == 0) {
                        echo SUCCESSFULL . ",";
                    } else {
                        echo FAILED . "," . $Server->error;
                    }
                    break;
                case 2:
                    $Server->scannPort($port);

                    if ($Server->getErrno() == 0) {
                        echo SUCCESSFULL . ",";
                    } else {
                        echo FAILED . "," . $Server->error;
                    }
                    break;
                case 3:
                    $Server->loginDbServer($user, $passwd);
                    $Server->setDataBase($db_name, $port);

                    if ($Server->errno == 0) {
                        $response = $Server->tryConnectServer($type);

                        if ($response->status == "SUCCESS") {
                            echo SUCCESSFULL . ",";
                        } else {
                            echo FAILED . "," . $Server->error;
                        }
                    } else {
                        echo FAILED . "," . $Server->error;
                    }
                    break;
                case 4:
                    $Server->loginDbServer($user, $passwd);
                    $Server->setDataBase($db_name, $port);

                    if ($Server->errno == 0) {
                        $response = $Server->tryConnectServer($type);

                        if ($response->status == "SUCCESS") {
                            $response = $Server->tryOpenDataBase($type);

                            if ($response->status == "SUCCESS") {
                                echo SUCCESSFULL . "," . $Server->error;
                            } else {
                                echo FAILED . "," . $Server->error;
                            }
                        } else {
                            echo FAILED . "," . $Server->error;
                        }
                    } else {
                        echo FAILED . "," . $Server->error;
                    }
                    break;
                default:
                    echo "finished";
                    break;
            }
        } else {
            $connectionType = $_POST["connectionType"];
            $tns = $_POST["tns"];

            $net = new Net();

            switch ($step) {
                case 1:
                    $net->loginDbServer($user, $passwd);

                    if ($net->errno == 0) {
                        $arrayServerData = array("connectionType" => $connectionType, "tns" => $tns);

                        $response = $net->tryConnectServer($type, $arrayServerData);

                        if ($response->status == "SUCCESS") {
                            $response = $net->tryOpenDataBase($type, $arrayServerData);

                            if ($response->status == "SUCCESS") {
                                echo SUCCESSFULL . "," . $net->error;
                            } else {
                                echo FAILED . "," . $net->error;
                            }
                        } else {
                            echo FAILED . "," . $net->error;
                        }
                    } else {
                        echo FAILED . "," . $net->error;
                    }
                    break;
                default:
                    echo "finished";
                    break;
            }
        }
        break;
    case 'showEncodes':

        $filter = new InputFilter();
        $engine = $_POST['engine'];

        if ($engine != "0") {
            $dbs = new DbConnections();
            $var = Bootstrap::json_encode($dbs->getEncondeList($filter->xssFilterHard($engine)));
            G::outRes($var);

        } else {
            G::outRes('[["0","..."]]');
        }
        break;
}

