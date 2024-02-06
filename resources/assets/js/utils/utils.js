import _ from "lodash";
export default {
    /**
     * Environment Formats function for full name
     * @param {object} params
     */
    userNameDisplayFormat(params) {
        let aux = "",
            defaultValues = {
                userName: '',
                firstName: '',
                lastName: '',
                format: '(@lastName, @firstName) @userName'
            };
        _.assignIn(defaultValues, params);
        if (defaultValues.userName !== "" || defaultValues.firstName !== "" || defaultValues.lastName !== "") {
            aux = defaultValues.format;
            aux = aux.replace('@userName',defaultValues.userName);
            aux = aux.replace('@firstName',defaultValues.firstName);
            aux = aux.replace('@lastName',defaultValues.lastName);
        }
        return aux;
    },
    /**
     * Parse an url string and prepare an object of the parameters
     * @param {string} url 
     * @returns {object} 
     */
    getAllUrlParams(url) {

        // get query string from url (optional) or window
        var queryString = url ? url.split('?')[1] : window.location.search.slice(1);
      
        // we'll store the parameters here
        var obj = {};
      
        // if query string exists
        if (queryString) {
      
          // stuff after # is not part of query string, so get rid of it
          queryString = queryString.split('#')[0];
      
          // split our query string into its component parts
          var arr = queryString.split('&');
      
          for (var i = 0; i < arr.length; i++) {
            // separate the keys and the values
            var a = arr[i].split('=');
      
            // set parameter name and value (use 'true' if empty)
            var paramName = a[0];
            var paramValue = typeof (a[1]) === 'undefined' ? true : a[1];
      
            // (optional) keep case consistent
            paramName = paramName.toLowerCase();
            if (typeof paramValue === 'string') paramValue = paramValue.toLowerCase();
      
            // if the paramName ends with square brackets, e.g. colors[] or colors[2]
            if (paramName.match(/\[(\d+)?\]$/)) {
      
              // create key if it doesn't exist
              var key = paramName.replace(/\[(\d+)?\]/, '');
              if (!obj[key]) obj[key] = [];
      
              // if it's an indexed array e.g. colors[2]
              if (paramName.match(/\[\d+\]$/)) {
                // get the index value and add the entry at the appropriate position
                var index = /\[(\d+)\]/.exec(paramName)[1];
                obj[key][index] = paramValue;
              } else {
                // otherwise add the value to the end of the array
                obj[key].push(paramValue);
              }
            } else {
              // we're dealing with a string
              if (!obj[paramName]) {
                // if it doesn't exist, create property
                obj[paramName] = paramValue;
              } else if (obj[paramName] && typeof obj[paramName] === 'string'){
                // if property does exist and it's a string, convert it to an array
                obj[paramName] = [obj[paramName]];
                obj[paramName].push(paramValue);
              } else {
                // otherwise add the property
                obj[paramName].push(paramValue);
              }
            }
          }
        }
      
        return obj;
    }
}
export function RCBase64() {
    var RCBase64 = { keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=", encode: function (e) { var t, r, s, o, i, n, a, h = "", c = 0; for (e = this.utf8_encode(e); c < e.length;)t = e.charCodeAt(c++), r = e.charCodeAt(c++), s = e.charCodeAt(c++), o = t >> 2, i = (3 & t) << 4 | r >> 4, n = (15 & r) << 2 | s >> 6, a = 63 & s, isNaN(r) ? n = a = 64 : isNaN(s) && (a = 64), h = h + this.keyStr.charAt(o) + this.keyStr.charAt(i) + this.keyStr.charAt(n) + this.keyStr.charAt(a); return h }, decode: function (e) { var t, r, s, o, i, n, a, h = "", c = 0; for (e = e.replace(/[^A-Za-z0-9\+\/\=]/g, ""); c < e.length;)o = this.keyStr.indexOf(e.charAt(c++)), i = this.keyStr.indexOf(e.charAt(c++)), n = this.keyStr.indexOf(e.charAt(c++)), a = this.keyStr.indexOf(e.charAt(c++)), t = o << 2 | i >> 4, r = (15 & i) << 4 | n >> 2, s = (3 & n) << 6 | a, h += String.fromCharCode(t), 64 !== n && (h += String.fromCharCode(r)), 64 !== a && (h += String.fromCharCode(s)); return h = this.utf8_decode(h) }, utf8_encode: function (e) { e = e.replace(/\r\n/g, "\n"); var t, r, s = ""; for (t = 0; t < e.length; t++)r = e.charCodeAt(t), 128 > r ? s += String.fromCharCode(r) : r > 127 && 2048 > r ? (s += String.fromCharCode(r >> 6 | 192), s += String.fromCharCode(63 & r | 128)) : (s += String.fromCharCode(r >> 12 | 224), s += String.fromCharCode(r >> 6 & 63 | 128), s += String.fromCharCode(63 & r | 128)); return s }, utf8_decode: function (e) { for (var t = "", r = 0, s = 0, o = 0, i = 0; r < e.length;)s = e.charCodeAt(r), 128 > s ? (t += String.fromCharCode(s), r++) : s > 191 && 224 > s ? (o = e.charCodeAt(r + 1), t += String.fromCharCode((31 & s) << 6 | 63 & o), r += 2) : (o = e.charCodeAt(r + 1), i = e.charCodeAt(r + 2), t += String.fromCharCode((15 & s) << 12 | (63 & o) << 6 | 63 & i), r += 3); return t } };
    return RCBase64;
}