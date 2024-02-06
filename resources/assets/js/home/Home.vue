<template>
    <div
        id="home"
        :class="[{ collapsed: collapsed }, { onmobile: isOnMobile }]"
        role="main"
    >
        <div class="demo">
            <b-alert
                :show="dataAlert.dismissCountDown"
                dismissible
                :variant="dataAlert.variant"
                @dismissed="dataAlert.dismissCountDown = 0"
                @dismiss-count-down="countDownChanged"
            >
                {{ dataAlert.message }}
            </b-alert>
            <div class="container">
                <router-view />
            </div>
            <CustomSidebar
                :menu="menu"
                @OnClickSidebarItem="OnClickSidebarItem"
                @onToggleCollapse="onToggleCollapse"
            />
            <div
                v-if="isOnMobile && !collapsed"
                class="sidebar-overlay"
                @click="collapsed = true"
            />
            <component
                v-bind:is="page"
                ref="component"
                :id="pageId"
                :pageUri="pageUri"
                :name="pageName"
                :defaultOption="defaultOption"
                :settings="settings"
                :filters="filters"
                :data="pageData"
                @onSubmitFilter="onSubmitFilter"
                @onRemoveFilter="onRemoveFilter"
                @onUpdatePage="onUpdatePage"
                @onUpdateDataCase="onUpdateDataCase"
                @onOpenCaseDetail="onOpenCaseDetail"
                @onLastPage="onLastPage"
                @onUpdateFilters="onUpdateFilters"
                @cleanDefaultOption="cleanDefaultOption"
                @updateSettings="updateSettings"
            ></component>
        </div>
    </div>
</template>
<script>
import CustomSidebar from "./../components/menu/CustomSidebar";
import CustomSidebarMenuItem from "./../components/menu/CustomSidebarMenuItem";
import MyCases from "./MyCases/MyCases.vue";
import MyDocuments from "./MyDocuments";
import Inbox from "./Inbox/Inbox.vue";
import Paused from "./Paused/Paused.vue";
import Draft from "./Draft/Draft.vue";
import Unassigned from "./Unassigned/Unassigned.vue";
import TaskMetrics from "./TaskMetrics/TaskMetrics.vue";
import BatchRouting from "./BatchRouting";
import CaseDetail from "./CaseDetail";
import XCase from "./XCase";
import TaskReassignments from "./TaskReassignments";
import AdvancedSearch from "./AdvancedSearch/AdvancedSearch.vue";
import LegacyFrame from "./LegacyFrame";
import CustomCaseList from "./CustomCaseList/CustomCaseList.vue"
import utils from "../utils/utils"
import api from "./../api/index";
import eventBus from './EventBus/eventBus'
import _ from "lodash";
export default {
    name: "Home",
    components: {
        CustomSidebar,
        MyCases,
        AdvancedSearch,
        MyDocuments,
        BatchRouting,
        TaskReassignments,
        XCase,
        Inbox,
        Draft,
        Paused,
        Unassigned,
        CaseDetail,
        LegacyFrame,
        TaskMetrics,
        CustomCaseList
    },
    data() {
        return {
            lastPage: "MyCases",
            page: null,
            menu: [],
            dataCase: {},
            selectedItem: {},
            hideToggle: true,
            collapsed: false,
            selectedTheme: "",
            isOnMobile: false,
            sidebarWidth: "260px",
            pageId: null,
            pageName: null,
            pageUri: null,
            filters: null,
            config: {
                id: window.config.userId || "1",
                name: "userConfig",
                setting: {}
            },
            menuMap: {
                CASES_MY_CASES: "MyCases",
                CASES_SENT: "MyCases",
                CASES_SEARCH: "advanced-search",
                CASES_INBOX: "inbox",
                CASES_DRAFT: "draft",
                CASES_PAUSED: "paused",
                CASES_SELFSERVICE: "unassigned",
                CONSOLIDATED_CASES: "batch-routing",
                CASES_TO_REASSIGN: "task-reassignments",
                CASES_FOLDERS: "my-documents",
                TASK_METRICS:"task-metrics"
            },
            defaultOption: window.config.defaultOption || '',
            pageData: {},
            settings: {},
            dataAlert: {
                dismissSecs: 5,
                dismissCountDown: 0,
                message: "",
                variant: "info"
            },
        };
    },
    mounted() {
        let that = this;
        this.onResize();
        this.getUserSettings();
        this.listenerIframe();
        window.setInterval(
            this.getHighlight,
            parseInt(window.config.FORMATS.casesListRefreshTime) * 1000
        );
        // adding eventBus listener
        eventBus.$on('sort-menu', (data) => {
            let page;
            let newData = [];
            data.forEach(item => {
                newData.push({id: item.id});
                if (!page) {
                    page = item.page;
                }
            });
            that.updateSettings({
                data: newData,
                key: "customCaseListOrder",
                page: page,
                type: "normal",
                id: this.id
            });
        });
        eventBus.$on('home-update-page', (data) => {
            that.onUpdatePage(data);
        });
        eventBus.$on('home::sidebar::click-item', (data) => {
            let item = that.getItemMenuByValue("page",data);
            that.OnClickSidebarItem(item);
            this.$router.push(item.item.href);
        });
        eventBus.$on('home::update-settings', (data) => {
            that.updateSettings(data);
        });
        eventBus.$on('home-update-datacase', (data) => {
            that.onUpdateDataCase(data);
        });
    },
    methods: {
        /**
         * Listener for iframes childs
         */
        listenerIframe() {
            let that = this,
                eventMethod = window.addEventListener
                    ? "addEventListener"
                    : "attachEvent",
                eventer = window[eventMethod],
                messageEvent =
                    eventMethod === "attachEvent" ? "onmessage" : "message";

            eventer(messageEvent, function(e) {
                if ( e.data === "redirect=todo" || e.message === "redirect=todo"){
                    that.$router.push('casesListExtJs?action=todo');
                    that.OnClickSidebarItem(that.getItemMenuByValue("page","inbox"));
                }
                if ( e.data === "redirect=MyCases" || e.message === "redirect=MyCases"){
                    that.onUpdateDataCase({
                        APP_UID: that.selectedItem.APP_UID,
                        DEL_INDEX: that.selectedItem.DEL_INDEX,
                        PRO_UID: that.selectedItem.PRO_UID,
                        TAS_UID: that.selectedItem.TAS_UID,
                        APP_NUMBER: that.selectedItem.CASE_NUMBER,
                        FLAG: "SUPERVISING",
                        ACTION:"to_revise"
                    });
                    that.onUpdatePage("case-detail");
                }
                if ( e.data === "update=debugger" || e.message === "update=debugger"){
                    if(that.$refs["component"].updateView){
                        that.$refs["component"].updateView();
                    }
                }
            });
        },
        /**
         * Gets the menu from the server
         */
        getMenu() {
            api.menu
                .get()
                .then((response) => {
                    this.setDefaultCasesMenu(response.data);
                    this.menu = this.mappingMenu(this.setDefaultIcon(response.data));
                    this.getHighlight();
                })
                .catch((e) => {
                    console.error(e);
                });
        },
        /**
         * Gets the user config
         */
        getUserSettings() {
            api.config
                .get({
                    id: this.config.id,
                    name: this.config.name
                })
                .then((response) => {
                    if(response.data && response.data.status === 404) {
                        this.createUserSettings();
                    } else if (response.data) {
                        this.config = response.data;
                        this.getMenu();
                    }
                })
                .catch((e) => {
                    console.error(e);
                });
        },
        /**
         * Creates the user config service
         */
        createUserSettings() {
            api.config
                .post(this.config)
                .then((response) => {
                    if (response.data) {
                        this.config = response.data;
                        this.getMenu();
                    }
                })
                .catch((e) => {
                    console.error(e);
                });
        },
        /**
         * Update the user config service
         * @param {object} params
         */
        updateSettings (params){
            if (params.type === "custom") {
                if (!this.config.setting[params.page]) {
                    this.config.setting[params.page] = {};
                }
                if (!this.config.setting[params.page]["customCaseList"]) {
                    this.config.setting[params.page]["customCaseList"] = {};
                }
                if (!this.config.setting[params.page].customCaseList[params.id]) {
                    this.config.setting[params.page].customCaseList[params.id] = {}
                }
                this.config.setting[params.page].customCaseList[params.id][params.key] = params.data;
            } else {
                if (!this.config.setting[params.page]) {
                    this.config.setting[params.page] = {};
                }
                this.config.setting[params.page][params.key] = params.data;
            }
            api.config
                .put(this.config)
                .then((response) => {
                    if (response.data) {
                        //TODO success response
                    }
                })
                .catch((e) => {
                    console.error(e);
                });
            
        },
        /**
         * Set default cases menu option
         */
        setDefaultCasesMenu(data) {
            let params,
                menuItem = _.find(data, function(o) {
                return o.id === window.config._nodeId;
            });
            if (menuItem && menuItem.href) {
                this.page = this.menuMap[window.config._nodeId] || "MyCases";
                this.$router.push(menuItem.href);
            } else {
                this.page = "MyCases";
            }
            params = utils.getAllUrlParams(this.defaultOption);
            if (params.action === 'mycases' && params.filter === '') { 
                this.showAlert(this.$i18n.t("ID_NO_PERMISSION_NO_PARTICIPATED_CASES"));
            }
            this.settings = this.config.setting[this.page];
            this.lastPage = this.page;
        },
        /**
         * Do a mapping of vue view for menus
         * @returns array
         */
        mappingMenu(data) {
            var i,
                j,
                that = this,
                newData = data,
                auxId;
            for (i = 0; i < data.length; i += 1) {
                auxId = data[i].page || "";
                if (auxId !== "" && this.menuMap[auxId]) {
                    newData[i].page = this.menuMap[auxId];
                } else if (newData[i].href) {
                    newData[i].page  = "LegacyFrame";
                }
                // Tasks group need pie chart icon
                if (data[i].header && data[i].id === "FOLDERS") {
                    data[i] = {
                        component: CustomSidebarMenuItem,
                        props: {
                            isCollapsed: this.collapsed? true: false,
                            item: {
                                href: "/task-metrics/" + data[i].id,
                                icon: "fas fa-chart-pie",
                                id: "TASK_METRICS",
                                page: "task-metrics",
                                title: data[i].title,
                                header: data[i] && !data[i].permission? true : null,
                                specialType: data[i] && data[i].permission? "header" : null
                            }
                        }
                    };

                }
                if (data[i].customCasesList)  {
                    data[i]["child"] = this.sortCustomCasesList(
                        data[i].customCasesList,
                        this.config.setting[data[i]["page"]] &&
                            this.config.setting[data[i]["page"]].customCaseListOrder
                            ? this.config.setting[data[i]["page"]].customCaseListOrder
                            : []
                    );
                    data[i]["sortable"] = data[i].customCasesList.length > 1;
                    data[i]["sortIcon"] = "gear-fill";
                    data[i]['highlight'] = false;
                    data[i] = {
                        component: CustomSidebarMenuItem,
                        props: {
                            isCollapsed: this.collapsed? true: false,
                            item: data[i],
                            showOneChild: true
                        }
                    };
                }
            }
            return newData;
        },
        /**
         * Sort the custom case list menu items
         * @param {array} list
         * @param {array} ref
         * @returns {array}
         */
        sortCustomCasesList(list, ref) {
            let item,
                newList = [],
                temp = [];
            if (ref && ref.length) {
                ref.forEach(function (menu) {
                    item = list.find(x => x.id === menu.id);
                    if (item) {
                        newList.push(item);
                    }
                })
            } else {
                return list;
            }
            temp = list.filter(this.comparerById(newList));
            return  [...newList, ...temp];

        },
        /**
         * Util to compare an array by id
         * @param {array} otherArray
         * @returns {object}
         */
        comparerById(otherArray){
            return function(current){
                return otherArray.filter(function(other){
                    return other.id == current.id
                }).length == 0;
            }
        },
        /**
         * Set a default icon if the item doesn't have one
         */
        setDefaultIcon(data){
            var i,
                auxData = data;
            for (i = 0; i < auxData.length; i += 1) {
                if (auxData[i].icon !== undefined && auxData[i].icon === "") {
                    auxData[i].icon = "fas fa-bars";
                }
            }
            return auxData;
        },
        /**
         * Clean the default option property
         */
        cleanDefaultOption() {
            this.defaultOption = "";
        },
        /**
         * Page view factory
         * @param {object} item
         */
        pageFactory(item){
            this.filters = [];
            this.lastPage = this.page;
            this.page = item.item.page;
            this.filters = item.item.filters;
            this.pageId = item.item.id;
            this.pageUri = item.item.href;
            this.pageName = item.item.title;
            this.settings = this.config.setting[this.page];
            this.pageData = {
                pageUri: item.item.pageUri,
                pageParent: item.item.page,
                pageName: item.item.title,
                pageIcon: item.item.icon,
                customListId: item.item.id, 
                color: item.item.colorScreen,
                settings: this.settings
            }
            //Custom Cases List
            if (!this.menuMap[item.item.id] && item.item.page !== "LegacyFrame" && item.item.page !== "advanced-search" ) {
                this.page = "custom-case-list";
                if (this.config.setting[item.item.page] && this.config.setting[item.item.page]["customCaseList"]) {
                    this.pageData.settings = this.config.setting[item.item.page]["customCaseList"][item.item.id];
                    this.settings = this.pageData.settings;
                } else {
                    this.pageData.settings  = {};
                }
            }
            if (this.page === this.lastPage
                && this.$refs["component"]
                && this.$refs["component"].updateView) {
                this.$refs["component"].updateView(this.pageData);
            }
        },
        /**
         * Click sidebar menu item handler
         * @param {object} item
         */
        OnClickSidebarItem(item) {
            this.pageFactory(item);
        },
        setCounter() {
            let that = this,
                counters = [];
            if (that.menu.length > 0) {
                api.menu
                .getCounters()
                .then((response) => {
                    var i,
                        j,
                        data = response.data;
                    that.counters = data;
                    for (i = 0; i < that.menu.length; i += 1) {
                        if (that.menu[i].id && data[that.menu[i].id]) {
                            that.menu[i].badge.text = data[that.menu[i].id];
                        }
                    }
                })
                .catch((e) => {
                    console.error(e);
                });
            }
        },
        onResize() {
            if (window.innerWidth <= 767) {
                this.isOnMobile = true;
                this.collapsed = true;
            } else {
                this.isOnMobile = false;
                this.collapsed = false;
            }
        },
        /**
         * Toggle sidebar handler
         * @param {Boolean} collapsed - if sidebar is collapsed true|false
         *
         */
        onToggleCollapse(collapsed) {
            this.collapsed = collapsed;
        },
        /**
         * Handle if filter was submited
         */

        onSubmitFilter(data) {
            this.addMenuSearchChild(data);
        },
        /**
         * Add a child submenu to search menu
         * @param {object} data - cnotains theinfo to generate a menu
         */
        addMenuSearchChild(data) {
            let newMenu = this.menu;
            let advSearch = _.find(newMenu, function(o) {
                return o.id === "CASES_SEARCH";
            });
            if (advSearch) {
                const index = advSearch.child.findIndex(function(o) {
                    return o.id === data.id;
                });
                if (index !== -1) {
                    advSearch.child[index].filters = data.filters;
                } else {
                    if (!advSearch.hasOwnProperty("child")) {
                        advSearch["child"] = [];
                    }
                    advSearch.child.push({
                        filters: data.filters,
                        href: "/advanced-search/" + data.id,
                        title: data.name,
                        icon: "fas fa-circle",
                        id: data.id,
                        page: "advanced-search",
                    });
                }
            }
        },
        onRemoveFilter(id) {
            this.removeMenuSearchChild(id);
            this.resetSettings();
        },
        resetSettings() {
            this.page = "advanced-search";
            this.pageId = null;
            this.pageName = null;
            this.filters = [];
        },
        onUpdatePage(page) {
            this.lastPage = this.page;
            this.page = page;
            if (this.$refs["component"] && this.$refs["component"].updateView) {
                this.$refs["component"].updateView();
            }
        },
        onUpdateDataCase(data) {
            this.dataCase = data;
        },
        onOpenCaseDetail(item){
            this.selectedItem = item;
        },
        onLastPage() {
            this.page = this.lastPage;
            this.lastPage = "MyCases";
        },
        removeMenuSearchChild(id) {
            let newMenu = this.menu;
            let advSearch = _.find(newMenu, function(o) {
                return o.id === "CASES_SEARCH";
            });
            if (advSearch) {
                const index = advSearch.child.findIndex(function(o) {
                    return o.id === id;
                });
                if (index !== -1) advSearch.child.splice(index, 1);
            }
        },
        /**
         * Update filters handler
         */
        onUpdateFilters(filters) {
            this.filters = filters;
        },
        /**
         * Service to get Highlight  
         */
        getHighlight() {
            let that = this;
            if (that.menu.length > 0) {
            api.menu
            .getHighlight()
            .then((response) => {
                var i,
                    dataHighlight = [];
                for (i = 0; i < response.data.length; i += 1) {
                    if (response.data[i].highlight) {
                        dataHighlight.push({
                            id: that.menuMap[response.data[i].item],
                            highlight: response.data[i].highlight
                        });
                    }
                }
                eventBus.$emit('highlight', dataHighlight);
            })
            .catch((e) => {
                console.error(e);
            });
            }
        },
        /**
         * Search in menu Items by value, return the item
         * @param {string} key - Key for search in object
         * @param {string} value - value for search in key
         */
        getItemMenuByValue(key, value) {
            let obj = _.find(this.menu, function(o) {
                if(o[key] == value){
                    return true;
                }
                if(o.component){
                  return o.props.item[key] == value;
                }
                return o[key] == value; 
            });
            if(obj.component){
              return obj.props;
            }
            if(obj.page){
                return {item : obj};
            }
            return obj;
        },
        /**
         * Show the alert message
         * @param {string} message - message to be displayen in the body
         * @param {string} type - alert type
         */
        showAlert(message, type) {
            this.dataAlert.message = message;
            this.dataAlert.variant = type || "info";
            this.dataAlert.dismissCountDown = this.dataAlert.dismissSecs;
        },
        /**
         * Updates the alert dismiss value to update
         * dismissCountDown and decrease
         * @param {mumber}
         */
        countDownChanged(dismissCountDown) {
            this.dataAlert.dismissCountDown = dismissCountDown;
        },
    }
};
</script>

<style lang="scss">
#home {
    padding-left: 260px;
    transition: 0.3s;
}
#home.collapsed {
    padding-left: 50px;
}
#home.onmobile {
    padding-left: 50px;
}

.container {
    max-width: 1500px;
}
</style>
