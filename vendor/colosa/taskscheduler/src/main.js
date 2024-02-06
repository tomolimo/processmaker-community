import Vue from 'vue'
import 'core-js/stable';
import App from './App.vue'
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import VtTableHeading from "./components/VtTableHeading.vue"
import VtTableCell from "./components/VtTableCell.vue"
import { ClientTable } from 'vue-tables-2'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

let options = {};
let useVuex = false;
let theme = "bootstrap4";
Vue.use(ClientTable, options, useVuex, theme, {
    tableHeading: VtTableHeading,
    tableCell: VtTableCell
});
Vue.use(BootstrapVue);
Vue.use(IconsPlugin)

new Vue({
    render: h => h(App)
}).$mount('#app')


