import axios from "axios";
import Api from "../../../../api/Api";
import Services from "./Services";
import Defaults from "./Mocks/defaults.json";
import { RCBase64 } from '../../../../utils/utils.js'
class caseListApi extends Api {
    constructor(services) {
    // Here, it calls the parent class' constructor with lengths
    // provided for the Polygon's width and height
        super(services, services);
    }
    /**
     * Get the case list
     * @param {object} data 
     * @param {string} module 
     */
    getCaseList(data, module) {
        let service = "CASE_LIST_TODO";
        switch (module) {
            case 'inbox' :
                service = "CASE_LIST_TODO";
                break;
            case 'draft' :
                service = "CASE_LIST_DRAFT";
                break;
            case 'unassigned' :
                service = "CASE_LIST_UNASSIGNED";
                break;
            case 'paused' :
                service = "CASE_LIST_PAUSED";
                break;
        }
        
        return this.get({
            service: service,
            params: data,
            keys: {}
        });
    }
    /**
     * Service delete case list 
     * @param {*} data 
     * @returns 
     */
    deleteCaseList(data) {
        var base64 = RCBase64();
        return axios.delete(
            window.config.SYS_SERVER_API +
            '/api/1.0/' +
            window.config.SYS_WORKSPACE +
            '/caseList/' + data.id, {
            headers: {
                'Authorization': 'Bearer ' + JSON.parse(base64.decode(window.config.SYS_CREDENTIALS)).accessToken,
                "Accept-Language": window.config.SYS_LANG
              }
            }
        );
    }
    /**
     * Service return report tables
     * @param {*} data 
     * @returns 
     */
    reportTables(data) {
        return this.get({
            service: 'REPORT_TABLES',
            params: data,
            keys: {}
        });
    }
    /**
     * Service default columns
     * @param {*} type 
     * @returns 
     */
    getDefault(type){
        return this.get({
            service: 'DEFAULT_COLUMNS',
            keys: {
                type: type
            }
        });
    }
    /**
     * Service create case list
     * @param {*} data 
     * @returns 
     */
    createCaseList(data) {
        return this.post({
            service: "CASE_LIST",
            data: data
        });
    }
    /**
     * Service update case list
     * @param {*} data 
     * @returns 
     */
    updateCaseList(data) {
        return this.put({
            service: "PUT_CASE_LIST",
            keys: {
                id: data.id
            },
            data: data
        });
    }
    /**
     * Service import case list
     * @param {*} data 
     * @returns 
     */
    importCaseList(data) {
        let formData = new FormData();
        formData.append('file_content', data.file);
        if (data.continue) {
            formData.append(data.continue, 'continue');
        }
        return this.post({
            service: "IMPOR_CASE_LIST",
            data: formData,
            headers:{
                'Content-Type': 'multipart/form-data'
            },
        })
    }
}
let api = new caseListApi(Services);

export default api;
