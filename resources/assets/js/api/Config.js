import ApiInstance from "./Api.js";
import Services from "./Services";
let Api = new ApiInstance( Services );

export let config = {
    get(data) {
        return Api.get({
            service: "GET_CONFIG",
            keys: data
        });
    },
    post(data) {
        return Api.post({
            service: "CONFIG",
            data: data
        });
    },
    put(data) {
        return Api.put({
            service: "CONFIG",
            data: data
        });
    },
};
