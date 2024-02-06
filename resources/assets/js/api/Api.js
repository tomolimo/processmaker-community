import _ from "lodash";
import axios from "axios";
import { RCBase64 } from '../utils/utils.js'
var base64 = RCBase64();
const urlBase = "{server}/api/1.0/{workspace}{service}";
var credentials = JSON.parse(base64.decode(window.config.SYS_CREDENTIALS));
class Api {
	constructor(services) {
		this.services = services;
	}

    getUrl(keys, service) {
        let k;
        let url = urlBase.replace(/{service}/, this.services[service]);
        let index;
        let reg;

        // eslint-disable-next-line no-restricted-syntax
        for (k in keys) {
            if (Object.prototype.hasOwnProperty.call(keys, k)) {
                url = url.replace(new RegExp(`{${k}}`, "g"), keys[k]);
            }
        }

        index = url.indexOf("?");
        if (index !== -1) {
            reg = new RegExp("{\\w+}", "g");
            if (reg.exec(url)) {
                url = url.substring(0, index);
            }
        }
        return url;
    }
    /**
     * options.method = "post|get"
     * options.service = "ENDPOINT ALIAS"
     * options.keys = "keys for URL"
     * @param {*} options 
     */
    fetch(options) {
        let service = options.service || "",
            data = options.data || {},
            keys = options.keys || {},
            url,
            workspace = window.config.SYS_WORKSPACE,
            server = window.config.SYS_SERVER_API,
            lang = window.config.SYS_LANG,
            method = options.method || "get";
        url = this.getUrl(_.extend(keys, credentials, { server }, { workspace }), service);

        return axios({
            method: method,
            url: url,
            data: data,
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "Cache-Control": "no-cache, must-revalidate",
                "Authorization": `Bearer ` + credentials.accessToken,
                "Accept-Language": lang
            }
        });
    }
    get(options) {
        let service = options.service || "",
            params = options.params || {},
            keys = options.keys || {},
            url,
            workspace = window.config.SYS_WORKSPACE,
            lang = window.config.SYS_LANG,
            server = window.config.SYS_SERVER_API;
        url = this.getUrl(_.extend(keys, credentials, { server }, { workspace }), service);

        return axios({
            method: "get",
            url: url,
            params,
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "Cache-Control": "no-cache, must-revalidate",
                "Authorization": `Bearer ` + credentials.accessToken,
                "Accept-Language": lang
            }
        });
    }
    post(options) {
        let service = options.service || "",
            params = options.params || {},
            data = options.data || {},
            keys = options.keys || {},
            headers = options.headers || {},
            url,
            workspace = window.config.SYS_WORKSPACE,
            lang = window.config.SYS_LANG,
            server = window.config.SYS_SERVER_API;
        url = this.getUrl(_.extend(keys, credentials, { server }, { workspace }), service);

        return axios({
            method: "post",
            url: url,
            params,
            data,
            headers: _.extend({
                "Accept": "application/json",
                "Content-Type": "application/json",
                "Cache-Control": "no-cache, must-revalidate",
                "Authorization": `Bearer ` + credentials.accessToken,
                "Accept-Language": lang
            }, headers)
        });
    }

    postFiles(options) {
        let service = options.service || "",
            params = options.params || {},
            data = options.data || {},
            keys = options.keys || {},
            headers = options.headers || {},
            url,
            workspace = window.config.SYS_WORKSPACE,
            server = window.config.SYS_SERVER_API;
        url = this.getUrl(_.extend(keys, credentials, { server }, { workspace }), service);

        return axios({
            method: "post",
            url: url,
            params,
            data,
            headers: _.extend({
                "Accept": "application/json",
                "Authorization": `Bearer ` + credentials.accessToken
            }, headers)
        });
    }

    delete(options) {
        let service = options.service || "",
            id = options.id || {},
            keys = options.keys || {},
            url,
            workspace = window.config.SYS_WORKSPACE,
            lang = window.config.SYS_LANG,
            server = window.config.SYS_SERVER_API;
        url = this.getUrl(_.extend(keys, credentials, { server }, { workspace }), service);

        return axios({
            method: "delete",
            url: url + id,
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "Cache-Control": "no-cache, must-revalidate",
                "Authorization": `Bearer ` + credentials.accessToken,
                "Accept-Language": lang
            }
        });
    }
    put(options) {
        let service = options.service || "",
            params = options.params || {},
            data = options.data || {},
            id = options.id || {},
            keys = options.keys || {},
            url,
            workspace = window.config.SYS_WORKSPACE,
            lang = window.config.SYS_LANG,
            server = window.config.SYS_SERVER_API;
        url = this.getUrl(_.extend(keys, credentials, { server }, { workspace }), service);

        return axios({
            method: "put",
            url: url,
            params,
            data,
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "Cache-Control": "no-cache, must-revalidate",
                "Authorization": `Bearer ` + credentials.accessToken,
                "Accept-Language": lang
            }
        });
    }
    /**
     * Put action in AXIOS
     * @param {*} options 
     * @returns 
     */
    update(options) {
        let service = options.service || "",
            params = options.params || {},
            data = options.data || {},
            keys = options.keys || {},
            url,
            workspace = window.config.SYS_WORKSPACE,
            lang = window.config.SYS_LANG,
            server = window.config.SYS_SERVER_API;
        url = this.getUrl(_.extend(keys, credentials, { server }, { workspace }), service);

        return axios({
            method: "put",
            url: url,
            params,
            data,
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "Cache-Control": "no-cache, must-revalidate",
                "Authorization": `Bearer ` + credentials.accessToken,
                "Accept-Language": lang
            }
        });
    }
}

export default Api;
