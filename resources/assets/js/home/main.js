import Vue from "vue";
import VueRouter from "vue-router";
import VueI18n from 'vue-i18n';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import { ServerTable, Event, ClientTable } from 'vue-tables-2';
import VtTableHeadingCustom from './../components/vuetable/extends/VtTableHeadingCustom';
import VtSortControl from './../components/vuetable/extends/VtSortControl';
import SettingsPopover from "../components/vuetable/SettingsPopover.vue";
import Sortable from 'sortablejs';
import "@fortawesome/fontawesome-free/css/all.css";
import 'bootstrap/dist/css/bootstrap-grid.css';
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-vue/dist/bootstrap-vue.css';
import VueApexCharts from 'vue-apexcharts';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import VueSimpleContextMenu from 'vue-simple-context-menu';
import VtTableRow from '../components/vuetable/extends/VtTableRow';
import 'vue-simple-context-menu/dist/vue-simple-context-menu.css'

import Home from "./Home";

Vue.use(VueApexCharts);
Vue.use(VueRouter);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
Vue.use(VueI18n);

Vue.use(ServerTable, {}, false, 'bootstrap3', {
    tableHeading: VtTableHeadingCustom,
    sortControl: VtSortControl,
    tableRow: VtTableRow
});
Vue.use(ClientTable, {}, false, 'bootstrap3', {});
Vue.component('settings-popover', SettingsPopover);
Vue.component('apexchart', VueApexCharts);
Vue.component('vue-simple-context-menu', VueSimpleContextMenu);

window.ProcessMaker = {
    apiClient: require('axios')
};

window.ProcessMaker.pluginBase = "/sysworkflow/en/neoclassic/viena/index.php";
window.ProcessMaker.apiClient.defaults.baseURL = '/sysworkflow/en/neoclassic/viena/index.php/api/';
window.ProcessMaker.SYS_SYS = "workflow";
window.ProcessMaker.SYS_LANG = "en";
window.ProcessMaker.SYS_SKIN = "neoclassic";

let messages = {};
messages[config.SYS_LANG] = config.TRANSLATIONS;
const i18n = new VueI18n({
    locale: config.SYS_LANG, // set locale
    messages, // set locale messages
});

// Define routes
const routes = [
    //{ path: "/advanced-search", component: AdvancedSearch }
];

const router = new VueRouter({
    routes, // short for `routes: routes`,
});

new Vue({
    i18n,
    // eslint-disable-line no-new
    el: "#app",
    router,
    render: (h) => h(Home),
});