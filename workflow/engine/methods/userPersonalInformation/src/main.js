import Vue from 'vue'
import {BootstrapVue, IconsPlugin} from 'bootstrap-vue'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
import {ClientTable} from 'vue-tables-2'
import {ServerTable} from 'vue-tables-2'
import App from './App.vue';

Vue.config.productionTip = false
Vue.use(BootstrapVue)
Vue.use(IconsPlugin)
Vue.use(ClientTable, {}, false, 'bootstrap4', {});
Vue.use(ServerTable, {}, false, 'bootstrap4', {});

new Vue({
    render: h => h(App),
    methods: {
        translation(text, params) {
            if ("TRANSLATIONS" in window && text in window.TRANSLATIONS) {
                text = window.TRANSLATIONS[text];
                if (params != undefined && "length" in params) {
                    for (let i = 0; i < params.length; i++) {
                        text = text.replace("{" + i + "}", params[i]);
                    }
                }
            }
            return text;
        },
        baseUrl() {
            return "../";
        },
        canEdit() {
            let canEdit = true;
            if ("canEdit" in window) {
                canEdit = window.canEdit;
            }
            return canEdit;
        },
        modeOfForm() {
            let modeOfForm = 1;
            if ("modeOfForm" in window) {
                modeOfForm = window.modeOfForm;
            }
            return modeOfForm;
        }
    }
}).$mount('#app');
