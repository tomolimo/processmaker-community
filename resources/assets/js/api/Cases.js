import axios from "axios";
import ApiInstance from "./Api.js";
import Services from "./Services";
import { RCBase64 } from '../utils/utils.js'
let Api = new ApiInstance( Services );
var base64 = RCBase64();
var credentials = JSON.parse(base64.decode(window.config.SYS_CREDENTIALS));
export let cases = {
    myCases(data) {
        return Api.get({
            service: "MY_CASES",
            params: data,
            keys: {}
        });
    },
    todo(data) {
        return Api.get({
            service: "TODO_LIST",
            params: data,
            keys: {}
        });
    },
    inbox(data) {
        let service = "INBOX_LIST",
            keys = {},
            params = data;
        if (data && data.id) {
            service = "INBOX_CUSTOM_LIST";
            keys["id"] =  data.id;
            params = data.filters;
        }
        return Api.get({
            service,
            params,
            keys
        });
    },
    draft(data) {
        let service = "DRAFT_LIST",
            keys = {},
            params = data;
        if (data && data.id) {
            service = "DRAFT_CUSTOM_LIST";
            keys["id"] =  data.id;
            params = data.filters;
        }
        return Api.get({
            service,
            params,
            keys
        });
    },
    paused(data) {
        let service = "PAUSED_LIST",
            keys = {},
            params = data;
        if (data && data.id) {
            service = "PAUSED_CUSTOM_LIST";
            keys["id"] =  data.id;
            params = data.filters;
        }
        return Api.get({
            service,
            params,
            keys
        });
    },
    unassigned(data) {
        let service = "UNASSIGNED_LIST",
            keys = {},
            params = data;
        if (data && data.id) {
            service = "UNASSIGNED_CUSTOM_LIST";
            keys["id"] =  data.id;
            params = data.filters;
        }
        return Api.get({
            service,
            params,
            keys
        });
    },
    summary(data) {
        return Api.get({
            service: "UNASSIGNED_LIST",
            params: {
            },
            keys: {}
        });
    },
    openSummary(data) {
        var params = new FormData();
        params.append('appUid', data.APP_UID);
        params.append('delIndex', data.DEL_INDEX);
        params.append('action', 'todo');

        return axios.post(window.config.SYS_SERVER_AJAX +
            window.config.SYS_URI +
            `appProxy/requestOpenSummary`, params);
    },
    inputdocuments(data) {
        var params = new FormData();
        params.append('appUid', data.APP_UID);
        params.append('delIndex', data.DEL_INDEX);
        params.append('action', "getCasesInputDocuments");

        return axios.post(window.config.SYS_SERVER_AJAX +
            window.config.SYS_URI +
            `cases/cases_Ajax.php?action=getCasesInputDocuments`, params);
    },
    outputdocuments(data) {
        var params = new FormData();
        params.append('appUid', data.APP_UID);
        params.append('delIndex', data.DEL_INDEX);
        params.append('action', "getCasesOutputDocuments");

        return axios.post(window.config.SYS_SERVER_AJAX +
            window.config.SYS_URI +
            `cases/cases_Ajax.php?action=getCasesOutputDocuments`, params);
    },
    casesummary(data) {
        var params = new FormData();
        params.append('appUid', data.APP_UID);
        params.append('delIndex', data.DEL_INDEX);
        params.append('action', "todo");

        return axios.post(window.config.SYS_SERVER_AJAX +
            window.config.SYS_URI +
            `appProxy/getSummary`, params, {
            headers: {
                'Cache-Control': 'no-cache'
            }
        });
    },
    casenotes(data) {
        var params = new FormData();
        params.append('appUid', data.APP_UID);
        params.append('delIndex', data.DEL_INDEX);
        params.append('pro', data.PRO_UID);
        params.append('tas', data.TAS_UID);
        params.append('start', "0");
        params.append('limit', "30");
        return axios.post(window.config.SYS_SERVER_AJAX +
            window.config.SYS_URI +
            `appProxy/getNotesList`, params);
    },
    pendingtask(data) {
        return axios.get(window.config.SYS_SERVER_API +
            '/api/1.0/' +
            window.config.SYS_WORKSPACE +
            '/home/' + data.APP_NUMBER + '/pending-tasks', {
            headers: {
                'Authorization': 'Bearer ' + credentials.accessToken,
                "Accept-Language": window.config.SYS_LANG
            }
        });
    },
    start(dt) {
        var params = new URLSearchParams();
        params.append('action', 'startCase');
        params.append('processId', dt.pro_uid);
        params.append('taskId', dt.task_uid);
        return axios.post(window.config.SYS_SERVER_AJAX +
            window.config.SYS_URI +
            `cases/casesStartPage_Ajax.php`, params);
    },
    open(data) {
        return axios.get(window.config.SYS_SERVER_AJAX +
            window.config.SYS_URI +
            `cases/open?APP_UID=${data.APP_UID}&DEL_INDEX=${data.DEL_INDEX}&action=${data.ACTION}`);
    },
    cases_open(data) {
        return axios.get(window.config.SYS_SERVER_AJAX +
            window.config.SYS_URI +
            `cases/cases_Open?APP_UID=${data.APP_UID}&DEL_INDEX=${data.DEL_INDEX}&action=${data.ACTION}`);
    },
    cancel(data) {
        return Api.update({
            service: "CANCEL_CASE",
            data: {
                reason: data.COMMENT,
                sendMail: data.SEND
            },
            keys: {
                app_uid: data.APP_UID
            }
        });
    },
    actions(data) {
        var params = new URLSearchParams();
        params.append('action', 'getCaseMenu');
        params.append('app_status', 'TO_DO');
        return axios.post(window.config.SYS_SERVER_AJAX +
            window.config.SYS_URI +
            `cases/ajaxListener`, params);
    },
    /**
     * Pause case with endpoint
     * @param {*} data 
     * @returns 
     */
    pauseCase(data) {
        return Api.update({
            service: "PAUSE_CASE",
            data: {
                unpaused_date: data.unpausedDate,
                unpaused_time: data.unpausedTime,
                index: data.DEL_INDEX,
                reason: data.reasonPause,
                sendMail: data.notifyUser
            },
            keys: {
                app_uid: data.APP_UID
            }
        });
    },
    /**
     * Unpause case with endpoint
     * @param {*} data 
     * @returns 
     */
    unpause(data) {
        return Api.update({
            service: "UNPAUSE_CASE",
            data: {
              index: data.DEL_INDEX
            },
            keys: {
                app_uid: data.APP_UID
            }
        });
    },
    getUserReassign(data) {
        return Api.get({
            service: "REASSIGN_USERS",
            data: {},
            keys: {
                task_uid: data.TAS_UID
            }
        });
    },
    /**
     * Get the list of users to re-assign
     * @param {*} data 
     * @returns
     */
    getUsersToReassign(data) {
        return Api.get({
            service: "GET_USERS_TO_REASSIGN",
            data: {},
            keys: {
                task_uid: data.TAS_UID,
                app_uid: data.APP_UID
            }
        });
    },
    reassingCase(data) {
        return Api.update({
            service: "REASSIGN_CASE",
            data: {
                usr_uid_target: data.userSelected,
                del_index: data.DEL_INDEX,
                reason: data.reasonReassign,
                sendMail: data.notifyUser,
                usr_uid_source: window.config.userConfig.usr_uid
            },
            keys: {
                app_uid: data.APP_UID
            }
        });
    },
    /**
     * Reassign a case to a new user
     * @param {*} data
     */
    reassingCaseSupervisor(data) {
        return Api.update({
            service: "REASSIGN_CASE",
            data: {
                usr_uid_target: data.userSelected,
                del_index: data.DEL_INDEX,
                reason: data.reasonReassign,
                sendMail: data.notifyUser,
                usr_uid_source: data.USR_UID
            },
            keys: {
                app_uid: data.APP_UID
            }
        });
    },
    /**
     * Claim case with endpoint
     * @param {*} data 
     * @returns 
     */
    claim(data) {
        return Api.update({
            service: "CLAIM_CASE",
            data: {
                index: data.DEL_INDEX
            },
            keys: {
                app_uid: data.APP_UID
            }
        });
    },
    /**
     * Assign case with endpoint
     * @param {*} data 
     * @returns 
     */
    assignCase(data) {
        return Api.update({
            service: "ASSIGN_CASE",
            data: {
                reason: data.reasonAssign,
                sendMail: data.notifyUser,
                index: data.DEL_INDEX
            },
            keys: {
                app_uid: data.APP_UID,
                usr_uid: data.userSelected  
            }
        });
    },
    /**
     * Verify if the user is Supervisor
     * @param {*} data 
     * @returns 
     */
    getIsSupervisor(data) {
        return Api.get({
            service: "IS_SUPERVISOR",
            keys: {
                app_num: data 
            }
        });
    },
    /**
     * Service to jump a case by it's number
     * @param {object} dt 
     */
    jump(dt) {
        var params = new URLSearchParams();
        params.append('action', 'previusJump');
        params.append('appNumber', dt.APP_NUMBER);
        params.append('actionFromList', dt.ACTION_FROM_LIST);
        return axios.post(window.config.SYS_SERVER_AJAX +
            window.config.SYS_URI +
            `cases/cases_Ajax.php`, params);
    },
    /**
     * Make a search request to the Api service 
     * @param {object} dt - filter parameters
     */
    search(dt) {
        return Api.get({
            service: "SEARCH",
            params: dt,
            keys: {},
            paged: dt.paged
        })
    },
    /**
     * Make a search request to the Api service 
     * @param {object} dt - filter parameters
     */
    debugStatus(dt) {
        return Api.get({
            service: "DEBUG_STATUS",
            params: {},
            keys: {
                prj_uid: dt.PRO_UID
            }
        })
    },
    /**
     * Get debug Vars in ajax service
     * @param {*} data 
     */
    debugVars(data) {
        var params;
        if (data.filter === "all") {
            return axios.get(window.config.SYS_SERVER_AJAX +
                window.config.SYS_URI +
                `cases/debug_vars`);
        } else {
            params = new URLSearchParams();
            params.append('filter', data.filter);
            return axios.post(window.config.SYS_SERVER_AJAX +
                window.config.SYS_URI +
                `cases/debug_vars`, params);
        }
    },
    /**
     * Get triggers debug Vars in ajax service
     * @param {*} data 
     */
    debugVarsTriggers(data) {
        let dc = _.random(0, 10000000000),
            r = _.random(1.0, 100.0);
        return axios.get(window.config.SYS_SERVER_AJAX +
            window.config.SYS_URI +
            `cases/debug_triggers?r=${r}&_dc=${dc}`);
    },
    /**
     * Make a search request to the Api service 
     * @param {object} dt - filter parameters
     */
    listTotalCases(dt) {
      return Api.get({
        service: "LIST_TOTAL_CASES",
        params: {},
        keys: {}
      })
    },
};

export let casesHeader = {
    get() {
        return axios.get(window.config.SYS_SERVER_API +
            '/api/1.0/' +
            window.config.SYS_WORKSPACE +
            '/home/counters', {
            headers: {
                'Authorization': 'Bearer ' + credentials.accessToken,
                "Accept-Language": window.config.SYS_LANG
            }
        });
    }
}; 
