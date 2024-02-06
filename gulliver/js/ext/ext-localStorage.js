/**
 * @class Ext.state.LocalStorageProvider
 * A Provider implementation which saves and retrieves state via the HTML5 localStorage object.
 * If the browser does not support local storage, there will be no attempt to read the state.
 * @param {Object} config The configuration object
 */
Ext.state.LocalStorageProvider = Ext.extend(Ext.state.Provider, {
    constructor: function(config) {
        Ext.state.LocalStorageProvider.superclass.constructor.call(this);
        Ext.apply(this, config);
        // get all items from localStorage
        this.state = this.readLocalStorage();
    },
    
    readLocalStorage: function() {
        var data = {},
            i,
            name;
        for (i = 0; i <= localStorage.length - 1; i++) {
            name = localStorage.key(i);
            if (name) {
                data[name] = this.decodeValue2(localStorage.getItem(name));
            }
        }
        return data;
    },
    
    set: function(name, value) {
        if (typeof value == "undefined" || value === null) {
            this.clear(name);
            return;
        }
        // write to localStorage
        localStorage.setItem(name, this.encodeValue(value));
        Ext.state.LocalStorageProvider.superclass.set.call(this, name, value);
    },

    // private
    clear: function(name) {
        localStorage.removeItem(name);
        Ext.state.LocalStorageProvider.superclass.clear.call(this, name);
    },
    
    getStorageObject: function() {
        if (Ext.supports.LocalStorage) {
            return window.localStorage;
        }
        return false;
    },
    decodeValue2: function(value) {
        /**
         * a -> Array
         * n -> Number
         * d -> Date
         * b -> Boolean
         * s -> String
         * o -> Object
         * -> Empty (null)
         */
        var re = /^(a|n|d|b|s|o|e)\:(.*)$/,
            matches = re.exec(unescape(value)),
            all,
            type,
            keyValue,
            values,
            vLen,
            v;
            
        if (!matches || !matches[1]) {
            return; // non state
        }
        
        type = matches[1];
        value = matches[2];
        switch (type) {
            case 'e':
                return null;
            case 'n':
                return parseFloat(value);
            case 'd':
                return new Date(Date.parse(value));
            case 'b':
                return (value == '1');
            case 'a':
                all = [];
                if (value != '') {
                    values = value.split('^');
                    vLen   = values.length;

                    for (v = 0; v < vLen; v++) {
                        value = values[v];
                        all.push(this.decodeValue2(value));
                    }
                }
                return all;
           case 'o':
                all = {};
                if(value != ''){
                    values = value.split('^');
                    vLen   = values.length;

                    for (v = 0; v < vLen; v++) {
                        value = values[v];
                        keyValue = value.split('=');
                        all[keyValue[0]] = this.decodeValue2(keyValue[1]);
                    }
                }
                return all;
           default:
                return value;
        }
    }
});