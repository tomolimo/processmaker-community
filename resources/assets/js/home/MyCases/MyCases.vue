<template>
    <div id="v-mycases" ref="v-mycases" class="v-container-mycases">
        <b-alert
            :show="dataAlert.dismissCountDown"
            dismissible
            :variant="dataAlert.variant"
            @dismissed="dataAlert.dismissCountDown = 0"
            @dismiss-count-down="countDownChanged"
        >
            {{ dataAlert.message }}
        </b-alert>
        <button-fleft :data="newCase"></button-fleft>
        <MyCasesFilter
            :filters="filters"
            :title="title"
            :random="random"
            :icon="filterHeaderObject.icon"
            @onRemoveFilter="onRemoveFilter"
            @onUpdateFilters="onUpdateFilters"
        />
        <header-counter :data="headers"> </header-counter>
        <modal-new-request ref="newRequest"></modal-new-request>
        <settings-popover
            :options="formatColumnSettings(options.headings)"
            target="pm-dr-column-settings"
            @onUpdateColumnSettings="onUpdateColumnSettings"
            :key="random + 1"
            :selected="formatColumnSelected(columns)"
        />
        <v-server-table
            :data="tableData"
            :columns="columns"
            :options="options"
            ref="vueTable"
            name="mycases"
            @row-click="onRowClick"
            :key="random"
        >
            <div slot="detail" slot-scope="props">
                <div class="btn-default" @click="openCaseDetail(props.row)">
                    <i class="fas fa-info-circle"></i>
                </div>
            </div>
            <div slot="case_number" slot-scope="props">
                {{ props.row.CASE_NUMBER }}
            </div>
            <div slot="thread_title" slot-scope="props">
                <ThreadTitleCell :data="props.row.THREAD_TITLE" />
            </div>
            <div slot="process_category" slot-scope="props">
                {{ props.row.PROCESS_CATEGORY }}
            </div>
            <div slot="process_name" slot-scope="props">
                {{ props.row.PROCESS_NAME }}
            </div>
            <div slot="pending_taks" slot-scope="props">
                <GroupedCell :data="props.row.PENDING_TASKS" />
            </div>
            <div slot="status" slot-scope="props">{{ props.row.STATUS }}</div>
            <div slot="start_date" slot-scope="props">
                {{ props.row.START_DATE }}
            </div>
            <div slot="finish_date" slot-scope="props">
                {{ props.row.FINISH_DATE }}
            </div>
            <div slot="duration" slot-scope="props">
                {{ props.row.DURATION }}
            </div>
            <div slot="actions" slot-scope="props">
                <div
                    class="btn-default"
                    v-bind:style="{ color: props.row.MESSAGE_COLOR }"
                    @click="openComments(props.row)"
                >
                    <span class="fas fa-comments"></span>
                </div>
            </div>
        </v-server-table>
        <ModalComments
            ref="modal-comments"
            @postNotes="onPostNotes"
        ></ModalComments>
        <ModalClaimCase ref="modal-claim-case" @claimCatch="claimCatch"></ModalClaimCase>
    </div>
</template>

<script>
import HeaderCounter from "../../components/home/HeaderCounter.vue";
import ButtonFleft from "../../components/home/ButtonFleft.vue";
import ModalNewRequest from "../ModalNewRequest.vue";
import ModalClaimCase from "../modal/ModalClaimCase.vue";
import MyCasesFilter from "../../components/search/MyCasesFilter";
import ModalComments from "../modal/ModalComments.vue";
import GroupedCell from "../../components/vuetable/GroupedCell.vue";
import ThreadTitleCell from "../../components/vuetable/ThreadTitleCell.vue"
import api from "../../api/index";
import utils from "../../utils/utils";
import defaultMixins from "./defaultMixins";
import { Event } from "vue-tables-2";

export default {
    name: "MyCases",
    mixins: [defaultMixins],
    components: {
        MyCasesFilter,
        HeaderCounter,
        ButtonFleft,
        ModalNewRequest,
        GroupedCell,
        ModalComments,
        ModalClaimCase,
        ThreadTitleCell,
    },
    props: ["defaultOption", "settings"],
    data() {
        let that = this;
        return {
            dataAlert: {
                dismissSecs: 5,
                dismissCountDown: 0,
                message: "",
                variant: "info",
            },
            metrics: [],
            title: this.$i18n.t("ID_MY_CASES"),
            filter: "CASES_INBOX",
            allView: [],
            filterHeader: "STARTED",
            filterHeaderObject: {
                icon:"fas fa-inbox"
            },
            headers: [],
            columMap: {
                case_number: "APP_NUMBER",
                thread_title: "DEL_TITLE",
                process_name: "PRO_TITLE",
            },
            random: _.random(0,1000000000),
            newCase: {
                title: this.$i18n.t("ID_NEW_CASE"),
                class: "btn-success",
                onClick: () => {
                    this.$refs["newRequest"].show();
                },
            },
            filters:
                this.settings && this.settings.filters
                    ? this.settings.filters
                    : {},
            columns:
                this.settings && this.settings.columns
                    ? this.settings.columns
                    : [
                          "case_number",
                          "process_name",
                          "thread_title",
                          "pending_taks",
                          "status",
                          "start_date",
                          "finish_date",
                          "duration",
                          "actions",
                      ],
            tableData: [],
            options: {
                filterable: false,
                perPageValues: [],
                pagination: { 
                    chunk: 3,
                    nav: 'scroll',
                    edge: true
                },
                headings: {
                    case_number: this.$i18n.t("ID_MYCASE_NUMBER"),
                    process_category: this.$i18n.t("ID_CATEGORY_PROCESS"),
                    process_name: this.$i18n.t("ID_PROCESS_NAME"),
                    thread_title: this.$i18n.t('ID_CASE_THREAD_TITLE'),
                    pending_taks: this.$i18n.t("ID_PENDING_TASKS"),
                    status: this.$i18n.t("ID_CASESLIST_APP_STATUS"),
                    start_date: this.$i18n.t("ID_START_DATE"),
                    finish_date: this.$i18n.t("ID_FINISH_DATE"),
                    duration: this.$i18n.t("ID_DURATION"),
                    actions: "",
                },
                texts: {
                    count: this.$i18n.t("ID_SHOWING_FROM_RECORDS_COUNT"),
                    first: "<<",
                    last: ">>",
                    filter: this.$i18n.t("ID_FILTER") + ":",
                    limit: this.$i18n.t("ID_RECORDS") + ":",
                    page: this.$i18n.t("ID_PAGE") + ":",
                    noResults: this.$i18n.t("ID_NO_MATCHING_RECORDS"),
                },
                selectable: {
                    mode: "single",
                    only: function(row) {
                        return true;
                    },
                    selectAllMode: "page",
                    programmatic: false,
                },
                sortable: ["case_number"],
                orderBy:
                    this.settings && this.settings.orderBy
                        ? this.settings.orderBy
                        : {},
                requestFunction(data) {
                    return this.$parent.$parent.getCasesForVueTable(data);
                },
                settings: {
                    actions: {
                        class: "fas fa-cog",
                        id: "pm-dr-column-settings",
                        events: {
                            click() {
                                that.$root.$emit(
                                    "bv::show::popover",
                                    "pm-dr-column-settings"
                                );
                            },
                        },
                    },
                },
            },
            translations: null,
            pmDateFormat: window.config.FORMATS.dateFormat,
            clickCount: 0,
            singleClickTimer: null,
            statusTitle: {
                ON_TIME: this.$i18n.t("ID_IN_PROGRESS"),
                OVERDUE: this.$i18n.t("ID_TASK_OVERDUE"),
                DRAFT: this.$i18n.t("ID_IN_DRAFT"),
                PAUSED: this.$i18n.t("ID_PAUSED"),
                UNASSIGNED: this.$i18n.t("ID_UNASSIGNED"),
            },
            clearSortState: this.settings && this.settings.orderBy && this.settings.orderBy.column,
        };
    },
    mounted() {
        let that = this;
        this.getHeaders();
        this.openDefaultCase();
        // force to open start cases modal
        // if the user has start case as a default case menu option
        if (window.config._nodeId === "CASES_START_CASE") {
            this.$refs["newRequest"].show();
        }
        // define sort event
        Event.$on('vue-tables.mycases.sorted', function (data) {
            that.$emit("updateSettings", {
                data: data,
                key: "orderBy",
                page: "MyCases",
                type: "normal",
                id: this.id
            });
        });
        Event.$on('clearSortEvent', this.clearSort);
    },
    watch: {
        columns: function (val) {
            this.$emit("updateSettings", {
                data: val,
                key: "columns",
                page: "MyCases",
                type: "normal",
                id: this.id
            });
        }
    },
    computed: {
        /**
         * Build our ProcessMaker apiClient
         */
        ProcessMaker() {
            return window.ProcessMaker;
        },
    },
    updated() {},
    beforeCreate() {},
    methods: {
        /**
         * Open a case when the component was mounted
         */
        openDefaultCase() {
            let params;
            if (this.defaultOption) {
                params = utils.getAllUrlParams(this.defaultOption);
                if (params && params.openapplicationuid) {
                    this.onUpdateFilters({
                        params: [
                            {
                                fieldId: "caseNumber",
                                filterVar: "caseNumber",
                                label: "",
                                options: [],
                                value: params.openapplicationuid,
                                autoShow: false,
                            },
                        ],
                    refresh: false,
                    });
                    this.$emit("cleanDefaultOption");
                    api.cases.pendingtask({APP_NUMBER:params.openapplicationuid}).then((response) => {
                        if (response.data && response.data[0] && response.data[0]['USR_ID'] == 0) {
                            this.claimCase(response.data[0]);
                        }
                    });
                }
            }
        },
        /**
         * Row click event handler
         * @param {object} event
         */
        onRowClick(event) {
            let self = this;
            self.clickCount += 1;
            if (self.clickCount === 1) {
                self.singleClickTimer = setTimeout(function() {
                    self.clickCount = 0;
                }, 400);
            } else if (self.clickCount === 2) {
                clearTimeout(self.singleClickTimer);
                self.clickCount = 0;
                self.openCaseDetail(event.row);
            }
        },
        /**
         * Open selected case
         *
         * @param {object} item
         */
        openCase(item) {
            this.$emit("onUpdateDataCase", {
                APP_UID: item.APP_UID,
                DEL_INDEX: item.DEL_INDEX,
                PRO_UID: item.PRO_UID,
                TAS_UID: item.TAS_UID,
                ACTION: "todo",
            });
            this.$emit("onUpdatePage", "XCase");
        },
        /**
         * Open case detail
         *
         * @param {object} item
         */
        openCaseDetail(item) {
            let that = this;
            that.$emit("onOpenCaseDetail", item);
            api.cases.open(_.extend({ ACTION: "todo" }, item)).then(() => {
                api.cases
                    .cases_open(_.extend({ ACTION: "todo" }, item))
                    .then(() => {
                        that.$emit("onUpdateDataCase", {
                            APP_UID: item.APP_UID,
                            DEL_INDEX: item.DEL_INDEX,
                            PRO_UID: item.PRO_UID,
                            TAS_UID: item.TAS_UID,
                            APP_NUMBER: item.CASE_NUMBER,
                            FLAG: this.filterHeader,
                            ACTION:
                                that.filterHeader === "SUPERVISING"
                                    ? "to_revise"
                                    : "todo",
                        });
                        that.$emit("onUpdatePage", "case-detail");
                    });
            });
        },
        /**
         * Get Cases Headers from BE
         */
        getHeaders() {
            let that = this;
            api.casesHeader.get().then((response) => {
                that.headers = that.formatCasesHeaders(response.data);
                if (that.headers[0]) {
                    that.title = that.headers[0].title;
                }
                that.setFilterHeader();
            });
        },
        /**
         * Set a filter in the header from Default Cases Menu option
         */
        setFilterHeader() {
            let header = window.config._nodeId,
                filters = this.headers,
                filter,
                i,
                params;
            if (header === "CASES_TO_REVISE") {
                filter = "SUPERVISING";
            }
            params = utils.getAllUrlParams(window.config.defaultOption);
            if (params.action === 'mycases' && params.filter !== '') {
                if (params.filter === 'inprogress') {
                    filter = 'IN_PROGRESS'
                }
                if (params.filter === 'completed') {
                    filter = 'COMPLETED'
                }
            }
            for (i = 0; i < filters.length; i += 1) {
                if (filters[i].item === filter) {
                    filters[i].onClick(filters[i]);
                }
            }
        },
        /**
         * Get cases data by header
         */
        getCasesForVueTable(data) {
            let that = this,
                dt,
                paged,
                limit = data.limit,
                start = data.page === 1 ? 0 : limit * (data.page - 1),
                filters = {},
                sort = "";
            filters = {
                filter: that.filterHeader,
                limit: limit,
                offset: start
            };
            _.forIn(this.filters, function(item, key) {
                if (filters && item.value) {
                    filters[item.filterVar] = item.value;
                }
            });
            sort = that.prepareSortString(data);
            if (sort) {
                filters["sort"] = sort;
            }
            return new Promise((resolutionFunc, rejectionFunc) => {
                api.cases
                    .myCases(filters)
                    .then((response) => {
                        dt = that.formatDataResponse(response.data.data);
                        resolutionFunc({
                            data: dt,
                            count: response.data.total,
                        });
                    })
                    .catch((e) => {
                        rejectionFunc(e);
                    });
            });
        },
        /**
         * Prepare sort string to be sended in the service
         * @param {object} data
         * @returns {string}
         */
        prepareSortString(data) {
            let sort = "";
            if (data.orderBy && this.columMap[data.orderBy]) {
                sort = `${this.columMap[data.orderBy]},${
                    data.ascending === 1 ? "ASC" : "DESC"
                }`;
            }
            return sort;
        },
        /**
         * Format Response API TODO to grid inbox and columns
         */
        formatDataResponse(response) {
            let that = this,
                data = [];
            _.forEach(response, (v) => {
                data.push({
                    CASE_NUMBER: v.APP_NUMBER,
                    THREAD_TITLE: v.THREAD_TITLES,
                    PROCESS_NAME: v.PRO_TITLE,
                    PROCESS_NAME: v.PRO_TITLE,
                    PROCESS_CATEGORY: v.CATEGORY,
                    STATUS: this.$i18n.t("ID_CASES_STATUS_"+ v.APP_STATUS.toUpperCase()),
                    START_DATE: v.APP_CREATE_DATE_LABEL || "",
                    FINISH_DATE: v.APP_FINISH_DATE_LABEL || "",
                    PENDING_TASKS: that.formantPendingTask(
                        v.PENDING,
                        v.APP_STATUS
                    ),
                    DURATION: v.DURATION,
                    DEL_INDEX: v.DEL_INDEX,
                    APP_UID: v.APP_UID,
                    PRO_UID: v.PRO_UID,
                    TAS_UID: v.TAS_UID,
                    MESSAGE_COLOR: v.CASE_NOTES_COUNT > 0 ? "black" : "silver",
                });
            });
            return data;
        },
        /**
         * Format data for pending task.
         */
        formantPendingTask(data, status) {
            var i,
                userDataFormat,
                dataFormat = [];
            for (i = 0; i < data.length; i += 1) {
                userDataFormat = utils.userNameDisplayFormat({
                    userName: data[i].user_tooltip.usr_username || "",
                    firstName: data[i].user_tooltip.usr_firstname || "",
                    lastName: data[i].user_tooltip.usr_lastname || "",
                    format: window.config.FORMATS.format || null,
                });
                dataFormat.push({
                    TAS_NAME: data[i].tas_title,
                    STATUS: data[i].tas_color,
                    DELAYED_TITLE: this.delayedTitle(data[i], status),
                    DELAYED_MSG:
                        data[i].tas_status === "OVERDUE" &&
                        status !== "COMPLETED"
                            ? data[i].delay
                            : "",
                    AVATAR:
                        userDataFormat !== ""
                            ? window.config.SYS_SERVER_AJAX +
                              window.config.SYS_URI +
                              `users/users_ViewPhotoGrid?pUID=${data[i].user_id}`
                            : "",
                    USERNAME:
                        userDataFormat !== ""
                            ? userDataFormat
                            : this.$i18n.t("ID_UNASSIGNED"),
                    POSITION: data[i].user_tooltip.usr_position,
                    EMAIL: data[i].user_tooltip.usr_email,
                    UNASSIGNED: userDataFormat !== "" ? true : false,
                });
            }
            return dataFormat;
        },
        /**
         * Prepare the delayed title
         * @param {object} data
         * @param {string} status
         * @returns {string}
         */
        delayedTitle(data, status) {
            let title = "";
            if (status === "COMPLETED") {
                title = this.$i18n.t("ID_COMPLETED") + ": ";
                title +=
                    data.tas_status === "ON_TIME"
                        ? this.$i18n.t("ID_ON_TIME")
                        : this.$i18n.t("ID_TASK_OVERDUE");
            } else {
                title =
                    data.tas_status === "OVERDUE"
                        ? this.$i18n.t("ID_DELAYED") + ":"
                        : this.statusTitle[data.tas_status];
            }
            return title;
        },
        /**
         * Convert string to date format
         *
         * @param {string} value
         * @return {date} myDate
         */
        convertDate(value) {
            myDate = new Date(1900, 0, 1, 0, 0, 0);
            try {
                if (!isNaN(Date.parse(value))) {
                    var myArray = value.split(" ");
                    var myArrayDate = myArray[0].split("-");
                    if (myArray.length > 1) {
                        var myArrayHour = myArray[1].split(":");
                    } else {
                        var myArrayHour = new Array("0", "0", "0");
                    }
                    var myDate = new Date(
                        myArrayDate[0],
                        myArrayDate[1] - 1,
                        myArrayDate[2],
                        myArrayHour[0],
                        myArrayHour[1],
                        myArrayHour[2]
                    );
                }
            } catch (err) {
                throw new Error(err);
            }
            return myDate;
        },
        /**
         * Get a format for specific date
         *
         * @param {string} d
         * @return {string} dateToConvert
         */
        dateFormatCases(d) {
            let dateToConvert = d;
            const stringToDate = this.convertDate(dateToConvert);
            if (this.pmDateFormat === "Y-m-d H:i:s") {
                dateToConvert = dateFormat(stringToDate, "yyyy-mm-dd HH:MM:ss");
            } else if (this.pmDateFormat === "d/m/Y") {
                dateToConvert = dateFormat(stringToDate, "dd/mm/yyyy");
            } else if (this.pmDateFormat === "m/d/Y") {
                dateToConvert = dateFormat(stringToDate, "mm/dd/yyyy");
            } else if (this.pmDateFormat === "Y/d/m") {
                dateToConvert = dateFormat(stringToDate, "yyyy/dd/mm");
            } else if (this.pmDateFormat === "Y/m/d") {
                dateToConvert = dateFormat(stringToDate, "yyyy/mm/dd");
            } else if (this.pmDateFormat === "F j, Y, g:i a") {
                dateToConvert = dateFormat(
                    stringToDate,
                    "mmmm d, yyyy, h:MM tt"
                );
            } else if (this.pmDateFormat === "m.d.y") {
                dateToConvert = dateFormat(stringToDate, "mm.dd.yy");
            } else if (this.pmDateFormat === "j, n, Y") {
                dateToConvert = dateFormat(stringToDate, "d,m,yyyy");
            } else if (this.pmDateFormat === "D M j G:i:s T Y") {
                dateToConvert = dateFormat(
                    stringToDate,
                    "ddd mmm d HH:MM:ss Z yyyy"
                );
            } else if (this.pmDateFormat === "M d, Y") {
                dateToConvert = dateFormat(stringToDate, "mmm dd, yyyy");
            } else if (this.pmDateFormat === "m D, Y") {
                dateToConvert = dateFormat(stringToDate, "mm ddd, yyyy");
            } else if (this.pmDateFormat === "D d M, Y") {
                dateToConvert = dateFormat(stringToDate, "ddd dd mmm, yyyy");
            } else if (this.pmDateFormat === "D M, Y") {
                dateToConvert = dateFormat(stringToDate, "ddd mmm, yyyy");
            } else if (this.pmDateFormat === "d M, Y") {
                dateToConvert = dateFormat(stringToDate, "dd mmm, yyyy");
            } else if (this.pmDateFormat === "d m, Y") {
                dateToConvert = dateFormat(stringToDate, "dd mm, yyyy");
            } else if (this.pmDateFormat === "d.m.Y") {
                dateToConvert = dateFormat(stringToDate, "mm.dd.yyyy");
            } else {
                dateToConvert = dateFormat(
                    stringToDate,
                    'dd "de" mmmm "de" yyyy'
                );
            }
            return dateToConvert;
        },
        /**
         * Format Response from HEADERS
         * @param {*} response
         */
        formatCasesHeaders(response) {
            let data = [],
                that = this,
                info = {
                    STARTED: {
                        icon: "fas fa-inbox",
                        class: "btn-primary",
                    },
                    COMPLETED: {
                        icon: "fas fa-check-square",
                        class: "btn-success",
                    },
                    IN_PROGRESS: {
                        icon: "fas fa-tasks",
                        class: "btn-danger",
                    },
                    SUPERVISING: {
                        icon: "fas fa-binoculars",
                        class: "btn-warning",
                    },
                };
            _.forEach(response, (v) => {
                //Hack for display the SUPERVISING CARD
                if (!(v.id === "SUPERVISING" && v.counter === 0)) {
                    data.push({
                        title: v.title,
                        counter: v.counter,
                        item: v.id,
                        icon: info[v.id].icon,
                        onClick: (obj) => {
                            that.title = obj.title;
                            that.filterHeader = obj.item;
                            that.filterHeaderObject = obj;
                            that.random = _.random(0,1000000000);
                        },
                        class: info[v.id].class,
                    });
                }
            });
            return data;
        },
        /**
         * Open the case notes modal
         * @param {object} data - needed to create the data
         */
        openComments(data) {
            let that = this;
            api.cases.open(_.extend({ ACTION: "todo" }, data)).then(() => {
                that.$refs["modal-comments"].dataCase = data;
                that.$refs["modal-comments"].show();
            });
        },
        onRemoveFilter(data) {},
        /**
         * Prepare the data to be updated
         * @param {object} data
         */
        prepareAndUpdate(data) {
            let canUpdate = false,
                newFilters = [];
            data.params.forEach(item =>  {
                const container  = {...item};
                container.autoShow = false;
                if (item.value !== "") {
                    newFilters.push(container);
                    canUpdate = true;
                }
            });
            if (data.params.length == 0) {
            canUpdate = true;
            } 
            if (canUpdate) {
            this.$emit("updateSettings", {
                data: newFilters,
                key: "filters",
                page: "MyCases",
                type: "normal",
                id: this.id
            });
            }
        },
        onUpdateFilters(data) {
            this.filters = data.params;
            this.prepareAndUpdate(data);
            if (data.refresh) {
                this.$nextTick(() => {
                    this.$refs["vueTable"].getData();
                });
            }
        },
        /**
         * Post notes event handler
         */
        onPostNotes() {
            this.$refs["vueTable"].getData();
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
        /**
         * Reset the sort in the table
         */
        clearSort() {
            if (this.$refs['vueTable']) {
                this.$refs['vueTable'].setOrder(false)
                this.$emit("updateSettings", {
                    data: [],
                    key: "orderBy",
                    page: "MyCases",
                    type: "normal",
                    id: this.id
                });
            }
        },
        /**
         * Claim case
         *
         * @param {object} item
         */
        claimCase(item) {
            let that = this;
            api.cases.open(_.extend({ ACTION: "unassigned" }, item)).then(() => {
                api.cases.cases_open(_.extend({ ACTION: "todo" }, item)).then(() => {
                that.$refs["modal-claim-case"].data = item;
                that.$refs["modal-claim-case"].show();
                });
            });
        },
        /**
         * Claim catch error handler message
         */
        claimCatch(message) {
            this.showAlert(message, "danger");
        }
    },
};
</script>
<style>
.VueTables__row {
  height: 75px;
}
.v-container-mycases {
    padding-top: 20px;
    padding-bottom: 20px;
    padding-left: 50px;
    padding-right: 50px;
}
</style>
