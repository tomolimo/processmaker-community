import axios from "axios";
import ApiInstance from "./Api.js";
import Services from "./Services";
let Api = new ApiInstance( Services );

export let custom = {
    inbox(data) {
        let service = "INBOX_CUSTOM_LIST",
            keys = {},
            params;
        keys["id"] =  data.id,
        params = data.filters;
        return Api.post({
            service,
            data: params,
            keys
        });
    },
    draft(data) {
        let service = "INBOX_CUSTOM_LIST",
            keys = {},
            params;
        service = "DRAFT_CUSTOM_LIST";
        keys["id"] =  data.id;
        params = data.filters;
        return Api.post({
            service,
            data: params,
            keys
        });
    },
    paused(data) {
        let service = "INBOX_CUSTOM_LIST",
            keys = {},
            params;
        service = "PAUSED_CUSTOM_LIST";
        keys["id"] =  data.id;
        params = data.filters;
        return Api.post({
            service,
            data: params,
            keys
        });
    },
    unassigned(data) {
        let service = "INBOX_CUSTOM_LIST",
            keys = {},
            params;
        service = "UNASSIGNED_CUSTOM_LIST";
        keys["id"] =  data.id;
        params = data.filters;
        return Api.post({
            service,
            data: params,
            keys
        });
    }
};