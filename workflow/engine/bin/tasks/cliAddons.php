<?php


function run_addon_core_install($args)
{
    try {
        $workspace = $args[0];
        $storeId = $args[1];
        $addonName = $args[2];

        if (empty(config("system.workspace"))) {
            define("SYS_SYS", $workspace);
            config(["system.workspace" => $workspace]);
        }
        if (!defined("PATH_DATA_SITE")) {
            define("PATH_DATA_SITE", PATH_DATA . "sites/" . config("system.workspace") . "/");
        }
        if (!defined("DB_ADAPTER")) {
            define("DB_ADAPTER", $args[3]);
        }

        $ws = new WorkspaceTools($workspace);
        $ws->initPropel(false);

        require_once PATH_CORE . 'methods' . PATH_SEP . 'enterprise' . PATH_SEP . 'enterprise.php';

        $addon = AddonsManagerPeer::retrieveByPK($addonName, $storeId);
        if ($addon == null) {
            throw new Exception("Id $addonName not found in store $storeId");
        }

        $addon->download();
        $addon->install();

        if ($addon->isCore()) {
            $ws = new WorkspaceTools($workspace);
            $ws->initPropel(false);
            $addon->setState("install-finish");
        } else {
            $addon->setState();
        }
    } catch (Exception $e) {
        $addon->setState("error");
    }
}
