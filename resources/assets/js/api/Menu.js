import axios from 'axios';
import { RCBase64 } from '../utils/utils.js'
var base64 = RCBase64();
var credentials = JSON.parse(base64.decode(window.config.SYS_CREDENTIALS));
export let menu = {
    get() {
        return axios.get(
            window.config.SYS_SERVER_API +
            '/api/1.0/' +
            window.config.SYS_WORKSPACE +
            '/home/menu', {
            headers: {
                'Authorization': 'Bearer ' + credentials.accessToken,
                "Accept-Language": window.config.SYS_LANG
              }
        });
        
    },
    getCounters() {
        return axios.get(
            window.config.SYS_SERVER_API +
            '/api/1.0/' +
            window.config.SYS_WORKSPACE +
            '/home/tasks/counter', {
            headers: {
                'Authorization': 'Bearer ' + credentials.accessToken,
                "Accept-Language": window.config.SYS_LANG
              }
        });
    },
    /**
     * Get the counter of a specific task
     * @param {string} task 
     * @returns 
     */
    getTooltip(task) {
        return axios.get(
            window.config.SYS_SERVER_API +
            '/api/1.0/' +
            window.config.SYS_WORKSPACE +
            '/home/'+ task +'/counter', {
            headers: {
                'Authorization': 'Bearer ' + credentials.accessToken,
                "Accept-Language": window.config.SYS_LANG
            }
        });
    },
    /**
     * Get the counter of a specific custom case list
     * @param {Object} data 
     * @returns 
     */
    getTooltipCaseList(data) {
        return axios.get(
            window.config.SYS_SERVER_API +
            '/api/1.0/' +
            window.config.SYS_WORKSPACE +
            '/home/' + data.page + '/counter/caseList/' + data.id, {
                headers: {
                    'Authorization': 'Bearer ' + credentials.accessToken,
                    "Accept-Language": window.config.SYS_LANG
                }
            }
        );
    },
    /**
     * Get the highlight
     * @returns 
     */
    getHighlight() {
        return axios.get(
            window.config.SYS_SERVER_API +
            '/api/1.0/' +
            window.config.SYS_WORKSPACE +
            '/home/tasks/highlight', {
            headers: {
                'Authorization': 'Bearer ' + credentials.accessToken,
                "Accept-Language": window.config.SYS_LANG
              }
        });
    }
};
