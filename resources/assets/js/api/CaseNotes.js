import axios from "axios";
import ApiInstance from "./Api.js";
import Services from "./Services";
let Api = new ApiInstance( Services );
export let caseNotes = {
    post(data) {
        var params = new FormData();
        params.append('appUid', data.APP_UID);
        params.append('note_content', data.COMMENT);
        params.append('send_mail', data.SEND_MAIL ? 1 : 0);

        _.each(data.FILES, (f) => {
            params.append("filesToUpload[]", f);
        })

        return Api.postFiles({
            service: "POST_NOTE",
            data: params,
            headers:{
                'Content-Type': 'multipart/form-data'
            },
            keys: {
                app_uid: data.APP_UID
            }
        });
    },
    get(data) {
        var params = new FormData();
        params.append('appUid', data.APP_UID);
        params.append('delIndex', data.DEL_INDEX);
        params.append('pro', data.PRO_UID);
        params.append('tas', data.TAS_UID);
        params.append('start', "0");
        params.append('limit', "30");
        
        return Api.get({
            service: "GET_NOTES",
            params:{
                start: "0",
                limit: "30",
                files: true
            },
            keys: {
                app_uid: data.APP_UID
            }
        });
    }
};
