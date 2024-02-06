import axios from "axios";
import ApiInstance from "./Api.js";
import Services from "./Services";
let Api = new ApiInstance(Services);
export let filters = {
    get(data) {
        return Api.get({
            service: "MY_FILTERS",
            params: {
                filter: data.params,
            },
            keys: {},
        });
    },
    post(data) {
        return Api.post({
            service: "POST_MY_FILTERS",
            data,
            keys: {},
        });
    },
    delete(data) {
        return Api.delete({
            service: "DELETE_MY_FILTERS",
            id: data.id,
            keys: {},
        });
    },
    put(data) {
        return Api.put({
            service: "PUT_MY_FILTERS",
            data,
            keys: {
                id: data.id,
            },
        });
    },
    /**
     * Service to generate a jump case URL
     */
    jumpCase() {
        var params = new URLSearchParams();
        params.append("action", "startCase");
        return axios.post(
            window.config.SYS_SERVER_AJAX +
            window.config.SYS_URI +
            `cases/casesStartPage_Ajax.php`,
            params
        );
    },
    /**
     * Service to get the process list
     */
    processList(query) {
        return Api.get({
            service: "PROCESSES",
            params: {
                text: query,
            },
            keys: {},
        });
    },
    /**
     * Service to get the process list
     */
    processListPaged(data) {
        return Api.get({
            service: "PROCESSES",
            params: data,
            keys: {},
        });
    },
    /**
     * Service to get the users list
     */
    userList(query) {
        return Api.get({
            service: "USERS",
            params: {
                text: query,
            },
            keys: {},
        });
    },
    /**
     * Service to get the users list
     */
    taskList(params) {
        return Api.get({
            service: "TASKS",
            params: {
                text: params.query,
                proId: params.proId
            },
            keys: {},
        });
    },
    /**
     * Service to get the categories list
     */
    categories(query) {
        let pr = {
            limit: 15,
            offset: 0
        };
        if (query) {
            pr["name"] = query;
        }
        return Api.get({
            service: "CATEGORIES",
            params: pr,
            keys: {},
        });
    },
    /**
     * Service to get the users list
     */
    userValues(query) {
        return axios.post(
            window.config.SYS_SERVER_AJAX +
            window.config.SYS_URI +
            `cases/casesList_Ajax?actionAjax=userValues&action=search`,
            {
                query,
            }
        );
    },
};
